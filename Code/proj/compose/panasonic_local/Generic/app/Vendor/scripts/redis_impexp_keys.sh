#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 2 ]; then
    echo "usage: redis_impexp_keys.sh <PLUGIN_NAME> <KEY_LIST>"
    exit 1
fi

PLUGIN_NAME=$1
IMPEXP_KEYS=$2

#####################################################
redis-cli -h $REDISHOST lpush "$PLUGIN_NAME"_impexp_keys $IMPEXP_KEYS

#redis-cli -h $REDISHOST hset App_list_labels "$PLUGIN_KEY" "$APP_LIST_LABEL"
#redis-cli -h $REDISHOST hset App_navname_labels "$PLUGIN_NAME" "$APP_NAVNAME_LABEL"
#redis-cli -h $REDISHOST hset App_name_to_plugin "$APP_NAVNAME_LABEL" "$PLUGIN_NAME"
