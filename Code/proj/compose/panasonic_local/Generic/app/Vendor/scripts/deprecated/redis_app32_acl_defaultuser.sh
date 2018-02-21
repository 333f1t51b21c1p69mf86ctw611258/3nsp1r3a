#! /bin/bash
. ./env.sh

PLUGIN_NAME=App32

#user

#manager

#controller
YER_USER_IDS='107'
LAYER_NAME="controller"
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
echo bash redis_push_user_to_acl_layer.sh $LAYER_USER_IDS $REDIS_ACL_LAYER_KEY
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY

#approver
LAYER_USER_IDS='104'
LAYER_NAME="approver"
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
echo bash redis_push_user_to_acl_layer.sh $LAYER_USER_IDS $REDIS_ACL_LAYER_KEY
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY

#admin
LAYER_USER_IDS='1 105 98'
LAYER_NAME="admin"
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
echo bash redis_push_user_to_acl_layer.sh $LAYER_USER_IDS $REDIS_ACL_LAYER_KEY
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY

