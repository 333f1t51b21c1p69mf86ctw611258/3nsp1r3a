#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 4 ]; then
    echo "usage: redis_config_workflow_create_as.sh <pluginName> <enable_or_disable> <allowed_group_mgr> <allowed_group_users>"
    exit 1
fi

PLUGIN_NAME=$1
OPERATION_MODE=$2
OPERATION_GROUP_MGR=$3
OPERATION_GROUP_USERS="$4"

OPERATION_GROUP_NAME_BASE=$PLUGIN_NAME'_workflow_create_as'
OPERATION_GROUP_NAME_SPECIFIC=$OPERATION_GROUP_NAME_BASE'_allowed_users'
#####################################################

redis-cli hset $OPERATION_GROUP_NAME_BASE mode $OPERATION_MODE
redis-cli hset $OPERATION_GROUP_NAME_BASE groupAllowedTo $OPERATION_GROUP_NAME_SPECIFIC

GROUP_NO=`bash redis_get_next_group_id.sh`
#echo "\n$GROUP_NO"
./redis_create_group.sh $GROUP_NO $OPERATION_GROUP_NAME_SPECIFIC $OPERATION_GROUP_MGR "$OPERATION_GROUP_USERS"

