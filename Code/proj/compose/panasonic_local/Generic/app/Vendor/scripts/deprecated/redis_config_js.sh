#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 3 ]; then
    echo "usage: redis_config_js.sh <plugin_name> <calculated_fields> <import_to_ignored_columns>"
    exit 1
fi

PLUGIN_NAME=$1
CALCULATED_FIELDS=$2
IMPORT_TO_IGNORED=$3

#####################################################

redis-cli hset "$PLUGIN_NAME" db_ignore "$PLUGIN_NAME"_upload_ignored
redis-cli hset "$PLUGIN_NAME" import_calculated_columns $IMPORT_TO_IGNORED
#IFS=' ' read -ra IDS <<< "$CALCULATED_FIELDS"
for column in $CALCULATED_FIELDS; do
    redis-cli sadd "$PLUGIN_NAME"_upload_ignored $column
done
