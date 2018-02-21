#! /bin/bash

cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 4 ]; then
    echo "usage: redis_config_nav.sh <plugin_name> <action> <label> <aco_op>"
    exit 1
fi

PLUGIN_NAME=$1
MODEL_NAME='Attr'${PLUGIN_NAME,,}
NAVOP_ACTION=$2
NAVOP_LABEL=$3
ACO=$MODEL_NAME
ACO_OP=$4

redis-cli hset "$PLUGIN_NAME"_nav_action_label $NAVOP_ACTION "$NAVOP_LABEL"
redis-cli hset "$PLUGIN_NAME"_nav_action_acos $NAVOP_ACTION $ACO
redis-cli hset "$PLUGIN_NAME"_nav_action_ops $NAVOP_ACTION $ACO_OP

