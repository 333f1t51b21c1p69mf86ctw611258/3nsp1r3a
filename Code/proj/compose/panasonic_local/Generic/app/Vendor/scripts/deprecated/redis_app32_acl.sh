#! /bin/bash
. ./env.sh

$PYTHONEXEPATH/python3 redis_load_app_acl_base.py App32 $APP_CONFIG_DIR/App32.json
$PYTHONEXEPATH/python3 redis_load_app_config.py App32 $APP_CONFIG_DIR/App32.json

#if [ $# -ne 1 ]; then
#    echo "Usage: $0 <plugin_name>"
#    exit 1
#fi
#
#PLUGIN_NAME=$1
#
#bash redis_app32_acl_base.sh
#
#bash redis_app32_acl_defaultuser.sh

