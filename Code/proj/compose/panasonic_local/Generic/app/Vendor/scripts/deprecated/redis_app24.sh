#! /bin/bash

PLUGIN_NAME='App24'
APP_KIND='db'
STRING_SIZE=1024
MAIN_PAGE='main_menu'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COLUMNS='id State Department Name work_date workday_minutes non_workday_minutes'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS" "id" "desc"
./redis_config_searchview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_notification.sh $PLUGIN_NAME 'disable'

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'readable_by_all'

# Report
./redis_config_report.sh $PLUGIN_NAME 'disable'

# Import/Export keys
IMPEXP_KEYS='State Department Name work_date'
./redis_impexp_keys.sh $PLUGIN_NAME "$IMPEXP_KEYS"

