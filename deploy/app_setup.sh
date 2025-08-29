#!/bin/bash
# System init/update

echo "Starting codedeploy app_setup.sh ..."

IMPORT_APP_NAME="deploy-web_import"
DEPLOY_PATH="/deploy"
WEB_PREV="/var/www.prev"
WEB_CURRENT="/var/www"

echo -n "> Detecting deployment type: "
if [ "$APPLICATION_NAME" == "$IMPORT_APP_NAME" ]; then
    echo "import."

    WEB_USERNAME="ec2-user"
    WEB_GROUPNAME="ec2-user"
else
    echo "cms."

    WEB_USERNAME="apache"
    WEB_GROUPNAME="apache"
fi

# Revert web files in the event of a deployment error
function rollback {
    echo -n "!!! Rolling back deployment state: "
    if [ -e "$WEB_PREV" ]; then
        if [ -e "$WEB_CURRENT" ]; then
            sudo rm -rf "$WEB_CURRENT"
        fi

        sudo mv -f "$WEB_PREV" "$WEB_CURRENT"
    fi

    echo "done."
    exit 1
}

if [ -e "$WEB_CURRENT" ]; then
    echo "> Moving existing web deployment out the way..."
    (sudo mv -f "$WEB_CURRENT" "$WEB_PREV" &&
        sudo mkdir -p "$WEB_CURRENT" &&
        sudo chown root:root "$WEB_CURRENT"
    ) || rollback
fi

echo "> Preparing new web deployment files..."
(sudo rm -f "$DEPLOY_PATH/appspec.yml" &&
    sudo rm -rf "$DEPLOY_PATH/deploy" &&
    sudo mkdir -p "$WEB_CURRENT" &&
    sudo chown ec2-user:ec2-user "$WEB_CURRENT" &&
    sudo mv -f "$DEPLOY_PATH/.env" "$DEPLOY_PATH/"* "$WEB_CURRENT" &&
    sudo ln -s "$WEB_CURRENT/public" "$WEB_CURRENT/html" &&
    sudo mkdir -p "$WEB_CURRENT/public/wp-content/uploads"
) || rollback

echo "> Setting web deployment permissions..."
(sudo chown -R ec2-user:ec2-user /var/www &&
    sudo chown -R "$WEB_USERNAME":"$WEB_GROUPNAME" "$WEB_CURRENT/public/wp-content/uploads" &&
    sudo chown -R ec2-user:"$WEB_GROUPNAME" "$WEB_CURRENT/var/log" &&
    sudo chmod -R og+w "$WEB_CURRENT/var/log" &&
    sudo chmod 640 "$WEB_CURRENT/.env" &&
    sudo chgrp apache "$WEB_CURRENT/.env" &&
    sudo chmod 666 "$WEB_CURRENT/public/wp-content/plugins/ccs-custom/library/user-activity-log.txt"
) || rollback

echo "> Running cleanup..."
if [ -e "$DEPLOY_PATH" ]; then
    echo "> > Deploy path..."
    sudo rm -rf "$DEPLOY_PATH"
fi

if [ -e "$WEB_PREV" ]; then
    echo "> > Previous web deployment..."
    sudo rm -rf "$WEB_PREV"
fi

echo "Codedeploy app_setup.sh complete."
