#! /bin/bash
. ./env.sh

appNames=($1)

if [ -z $appNames ]; then
    echo "usage: gen_app.sh \"<list_of_app#s>\""
    exit 1
fi

for i in ${appNames[@]}
do
    echo "creating App$i..."
    bash ./addApp.sh "App$i"
    bash ./acl_app.sh "App$i"
done
