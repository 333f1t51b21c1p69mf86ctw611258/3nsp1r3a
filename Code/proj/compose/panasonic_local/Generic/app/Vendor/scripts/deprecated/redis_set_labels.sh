#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 3 ]; then
    echo "usage: redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>"
    exit 1
fi

PLUGIN_NAME=$1
APP_LIST_LABEL="$2"
APP_NAVNAME_LABEL="$3"

PLUGIN_KEY="$PLUGIN_NAME"/main_menu

#####################################################
# order of app selector is determined by App_list_urls
URL="$PLUGIN_NAME"/main_menu
redis-cli rpush App_list_urls $URL

redis-cli hset App_list_labels "$PLUGIN_KEY" "$APP_LIST_LABEL"
redis-cli hset App_navname_labels "$PLUGIN_NAME" "$APP_NAVNAME_LABEL"
redis-cli hset App_name_to_plugin "$APP_NAVNAME_LABEL" "$PLUGIN_NAME"
