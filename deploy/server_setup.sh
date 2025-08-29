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

echo "> Set timezone..."
    sudo rm -f /etc/sysconfig/clock
    sudo mv -f \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/clock" \
        /etc/sysconfig/clock
    sudo ln -sf /usr/share/zoneinfo/Europe/London /etc/localtime


if [ ! -e "$FIRST_RUN_PATH" ]; then
    echo "> Running once-only deployment tasks..."

    echo "> > Installing awslogs service..."
    sudo yum install -y amazon-cloudwatch-agent
    sudo systemctl enable amazon-cloudwatch-agent

    echo "> > chown'ing awslogs config files..."
    sudo chown root:root \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/applogs" \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/logrotate.conf" \

    echo "> > chmod'ing awslogs config files..."
    sudo chmod 640 \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/applogs" \
        "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/logrotate.conf" \

    echo "> > Moving log rotate config files..."
    sudo mv -f "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/logrotate.conf" /etc/logrotate.conf
    sudo mv -f "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/applogs" /etc/logrotate.d/

    echo "> > Wordpress Activity Logging..."
    sudo touch /var/log/httpd/user-activity-log.txt
    sudo chown root:root /var/log/httpd/user-activity-log.txt
    sudo chmod 666 /var/log/httpd/user-activity-log.txt
        
    echo "> > Moving cloudwatch rotate config file..."
    sudo mv -f "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/cloudwatch.json" /opt/aws/amazon-cloudwatch-agent/etc/config.json

    echo "> > Starting cloudwatch..."
    sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl -a fetch-config -m ec2 -s -c file:/opt/aws/amazon-cloudwatch-agent/etc/config.json
    sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl -m ec2 -a status

    echo "> > Adding additional package repos..."
    sudo yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
    sudo yum install -y https://repo.ius.io/ius-release-el$(rpm -E '%{rhel}').rpm

    echo "> > Setting journalctl to max 500mb..."
    sudo journalctl --vacuum-size=500M

    echo "> > Installing common web packages..."

    sudo yum clean metadata
    sudo yum -y install \
        php8.2 \
        php-mysqlnd.x86_64 \
        php-opcache.x86_64 \
        php-xml.x86_64 \
        php-gd.x86_64 \
        php-devel.x86_64 \
        php-intl.x86_64 \
        php-mbstring.x86_64 \
        php-bcmath.x86_64 \
        php-soap.x86_64 \
        php-json.x86_64
        
    php -v

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

        echo "> Installing cronie..."
        sudo yum install cronie -y
        sudo systemctl enable crond.service
        sudo systemctl start crond.service

        echo "> Installing text logging..."
        sudo yum install rsyslog -y
        sudo systemctl start rsyslog
        sudo systemctl enable rsyslog

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

        echo "> Installing Dead Mans Snitch field agent..."
        sudo curl -O https://releases.deadmanssnitch.com/field-agent/1.0.8/dms_1.0.8_linux_amd64.tar.gz
        sudo tar zxvf dms_1.0.8_linux_amd64.tar.gz -C /usr/local/bin

echo "> Installing import-specific wp_import cron script..."

        echo "> > chown'ing wp_import_dms.sh..."
        sudo chown ec2-user:ec2-user "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import_dms.sh"

        echo "> > chmod'ing wp_import_dms.sh..."
        sudo chmod 700 "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import_dms.sh"

        echo "> > Moving wp_import_dms.sh..."
        sudo mv -f \
            "$SCRIPTDIR/$DEPLOYMENT_TYPE/files/wp_import_dms.sh" \
            ~ec2-user/


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
