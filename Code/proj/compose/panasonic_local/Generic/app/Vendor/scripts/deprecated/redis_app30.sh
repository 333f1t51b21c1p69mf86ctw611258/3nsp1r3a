#! /bin/bash

PLUGIN_NAME='App30'
APP_KIND='workflow'
STRING_SIZE=1024
MAIN_PAGE='list_waiting_for_your_action'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE
./redis_config_workflow.sh $PLUGIN_NAME 'enable' 'enable' "external|http://remote.enspirea.com:8888/Generic/users/login internal|http://192.168.1.8/Generic/users/login"

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

# Report
./redis_config_report.sh $PLUGIN_NAME 'disable'

MAINVIEW_COLUMNS='subject_id created_at creator_id summary assignee'
SEARCHVIEW_COLUMNS='id created_at creator_id'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_searchview.sh $PLUGIN_NAME "$SEARCHVIEW_COLUMNS"




