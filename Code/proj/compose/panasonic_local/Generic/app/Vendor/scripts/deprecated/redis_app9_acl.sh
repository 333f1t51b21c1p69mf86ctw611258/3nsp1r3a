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

# User Layers
LAYER_ROOT='/'
LAYER_NAME='user'
LAYER_USER_IDS='13 14 15 17 18 19 21 22 117 125'
LAYER_OPS='all'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"
LAYER_ROOT='user'
LAYER_NAME='admin'
LAYER_USER_IDS='1 16 23'
LAYER_OPS='create read update'
./redis_create_layer.sh $PLUGIN_NAME $LAYER_ROOT $LAYER_NAME "$LAYER_USER_IDS" "$LAYER_OPS"

GROUP=1
GROUP1='New Jersey'
GROUP1_MANAGERS='13'
GROUP1_USERS='14 15'
./redis_create_group.sh $GROUP "$GROUP1" $GROUP1_MANAGERS "$GROUP1_USERS"
GROUP=2
GROUP2='Chicago'
GROUP2_MANAGERS='16'
GROUP2_USERS='17 18 19'
./redis_create_group.sh $GROUP "$GROUP2" $GROUP2_MANAGERS "$GROUP2_USERS"
GROUP=3
GROUP3='Los Angeles'
GROUP3_MANAGERS='117'
GROUP3_USERS='21 22'
./redis_create_group.sh $GROUP "$GROUP3" $GROUP3_MANAGERS "$GROUP3_USERS"

