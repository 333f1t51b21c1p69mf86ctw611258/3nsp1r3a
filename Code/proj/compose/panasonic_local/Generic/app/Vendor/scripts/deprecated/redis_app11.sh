#! /bin/bash

MAINVIEW_COLUMNS11='subject_id created_at creator_id summary assignee mandatory_flag validation_flag'
SEARCHVIEW_COLUMNS11='id created_at creator_id Control_Number A_Time_slot_1 A_Line_1 A_Appearance_1'

PLUGIN_NAME="App11"
APP_KIND='workflow'
STRING_SIZE=256
MAIN_PAGE='list_all'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COL_NAMES="MAINVIEW_COLUMNS11"
SEARCHVIEW_COL_NAMES="SEARCHVIEW_COLUMNS11"
./redis_config_mainview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
./redis_config_searchview.sh $PLUGIN_NAME "${!SEARCHVIEW_COL_NAMES}"

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

# Report
./redis_config_report.sh $PLUGIN_NAME 'enable'

# validation
./redis_validation_app11.sh

