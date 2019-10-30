#!/bin/bash
# Start/enable application-related services

IMPORT_APP_NAME="deploy-web_import"
WP_IMPORT_SCRIPT="/home/ec2-user/wp_import.sh"

echo "Starting codedeploy server_start.sh ..."

echo -n "> Detecting deployment type: "
if [ "$APPLICATION_NAME" == "$IMPORT_APP_NAME" ]; then
    echo "import."

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

echo "> Starting services..."
for SERVICE in "${SERVICES[@]}"; do
    echo -n "> > Enabling & starting service [$SERVICE]: "

    sudo systemctl is-enabled --quiet "$SERVICE" \
        || sudo systemctl enable "$SERVICE"

    sudo systemctl is-active --quiet "$SERVICE" \
        || sudo systemctl start "$SERVICE"

    echo "done."
done

echo "Codedeploy server_start.sh complete."
