#! /bin/bash

for (( i=12; i<=12; i++ ))
do
    PLUGIN_NAME="App$i"
    APP_KIND='db'
    STRING_SIZE=256
    MAIN_PAGE='main_menu'
    ./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

    MAINVIEW_COLUMNS="id"
    ./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
    ./redis_config_searchview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
    ./redis_config_notification.sh $PLUGIN_NAME 'disable'

    # Visibility
    ./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

    # Report
    ./redis_config_report.sh $PLUGIN_NAME 'enable'
done

