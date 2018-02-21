#! /bin/bash

PLUGIN_NAME='App19'
APP_KIND='workflow'
STRING_SIZE=256
MAIN_PAGE='list_waiting_for_your_action'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE
./redis_config_workflow.sh $PLUGIN_NAME 'disable' 'enable' #email

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

# Report
./redis_config_report.sh $PLUGIN_NAME 'disable'

MAINVIEW_COLUMNS='subject_id created_at creator_id summary assignee'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
SEARCHVIEW_COLUMNS='subject_id creator_id created_at 5_A_total 5_B_total 5_C_total 5_D_total 5_E_total 5_ABCDE_total'
./redis_config_searchview.sh $PLUGIN_NAME "$SEARCHVIEW_COLUMNS"




