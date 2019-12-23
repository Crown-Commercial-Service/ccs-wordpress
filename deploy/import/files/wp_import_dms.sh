#!/bin/bash
source /var/www/.env

IMPORT_TIME=$1

if [ "$IMPORT_TIME" == "import_1" ]; then
    export DMS_ID=$DMS_2AM
elif [ "$IMPORT_TIME" == "import_2" ]; then
    export DMS_ID=$DMS_10AM
elif [ "$IMPORT_TIME" == "import_3" ]; then
    export DMS_ID=$DMS_12PM
elif [ "$IMPORT_TIME" == "import_4" ]; then
    export DMS_ID=$DMS_3PM
fi

/usr/local/bin/dms $DMS_ID /home/ec2-user/wp_import.sh | logger -t 'wp_import'

