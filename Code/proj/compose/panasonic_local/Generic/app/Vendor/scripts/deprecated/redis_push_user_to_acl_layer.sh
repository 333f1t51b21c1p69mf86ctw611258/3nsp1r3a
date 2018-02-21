#! /bin/bash
. ./env.sh
if [ $# -ne 2 ]; then
    echo "usage: $0 <layer_user_ids> <redis_acl_layer_key>"
    exit 1
fi

LAYER_USER_IDS=$1
REDIS_ACL_LAYER_KEY=$2

for i in $LAYER_USER_IDS; do
    redis-cli rpush $REDIS_ACL_LAYER_KEY User.$i
done

