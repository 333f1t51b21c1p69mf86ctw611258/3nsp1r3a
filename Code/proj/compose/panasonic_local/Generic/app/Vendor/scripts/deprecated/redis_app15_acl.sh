#! /bin/sh
. ./env.sh
if [ $# -ne 1 ]; then
    echo "Usage: $0 <plugin_name>"
    exit 1
fi

PLUGIN_NAME=$1

# User Layers
LAYER_ROOT='/'
LAYER_NAME='user'
LAYER_USER_IDS='26 27 28 29 30 31 32'
LAYER_OPS='create read'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='user'
LAYER_NAME='manager'
LAYER_USER_IDS='25'
LAYER_OPS='create read update'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='manager'
LAYER_NAME='approver'
LAYER_USER_IDS=''
LAYER_OPS='all'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='approver'
LAYER_NAME='admin'
LAYER_USER_IDS='1 24'
LAYER_OPS='all'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"

# Actions
ACTION='export'
ACL_OP='update'
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

