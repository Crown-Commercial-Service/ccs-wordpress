#!/bin/bash
# System init/update

echo "Starting codedeploy server_setup.sh ..."

SCRIPTDIR=$(dirname $0)
IMPORT_APP_NAME="deploy-web_import"
FIRST_RUN_PATH="/codedeploy.server_setup"

echo -n "> Detecting deployment type: "
if [ "$APPLICATION_NAME" == "$IMPORT_APP_NAME" ]; then
    echo "import."

    DEPLOYMENT_TYPE="import"
else
    echo "cms."

    DEPLOYMENT_TYPE="cms"
fi

echo "> Updating system software..."
sudo yum update -y

if [ ! -e "$FIRST_RUN_PATH" ]; then
    echo "> Running once-only deployment tasks..."

    echo "> > Installing awslogs service..."
    sudo yum install -y awslogs

    echo "> > chown'ing awslogs config files..."
    sudo chown root:root \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/awscli.conf" \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/awslogs.conf"

    echo "> > chmod'ing awslogs config files..."
    sudo chmod 640 \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/awscli.conf" \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/awslogs.conf"

    echo "> > Movinging awslogs config files..."
    sudo mv -f \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/awscli.conf" \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/awslogs.conf" \
        /etc/awslogs/

    echo "> > Adding additional package repos..."
    sudo yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
    sudo yum install -y https://centos7.iuscommunity.org/ius-release.rpm

    echo "> > Installing common web packages..."
    sudo yum install -y \
        php73-cli \
        php73-mysqlnd.x86_64 \
        php73-opcache \
        php73-xml \
        php73-gd \
        php73-devel \
        php73-intl \
        php73-mbstring \
        php73-bcmath \
        php73-soap \
        php73-json

    if [ "$APPLICATION_NAME" != "$IMPORT_APP_NAME" ]; then
        echo "> Installing cms-specific web packages..."
        sudo yum install -y \
            httpd \
            mod_php73
    fi

    echo "> Ensuring system software is up to date..."
    sudo yum update -y

    echo "> Installing WP CLI..."
    sudo curl -s -o wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    sudo chmod +x wp
    sudo mv -f wp /usr/local/bin/

    echo "> > chown'ing logrotate config files..."
    sudo chown root:root "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/applogs"

    echo "> > chmod'ing logrotate config files..."
    sudo chmod 644 "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/applogs"

    echo "> > Moving logrotate config files..."
    sudo mv -f \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/applogs" \
        /etc/logrotate.d/

    echo "> > chown'ing php config file..."
    sudo chown root:root \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/99-custom.ini"

    echo "> > chmod'ing php config file..."
    sudo chmod 644 \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/99-custom.ini"

    echo "> > Moving php config file..."
    sudo mv -f \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/99-custom.ini" \
        /etc/php.d/

    if [ "$APPLICATION_NAME" == "$IMPORT_APP_NAME" ]; then
        echo "> Installing import-specific wp_import process..."

        echo "> > chown'ing wp_import.sh..."
        sudo chown ec2-user:ec2-user "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import.sh"

        echo "> > chmod'ing wp_import.sh..."
        sudo chmod 700 "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import.sh"

        echo "> > chown'ing wp_import..."
        sudo chown root:root "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import"

        echo "> > chmod'ing wp_import..."
        sudo chmod 644 "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import"

        echo "> > Moving wp_import.sh..."
        sudo mv -f \
            "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import.sh" \
            ~ec2-user/

        echo "> > Moving wp_import..."
        sudo mv -f \
            "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import" \
            /etc/cron.d/
    else
        echo "> Moving cms-specific httpd.conf..."
        sudo mv -f \
            "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/httpd.conf" \
            /etc/httpd/conf/httpd.conf
    fi

    echo "> > Marking first deployment tasks as completed..."
    sudo touch "$FIRST_RUN_PATH"
fi

echo "Codedeploy server_setup.sh complete."
