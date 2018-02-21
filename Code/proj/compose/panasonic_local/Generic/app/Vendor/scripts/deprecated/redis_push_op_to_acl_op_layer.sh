#! /bin/bash
. ./env.sh
if [ $# -ne 2 ]; then
    echo "usage: $0 <layer_ops> <redis_acl_ops_layer_key>"
    exit 1
fi

LAYER_OPS=$1
REDIS_ACL_OPS_LAYER_KEY=$2

for op in $LAYER_OPS; do
    redis-cli rpush $REDIS_ACL_OPS_LAYER_KEY $op
done

