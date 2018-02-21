#! /bin/bash

PLUGIN_NAME='App36'
APP_KIND='db'
STRING_SIZE=256
MAIN_PAGE='main_menu'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COLUMNS='id Person_in_Charge Customer Plant Document_Date'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS" 
./redis_config_searchview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_notification.sh $PLUGIN_NAME 'enable' 'enspirea.dev@gmail.com' "external_baseurl|https://cci-corporation.briode.com/Generic"

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'readable_by_all'

# Report
./redis_config_report.sh $PLUGIN_NAME 'disable'

