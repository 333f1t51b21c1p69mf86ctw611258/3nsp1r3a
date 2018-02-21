#! /bin/bash
. ./env.sh

#DEBUG=1
if [ ! -z ${DEBUG} ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"
fi

if [ $# -ne 3 ]; then
    echo "Usage: $0 <group_name> <list_of_manager> <list_of_members>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

MANAGER_LAYER_NAME=${1}_manager
MEMBERS_LAYER_NAME=${1}_member
MANAGERS=$2
MEMBERS=$3

RES_DEL1=`redis-cli -h $REDISHOST del $MANAGER_LAYER_NAME | awk '{print $1}'`
RES_DEL2=`redis-cli -h $REDISHOST del $MEMBERS_LAYER_NAME | awk '{print $1}'`
RES_DEL3=`redis-cli -h $REDISHOST lpush $MANAGER_LAYER_NAME $MANAGERS | awk '{print $1}'`
RES_DEL4=`redis-cli -h $REDISHOST lpush $MEMBERS_LAYER_NAME $MEMBERS | awk '{print $1}'`

if [ \( "${RES_DEL1}" == "ERR" \) -o \( "${RES_DEL2}" == "ERR" \) -o \( "${RES_DEL3}" == "ERR" \) -o \( "${RES_DEL4}" == "ERR" \) ]; then
    echo "Error occurred in updating configuration"
fi
