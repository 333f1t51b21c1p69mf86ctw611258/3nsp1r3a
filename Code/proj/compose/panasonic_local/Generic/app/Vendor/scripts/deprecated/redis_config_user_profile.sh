#! /bin/bash

cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 1 ]; then
    echo "usage: redis_config_user_profile.sh <mode_profile_picture>"
    exit 1
fi

MODE_PROFILE_PICTURE=$1

redis-cli hset App_user_profile picture $MODE_PROFILE_PICTURE

