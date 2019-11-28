#!/bin/bash
# Stop application-related services

IMPORT_APP_NAME="deploy-web_import"
WP_IMPORT_SCRIPT="/home/ec2-user/wp_import.sh"
WP_IMPORT_PID_FILE="/home/ec2-user/wp_import_sh.pid"

echo "Starting codedeploy server_stop.sh ..."

echo -n "> Detecting deployment type: "
if [ "$APPLICATION_NAME" == "$IMPORT_APP_NAME" ]; then
    echo "import."

    if [ -f "$WP_IMPORT_PID_FILE" ]; then
        echo "> Active import detected, stopping..."
        kill $(cat "$WP_IMPORT_PID_FILE")
        echo "> Import stopped."
    fi

    SERVICES=(
        "awslogsd.service"
    )
else
    echo "cms."

    SERVICES=(
        "awslogsd.service"
        "httpd.service"
    )
fi

# Service will not be installed on the first run
echo "> Stopping services..."
for SERVICE in "${SERVICES[@]}"; do
    echo -n "> > Stopping service [$SERVICE]: "

    SERVICE_REGEX=$(echo "$SERVICE" | sed -e 's/\./\\./g')

    sudo systemctl list-unit-files "$SERVICE"|grep -q "^${SERVICE_REGEX}\\s"
    if [ $? -eq 0 ]; then
        sudo systemctl is-active --quiet "$SERVICE" \
            && sudo systemctl stop "$SERVICE"
    fi

    echo "done."
done

echo "Codedeploy server_stop.sh complete."
