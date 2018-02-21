#! /bin/bash
. ./env.sh

if [ $# -ne 1 ]; then
    echo "Usage: $0 <plugin_name>"
    exit 1
fi

PLUGIN_NAME=$1

$PYTHONEXEPATH/python3 redis_load_app_base.py $PLUGIN_NAME $APP_CONFIG_DIR/${PLUGIN_NAME}.json

