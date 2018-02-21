#! /bin/bash
. ./env.sh

PLUGINNAME='App2'
GROUPDIR_KEY='App_acl_groups'
#CREATE_AS_KEY="$PLUGINNAME"_workflow_create_as_allowed_users

GROUP_ID=$BASE_GROUP_ID_APPROVAL
#HSCAN_QUERY_RES=`$HSCAN_QUERY`
while true; do
    HSCAN_QUERY="redis-cli hscan $GROUPDIR_KEY 0 match $GROUP_ID"
    HSCAN_QUERY_RES=`$HSCAN_QUERY`
    # if entry exists returns 3, otherwise 1
    if [[ `echo $HSCAN_QUERY_RES|wc -w` -eq 1 ]]; then
        break
    fi
    #if [[ $HSCAN_QUERY_RES =~ $CREATE_AS_KEY ]]; then
    #    TODO
    ##fi
    (( GROUP_ID ++ ))
done
echo $GROUP_ID
