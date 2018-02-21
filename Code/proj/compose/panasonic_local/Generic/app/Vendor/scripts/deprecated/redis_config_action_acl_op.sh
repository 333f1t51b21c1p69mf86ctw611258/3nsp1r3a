#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 3 ]; then
    echo "usage: redis_config_action_acl_op.sh <plugin_name> <wf_action> <acl_op>"
    exit 1
fi

PLUGIN_NAME=$1
MODEL_NAME='Attr'${PLUGIN_NAME,,}
ACTION=$2
ACL_OP=$3

#####################################################
ACTION_ACOS_KEY="$PLUGIN_NAME"_acl_action_acos
ACTION_OPS_KEY="$PLUGIN_NAME"_acl_action_ops
ACO=$MODEL_NAME

redis-cli hset $ACTION_ACOS_KEY $ACTION $ACO
redis-cli hset $ACTION_OPS_KEY $ACTION $ACL_OP

