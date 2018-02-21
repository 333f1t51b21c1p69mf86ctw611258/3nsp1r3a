#! /bin/bash 
. ./env.sh

cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 2 ]; then
    echo "usage: redis_app_login_redirect_by_usertype.sh <usertype> <url_to_redirect>"
    exit 1
fi

USERTYPE=$1
URL_TO_REDIRECT=$2

redis-cli hset App_login_redirect_by_usertype $USERTYPE $URL_TO_REDIRECT

