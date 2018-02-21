#! /bin/bash
. ./env.sh
if [ $# -ne 1 ]; then
    echo "Usage: $0 <project_name>"
    exit 1
fi

PROJECT_NAME=$1

# for now only projectparams are safe to reload
bash redis_load_proj_param.sh $PROJECT_NAME

