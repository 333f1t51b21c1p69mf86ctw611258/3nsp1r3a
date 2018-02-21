#! /bin/bash
. ./env.sh

for i in {37..86}
do
    bash ./redis_config_user.sh $i App29
done

