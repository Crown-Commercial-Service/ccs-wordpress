#!/usr/bin/env bash
# Import wrapper

PID_FILE="/home/ec2-user/wp_import_sh.pid"
WEB_PATH="/var/www/public"
ALERT_MINS=1440

# Check we have a deployed server
if [ ! -e "$WEB_PATH" ]; then
    echo "ALERT: Import codebase not available, exitting"
    exit 1
fi

# Ensure import does not run concurrently
if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    echo "WARNING: Import is currently running, pid:[$PID], exitting"

    # Alert to stale pid
    if [ ! -z $(find "$PID_FILE" -mmin +$ALERT_MINS) ]; then
        echo "ALERT: Import process still locked after $(( $ALERT_MINS / 60)) hours! Check whether manual resolution is required."
    fi

    exit 1
fi

# Execute the import process
trap 'echo Import shutting down; $(jobs -p | xargs -r kill); rm -f "$PID_FILE"' EXIT

echo "Starting import..."
echo -n $$ > "$PID_FILE"
chmod 600 "$PID_FILE"

cd "$WEB_PATH"
/usr/local/bin/wp --no-color salesforce import all 2>&1
echo "Import complete."

rm -f "$PID_FILE"
