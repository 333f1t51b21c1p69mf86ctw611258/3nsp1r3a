#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 2 ]; then
    echo "usage: redis_config_db_visibility.sh <plugin_name> <visibility_string>"
    exit 1
fi

PLUGIN_NAME="$1"
VISIBILITY_STRING=$2
#####################################################

# Excel import initial configuration
redis-cli hset "$PLUGIN_NAME" db_visibility $VISIBILITY_STRING
