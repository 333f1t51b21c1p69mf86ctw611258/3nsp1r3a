#! /bin/bash

PLUGIN_NAME='App15'
APP_KIND='workflow'
STRING_SIZE=256
MAIN_PAGE='list_waiting_for_your_action'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE
./redis_config_workflow.sh $PLUGIN_NAME 'disable' 'enable' 

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

# Report
./redis_config_report.sh $PLUGIN_NAME 'enable'

MAINVIEW_COLUMNS='subject_id created_at creator_id summary assignee mandatory_flag validation_flag'
SEARCHVIEW_COLUMNS='id creator_id created_at Datecell Stringcell Integercell'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_searchview.sh $PLUGIN_NAME "$SEARCHVIEW_COLUMNS"

# 'loading validation for redis....'
./redis_validation_app15.sh

