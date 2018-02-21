#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 4 ]; then
    echo "usage: redis_create_group.sh <group_id> <group_name> <manager_list> <member_list>"
    exit 1
fi


GROUP_ID=$1
GROUP_NAME=$2
GROUP_MANAGERS=$3
GROUP_MEMBERS=$4

redis-cli hset App_acl_groups $GROUP_ID "$GROUP_NAME"
#IFS=' ' read -ra IDS <<< $GROUP_MANAGERS
for i in $GROUP_MANAGERS; do
    redis-cli rpush App_acl_group"$GROUP_ID"_manager User.$i
done
#echo "Group_members=" $GROUP_MEMBERS
#IFS=' ' read -ra IDS <<< $GROUP_MEMBERS
for i in $GROUP_MEMBERS; do
    redis-cli rpush App_acl_group"$GROUP_ID"_member User.$i
done

