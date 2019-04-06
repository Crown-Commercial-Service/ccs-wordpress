#!/bin/bash
# Stop application-related services

WP_IMPORT_SCRIPT="/home/ec2-user/wp_import.sh"
WP_IMPORT_PID_FILE="/home/ec2-user/wp_import_sh.pid"

echo "Starting codedeploy server_stop.sh ..."

# Is an import server
if [ -f "$WP_IMPORT_SCRIPT" ]; then
    # Import in progress
    if [ -f "$WP_IMPORT_PID_FILE" ]; then
        echo "Active import detected, stopping..."
        kill $(cat "$WP_IMPORT_PID_FILE")
        echo "Import stopped."
    fi
else
    sudo systemctl is-active --quiet httpd \
        && sudo systemctl stop httpd.service
fi

echo "Codedeploy server_stop.sh complete."
