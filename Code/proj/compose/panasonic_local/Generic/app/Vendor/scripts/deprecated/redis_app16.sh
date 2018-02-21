#! /bin/bash

MAINVIEW_COLUMNS16='id creator_id Date Application_No Project Purpose Total'
MAINVIEW_COLUMNS17='id creator_id created_at Topmenu Webtabmenu UploadExcel Account1 Account2 Account3 ProfilePicture'

for (( i=16; i<=16; i++ ))
do
    PLUGIN_NAME="App$i"
    APP_KIND='db'
    STRING_SIZE=1024
    MAIN_PAGE='main_menu'
    ./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

    MAINVIEW_COL_NAMES="MAINVIEW_COLUMNS$i"
    ./redis_config_mainview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
    ./redis_config_searchview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
    ./redis_config_notification.sh $PLUGIN_NAME 'disable'

    # Visibility
    ./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

    # Report
    ./redis_config_report.sh $PLUGIN_NAME 'enable'
done

