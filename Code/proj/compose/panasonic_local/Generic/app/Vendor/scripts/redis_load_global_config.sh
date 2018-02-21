#! /bin/bash
. ./env.sh

if [ $# -ne 1 ]; then
    echo "Usage: $0 <project_name>"
    exit 1
fi

PROJ_NAME=$1

$PYTHONEXEPATH/python3 redis_load_global_config.py $PROJ_NAME $PROJ_CONFIG_DIR/${PROJ_NAME}.json

