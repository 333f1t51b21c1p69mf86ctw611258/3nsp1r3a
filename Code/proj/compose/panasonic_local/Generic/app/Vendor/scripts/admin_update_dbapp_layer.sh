#! /bin/bash
. ./env.sh

if [ ! -z ${DEBUG} ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"
fi

if [ $# -ne 2 ]; then
    echo "Usage: $0 <layer_redis_key> <list_of_members_aro>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

LAYER_REDIS_KEY=$1
MEMBERS=$2
SCRIPT_LOG=/tmp/admin_update_layer.log
echo $MEMBERS > $SCRIPT_LOG

RES_DEL=`redis-cli -h $REDISHOST del $LAYER_REDIS_KEY| awk '{print $1}'`
echo redis-cli -h $REDISHOST lpush $LAYER_REDIS_KEY $MEMBERS >> $SCRIPT_LOG
RES_LPUSH=`redis-cli -h $REDISHOST lpush $LAYER_REDIS_KEY $MEMBERS| awk '{print $1}'`
echo `redis-cli -h $REDISHOST lrange $LAYER_REDIS_KEY 0 -1` >> $SCRIPT_LOG

if [ \( "${RES_DEL}" == "ERR" \) -o \( "${RES_LPUSH}" == "ERR" \) ]; then
    echo "Error in updating configuration"
    exit 1
fi

bash admin_reload_acl.sh >> $SCRIPT_LOG 2>&1

