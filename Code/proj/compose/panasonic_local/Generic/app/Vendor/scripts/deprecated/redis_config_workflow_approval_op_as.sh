#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 6 ]; then
    echo "usage: redis_config_workflow_approval_op_as.sh <pluginName> <fromUpperMode> <fromLowerMode> <allowed_group_mgr> <allowed_group_users> <mode_list_approver_from_lower>"
    exit 1
fi

PLUGIN_NAME=$1
OPERATION_FROMUPPER_MODE=$2
OPERATION_FROMLOWER_MODE=$3
OPERATION_GROUP_MGR=$4
OPERATION_GROUP_USERS="$5"
MODE_LIST_APPROVER_FROM_LOWER=$6

OPERATION_GROUP_NAME_BASE=$PLUGIN_NAME'_workflow_approval_op_as'
OPERATION_GROUP_NAME_SPECIFIC=$OPERATION_GROUP_NAME_BASE'_allowed_users'
#####################################################

redis-cli hset $OPERATION_GROUP_NAME_BASE fromUpper $OPERATION_FROMUPPER_MODE
redis-cli hset $OPERATION_GROUP_NAME_BASE fromLower $OPERATION_FROMLOWER_MODE
redis-cli hset $OPERATION_GROUP_NAME_BASE groupAllowedTo $OPERATION_GROUP_NAME_SPECIFIC
redis-cli hset $OPERATION_GROUP_NAME_BASE listApproversFromLower $MODE_LIST_APPROVER_FROM_LOWER

GROUP_NO=`bash redis_get_next_group_id.sh`
#echo "\n$GROUP_NO"
./redis_create_group.sh $GROUP_NO $OPERATION_GROUP_NAME_SPECIFIC $OPERATION_GROUP_MGR "$OPERATION_GROUP_USERS"

