#! /bin/bash

PLUGIN_NAME='App39'
APP_KIND='db'
STRING_SIZE=32
MAIN_PAGE='main_menu'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COLUMNS='id Name Branch Year Month Date_Reported'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_searchview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_notification.sh $PLUGIN_NAME 'disable'

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'readable_by_all'

# Report
./redis_config_report.sh $PLUGIN_NAME 'disable'

