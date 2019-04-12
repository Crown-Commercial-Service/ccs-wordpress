#!/bin/bash
# Start/enable application-related services

WP_IMPORT_SCRIPT="/home/ec2-user/wp_import.sh"

echo "Starting codedeploy server_start.sh ..."

# Not an import server
if [ ! -f "$WP_IMPORT_SCRIPT" ]; then
    sudo systemctl is-enabled --quiet httpd \
        || sudo systemctl enable httpd

    sudo systemctl is-active --quiet httpd \
        || sudo systemctl start httpd
fi

echo "Codedeploy server_start.sh complete."
