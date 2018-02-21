#! /bin/bash
. ./env.sh

PLUGIN_NAME=App2

#user
LAYER_USER_IDS='109 110 113 114 115 125 129 130'
LAYER_NAME="user"
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY

LAYER_USER_IDS='89 90 91 92 94 96 97 98 99 100 124 126 127 128 131'
LAYER_NAME='manager'
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY

LAYER_USER_IDS='105 107'
LAYER_NAME='controller'
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY

LAYER_USER_IDS='104'
LAYER_NAME='approver'
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY

LAYER_USER_IDS='1'
LAYER_NAME='admin'
REDIS_ACL_LAYER_KEY=${PLUGIN_NAME}_acl_${LAYER_NAME}
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" $REDIS_ACL_LAYER_KEY


###################
# Workflow control
###################

./redis_config_workflow_post_approve.sh $PLUGIN_NAME 'review' 'enable' "$PLUGIN_NAME"_workflow_create_as_allowed_users '107' "109 110 113 114 115 125 129 130" # send email to listed
./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '89' "92 94 99 131" # 89:jbergen 
./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '90' "96 97 124 127" # 90:mmarciniak
./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '91' "126 128" # 91:pdecarlo
#./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '93' "" # 93:tmoeller
./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '98' "" # 98:dlong
./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '100' "" # 100:gtakamatsu
#./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '101' "102" # 101:slynum
#./redis_config_workflow_create_as.sh $PLUGIN_NAME enable '103' "" # 103:ksaka

./redis_config_workflow_approval_op_as.sh $PLUGIN_NAME enable enable '104' "105" enable # Christine is higher than Daria


