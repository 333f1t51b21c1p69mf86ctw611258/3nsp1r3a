#! /bin/bash

MAINVIEW_COLUMNS4='subject_id created_at creator_id summary assignee mandatory_flag validation_flag'
SEARCHVIEW_COLUMNS4='id created_at creator_id Control_Number A_Time_slot_1 A_Line_1 A_Appearance_1 A_Trim_Height1_1'

PLUGIN_NAME="App4"
APP_KIND='workflow'
STRING_SIZE=256
MAIN_PAGE='list_all'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COL_NAMES="MAINVIEW_COLUMNS4"
SEARCHVIEW_COL_NAMES="SEARCHVIEW_COLUMNS4"
./redis_config_mainview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
./redis_config_searchview.sh $PLUGIN_NAME "${!SEARCHVIEW_COL_NAMES}"

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

# Report - FIXME 2nd option ignored
./redis_config_report.sh $PLUGIN_NAME 'enable' "url|https://awnc.briode.com/Generic/users/login"

# validation
./redis_validation_app4.sh

