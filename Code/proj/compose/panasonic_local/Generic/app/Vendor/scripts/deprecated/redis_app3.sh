#! /bin/bash

MAINVIEW_COLUMNS3='id project_number part_name part_number response_to First_shipment First_good'

PLUGIN_NAME="App3"
APP_KIND='db'
STRING_SIZE=256
MAIN_PAGE='main_menu'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COL_NAMES="MAINVIEW_COLUMNS3"
./redis_config_mainview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
./redis_config_searchview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
./redis_config_notification.sh $PLUGIN_NAME 'disable'

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

# Report
./redis_config_report.sh $PLUGIN_NAME 'enable'


