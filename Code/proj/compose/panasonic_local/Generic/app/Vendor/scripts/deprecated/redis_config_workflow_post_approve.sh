#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 6 ]; then
    echo "usage: redis_config_workflow_post_approve.sh <pluginName> <post_action_name> <enable/disable_post_script> <"" or group_name_for_notification> <allowed_group_mgr> <allowed_group_users>"
    exit 1
fi

PLUGIN_NAME=$1
POST_APPROVE_ACTION_NAME=$2
ENABLE_POST_SCRIPT=$3
GROUP_NAME_FOR_NOTIFICATION=$4
POST_APPROVE_GROUP_MGR=$5
POST_APPROVE_GROUP_USERS="$6"

ENABLE_GROUP_NOTIFICATION=enable
if [ -z "$GROUP_NAME_FOR_NOTIFICATION" ]; then
    ENABLE_GROUP_NOTIFICATION=disable
fi


POST_APPROVE_GROUP_NAME=$PLUGIN_NAME'_workflow_post_approve_allowed_users'
#####################################################

redis-cli hset "$PLUGIN_NAME"_workflow_post_approve action $POST_APPROVE_ACTION_NAME
redis-cli hset "$PLUGIN_NAME"_workflow_post_approve groupAllowedTo $POST_APPROVE_GROUP_NAME
redis-cli hset "$PLUGIN_NAME"_workflow_post_approve enablePostScript $ENABLE_POST_SCRIPT
redis-cli hset "$PLUGIN_NAME"_workflow_post_approve enableGroupNotification $ENABLE_GROUP_NOTIFICATION
if [ "$ENABLE_GROUP_NOTIFICATION" = "enable" ]; then
    redis-cli lpush "$PLUGIN_NAME"_workflow_post_approve_notification_groups $GROUP_NAME_FOR_NOTIFICATION
fi


GROUP_NO=`bash redis_get_next_group_id.sh`
#echo "\n$GROUP_NO"
./redis_create_group.sh $GROUP_NO $POST_APPROVE_GROUP_NAME $POST_APPROVE_GROUP_MGR "$POST_APPROVE_GROUP_USERS"

