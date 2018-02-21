#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 1 ]; then
    echo "usage: redis_set_labels.sh <PLUGIN_NAME>"
    exit 1
fi

PLUGIN_NAME=$1

#####################################################

redis-cli hset App_initial_plugin default $PLUGIN_NAME
