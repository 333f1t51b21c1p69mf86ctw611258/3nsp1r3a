#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 5 ]; then
    echo "usage: redis_create_layer.sh <plugin_name> <layer_parent> <layer_name> <layer_user_ids> <layer_ops>"
    exit 1
fi

PLUGIN_NAME=$1
LAYER_PARENT=$2
LAYER_NAME=$3
LAYER_USER_IDS=$4
LAYER_OPS=$5

#####################################################
redis-cli sadd "$PLUGIN_NAME"_acl_layer_"$LAYER_PARENT" $LAYER_NAME
#IFS=' ' read -ra IDS <<< "$LAYER_USER_IDS"

REDIS_ACL_LAYER_KEY="$PLUGIN_NAME"_acl_"$LAYER_NAME"
bash redis_push_user_to_acl_layer.sh "$LAYER_USER_IDS" "$REDIS_ACL_LAYER_KEY"

REDIS_ACL_OPS_LAYER_KEY="$PLUGIN_NAME"_acl_ops_"$LAYER_NAME"
bash redis_push_op_to_acl_op_layer.sh "$LAYER_OPS" "$REDIS_ACL_OPS_LAYER_KEY"
