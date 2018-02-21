#! /bin/sh
. ./env.sh
if [ $# -ne 1 ]; then
    echo "Usage: $0 <plugin_name>"
    exit 1
fi

PLUGIN_NAME=$1

# Actions
ACTION='export'
ACL_OP='update'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP
ACTION='create'
ACL_OP='create'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP
ACTION='import_excel'
ACL_OP='update'
./redis_config_action_acl_op.sh $PLUGIN_NAME $ACTION $ACL_OP

# User Layers
LAYER_ROOT='/'
LAYER_NAME='user'
LAYER_USER_IDS='118 119 120 122'
LAYER_OPS='read'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='user'
LAYER_NAME='admin'
LAYER_USER_IDS='1 121'
LAYER_OPS='create read update delete'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"

