#! /bin/sh
. ./env.sh

PLUGIN_NAME=App2

# User Layers
LAYER_USER_IDS=''

LAYER_ROOT='/'
LAYER_NAME='user'
LAYER_OPS='read'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='user'
LAYER_NAME='manager'
LAYER_OPS='create read update'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='manager'
LAYER_NAME='controller'
LAYER_OPS='create read update delete'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='controller'
LAYER_NAME='approver'
LAYER_OPS='all'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='approver'
LAYER_NAME='admin'
LAYER_OPS='all'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"

# Actions
ACTION='export'
ACL_OP='delete'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP
ACTION='create'
ACL_OP='create'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP
# Workflow Actions
ACTION='approve'
ACL_OP='delete'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP
ACTION='cancel'
ACL_OP='delete'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP
ACTION='update'
ACL_OP='update'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP
ACTION='review'
ACL_OP='update'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP

