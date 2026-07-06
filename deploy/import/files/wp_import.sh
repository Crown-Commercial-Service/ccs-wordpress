#!/usr/bin/env bash

# Import wrapper
PID_FILE="/home/ec2-user/wp_import_sh.pid"
WEB_PATH="/var/www/public"
ALERT_MINS=1440

# Get SNS Topic from SSM
IMDS_TOKEN=$(curl -s -X PUT "http://169.254.169.254/latest/api/token" -H "X-aws-ec2-metadata-token-ttl-seconds: 60")
AWS_REGION=$(curl -s -H "X-aws-ec2-metadata-token: $IMDS_TOKEN" http://169.254.169.254/latest/meta-data/placement/region)
SNS_TOPIC_ARN=$(aws ssm get-parameter --name "import-sns-topic" --query "Parameter.Value" --output text --region "$AWS_REGION" 2>/dev/null)

# Helper function to send alert events
send_sns_alert() {
    local state="$1"       # Must be "ALARM" or "OK"
    local description="$2" # Message detail

    if [ -z "$SNS_TOPIC_ARN" ]; then
        echo "WARNING: SNS_TOPIC_ARN environment variable missing, Skipping alert."
        return
    fi

    # CloudWatch payload.
    local json_payload
    json_payload=$(cat <<EOF
{
  "AlarmName": "Import-Failure",
  "AlarmDescription": "${description}",
  "NewStateValue": "${state}",
  "NewStateReason": "Status triggered from EC2 bash script.",
  "Region": "eu-west-2"
}
EOF
)

    echo "Publishing alert state [${state}] to SNS..."
    aws sns publish \
        --topic-arn "$SNS_TOPIC_ARN" \
        --subject "CloudWatch Alarm: Import-Failure - State: ${state}" \
        --message "$json_payload" > /dev/null
}

# Check we have a deployed server
if [ ! -e "$WEB_PATH" ]; then 
    msg="ALERT: ($WEB_PATH) Not Available, Import Aborted."
    echo "$msg"
    send_sns_alert "ALARM" "$msg"
    exit 1
fi

# Ensure import does not run concurrently
if [ -f "$PID_FILE" ]; then 
    PID=$(cat "$PID_FILE")
    msg="ALERT: Another Import is Already Running, PID:[$PID], Import Aborted."
    
    # Alert to stale pid 
    if [ ! -z "$(find "$PID_FILE" -mmin +$ALERT_MINS)" ]; then 
        msg="ALERT: Import process PID:[$PID] still locked after $(( $ALERT_MINS / 60)) hours! Check whether manual resolution is required."
    fi

    echo "$msg"
    send_sns_alert "ALARM" "$msg"
    exit 1
fi

# Execute the import process
trap 'echo Import shutting down; $(jobs -p | xargs -r kill); rm -f "$PID_FILE"' EXIT

echo "Starting import..."
echo -n $$ > "$PID_FILE"
chmod 600 "$PID_FILE"
cd "$WEB_PATH"

if /usr/local/bin/wp mdm-import importAll 2>&1; then
    msg="OK: Import Completed Successfully."
    echo "$msg"
    send_sns_alert "OK" "$msg"
else
    msg="ERROR: The WordPress mdm-import command failed during execution."
    echo "$msg"
    send_sns_alert "ALARM" "$msg"
    exit 1
fi
