#! /bin/sh
. ./env.sh

bash redis_load_app_acl_base.sh App2
bash redis_load_app_config.sh App2

#$PYTHONEXEPATH/python3 redis_load_app_acl_base.py App2 appconfig/App2.json
#$PYTHONEXEPATH/python3 redis_load_app_config.py App2 appconfig/App2.json
#
#if [ $# -ne 1 ]; then
#    echo "Usage: $0 <plugin_name>"
#    exit 1
#fi
#
#PLUGIN_NAME=$1
#
#bash redis_app2_acl_base.sh
#
#bash redis_app2_acl_defaultuser.sh

