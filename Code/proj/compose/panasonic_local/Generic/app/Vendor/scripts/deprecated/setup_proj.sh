#! /bin/bash
. ./env.sh
if [ $# -ne 1 ]; then
    echo "Usage: $0 <project_name>"
    exit 1
fi

PROJECT_NAME=$1

# cleanup redis
bash redis_clear.sh
bash redis_global.sh
bash redis_config_user_profile.sh disable  # disable profile picture

# loop thru projects, call config scripts one by one
$PYTHONEXEPATH/python3 setup_proj.py $PROJECT_NAME

