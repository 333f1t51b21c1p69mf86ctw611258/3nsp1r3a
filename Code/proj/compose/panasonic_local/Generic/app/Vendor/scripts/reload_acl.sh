#! /bin/bash

# Usage:
#  Run this script after modifying redis_acl.sh

. ./env.sh
if [ $# -ne 1 ]; then
    echo "Usage: $0 <project_name>"
    exit 1
fi

PROJECT_NAME=$1
PLUGINNAMES=`bash redis_dump_plugins.sh $PROJECT_NAME`
#PLUGINNAMES="$1"

./redis_clean_groups.sh
for name in ${PLUGINNAMES[@]}
do
    #./redis_clean_acl.sh $name
    ./acl_app.sh $name
done

