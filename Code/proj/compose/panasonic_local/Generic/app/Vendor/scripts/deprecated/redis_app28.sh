#! /bin/bash

PLUGIN_NAME='App28'
APP_KIND='db'
STRING_SIZE=1024
MAIN_PAGE='main_menu'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COLUMNS='id Sls_CD AS400 Time_Card'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS" "id" "desc"
./redis_config_searchview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_notification.sh $PLUGIN_NAME 'disable'

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'readable_by_all'

# Report
./redis_config_report.sh $PLUGIN_NAME 'enable'

# Import/Export keys
IMPEXP_KEYS='Sls_CD'
./redis_impexp_keys.sh $PLUGIN_NAME "$IMPEXP_KEYS"

