#! /bin/bash
cnt=0
for arg in "$@"
do
    let "cnt += 1"
done
if [ $cnt -lt 1 ]; then
    echo "usage: redis_set_ui_options_list.sh <LIST_NAME> <LIST_VALUE> <LIST_VALUE>..."
    echo "number of arguments should be greater than 1"
    exit 1
fi

VALUE=("$@")
LIST_NAME=$1
LIST_KEY=App_ui_options_"$1"

#####################################################

for ((i=1; i<$#; i++))
do
    redis-cli hset App_ui_options $LIST_NAME $LIST_KEY
    LIST_VALUE="${VALUE[i]}"
    redis-cli rpush $LIST_KEY "$LIST_VALUE"
done
