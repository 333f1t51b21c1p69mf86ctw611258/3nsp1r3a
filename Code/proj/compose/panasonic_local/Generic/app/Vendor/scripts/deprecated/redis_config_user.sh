#! /bin/bash

cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 2 ]; then
    echo "usage: redis_config_user.sh <userid> <appname>"
    exit 1
fi

USER_ID=$1
USER_ACO="User.$1"
APP_NAME=$2

redis-cli hset App_user_loginapp $USER_ACO $APP_NAME

