#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 4 ]; then
    echo "usage: redis_create_app.sh <plugin_name> <app_kind> <string_size> <main_page>"
    exit 1
fi

PLUGIN_NAME=$1
APP_KIND=$2
STRING_SIZE=$3
MAIN_PAGE=$4

#####################################################
# Derived values
MODEL_SUFFIX='Attr'
MODEL_NAME="$MODEL_SUFFIX${PLUGIN_NAME,,}"
DB_NAME=${MODEL_SUFFIX,,}${PLUGIN_NAME,,}'s'

# AppConfig
redis-cli hset $PLUGIN_NAME app_kind $APP_KIND
redis-cli hset $PLUGIN_NAME app_name $PLUGIN_NAME
redis-cli hset $PLUGIN_NAME model_name $MODEL_NAME
redis-cli hset $PLUGIN_NAME db_name $DB_NAME
redis-cli hset $PLUGIN_NAME db_col_size $STRING_SIZE
redis-cli hset $PLUGIN_NAME main_page $MAIN_PAGE

if [ $APP_KIND == "workflow" ]; then
    WF_MODEL_SUFFIX='Wf'
    WF_MODEL_NAME="$WF_MODEL_SUFFIX${PLUGIN_NAME,,}"
    WF_DB_NAME=${WF_MODEL_SUFFIX,,}${PLUGIN_NAME,,}'s'
    COMM_MODEL_SUFFIX='Comm'
    COMM_MODEL_NAME="$COMM_MODEL_SUFFIX${PLUGIN_NAME,,}"
    COMM_DB_NAME=${COMM_MODEL_SUFFIX,,}${PLUGIN_NAME,,}'s'
    redis-cli hset "$PLUGIN_NAME"_workflow model_name $WF_MODEL_NAME
    redis-cli hset "$PLUGIN_NAME"_workflow db_name $WF_DB_NAME
    redis-cli hset "$PLUGIN_NAME"_workflow comment_model_name $COMM_MODEL_NAME
    redis-cli hset "$PLUGIN_NAME"_workflow comment_db_name $COMM_DB_NAME
fi

# Default Configuration
redis-cli hset "$PLUGIN_NAME"_acl default_acl_aco $MODEL_NAME
redis-cli hset "$PLUGIN_NAME"_acl default_acl_op  read

redis-cli hset "$PLUGIN_NAME"_op_label export "Export Data(.xls)"
redis-cli hset "$PLUGIN_NAME"_op_label import_excel "Import Data(.xls)"
redis-cli hset "$PLUGIN_NAME"_op_label import_timesheet "Import Timesheet(.xls)"
redis-cli hset "$PLUGIN_NAME"_op_label import_target "Import Target(.xls)"
redis-cli hset "$PLUGIN_NAME"_op_label create "Create New Entry"
