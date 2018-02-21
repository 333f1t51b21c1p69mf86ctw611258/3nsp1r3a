#! /bin/bash
. ./env.sh
CAKEBINPATH=../../Console/cake
if [ $# -lt 1 ]; then
    echo "Usage: $0 <plugin_name> <reload(optional)>"
    exit 1
fi
PLUGINNAME=$1
RELOAD=$2

# Clean ACL database/Redis config when exists
$CAKEBINPATH acl delete aco "$PLUGINNAME" 2> /dev/null
$CAKEBINPATH acl delete aro "$PLUGINNAME"-user 2> /dev/null
$CAKEBINPATH acl delete aro "$PLUGINNAME"-controller 2> /dev/null
$CAKEBINPATH acl delete aro "$PLUGINNAME"-approver 2> /dev/null
$CAKEBINPATH acl delete aro "$PLUGINNAME"-manager 2> /dev/null
$CAKEBINPATH acl delete aro "$PLUGINNAME"-admin 2> /dev/null

# normally load ACL configuration onto Redis
# skip loading acl file when reload is set
if [ "$RELOAD" == '' ]; then
    bash redis_clean_acl.sh $PLUGINNAME
    bash redis_load_acl_base.sh $PLUGINNAME
    bash redis_load_app_config.sh $PLUGINNAME
    #ACL_SCRIPT=redis_${1,,}_acl.sh
    #bash $ACL_SCRIPT $PLUGINNAME
else
    #ACL_SCRIPT=redis_${1,,}_acl_base.sh
    bash redis_load_acl_base.sh $PLUGINNAME
fi

appMode=`redis-cli -h $REDISHOST hget $PLUGINNAME app_kind`
DBMODEL=`redis-cli -h $REDISHOST hget $PLUGINNAME model_name`
if [ "$appMode" == "workflow" ]; then
    WORKFLOWMODEL=`redis-cli -h $REDISHOST hget "$PLUGINNAME"_workflow model_name`
    COMMENTMODEL=`redis-cli -h $REDISHOST hget "$PLUGINNAME"_workflow comment_model_name`
fi

# ACO
echo "pass1"
$CAKEBINPATH acl create aco / $PLUGINNAME
echo $CAKEBINPATH acl create aco / $PLUGINNAME
echo "pass2"
echo $CAKEBINPATH acl create aco $PLUGINNAME $DBMODEL
$CAKEBINPATH acl create aco $PLUGINNAME $DBMODEL
if [ "$appMode" == "workflow" ]; then
    $CAKEBINPATH acl create aco $PLUGINNAME $WORKFLOWMODEL
    $CAKEBINPATH acl create aco $PLUGINNAME $COMMENTMODEL
fi

# ARO
TMPQUEUE_KEY=$PLUGINNAME'_tmp_queue'
PARENT_LAYER_ACL=''
redis-cli -h $REDISHOST rpush $TMPQUEUE_KEY /
while [ `redis-cli -h $REDISHOST llen $TMPQUEUE_KEY` -gt 0 ]
do
    echo 'pass4-lpop'
    CURRENT_LAYER=`redis-cli -h $REDISHOST lpop $TMPQUEUE_KEY`
    CURRENT_LAYER_ACL=$PLUGINNAME'-'$CURRENT_LAYER
    if [ "$CURRENT_LAYER" == '/' ]; then 
        CURRENT_LAYER_ACL=$CURRENT_LAYER
    fi
    CURRENT_LAYER_USERS_KEY=$PLUGINNAME'_acl_'$CURRENT_LAYER
    CURRENT_LAYER_OPS_KEY=$PLUGINNAME'_acl_ops_'$CURRENT_LAYER
    echo 'pass4-exists, ops_key='$CURRENT_LAYER_OPS_KEY
    if [ `redis-cli -h $REDISHOST exists $CURRENT_LAYER_OPS_KEY` == 1 ]; then
        echo 'pass4-exists in if clause'
        echo 'creating layer aro:' $PARENT_LAYER_ACL '/' $CURRENT_LAYER_ACL
        $CAKEBINPATH acl create aro $PARENT_LAYER_ACL $CURRENT_LAYER_ACL
        NUM_OPS=`redis-cli -h $REDISHOST llen $CURRENT_LAYER_OPS_KEY`
        for (( CUR_OP=0; CUR_OP<NUM_OPS; CUR_OP++ )); do
            OP=`redis-cli -h $REDISHOST lindex $CURRENT_LAYER_OPS_KEY $CUR_OP`
            echo 'granting the layer for ' $OP
            $CAKEBINPATH acl grant $CURRENT_LAYER_ACL $DBMODEL $OP
        done
        NUM_USERS=`redis-cli -h $REDISHOST llen $CURRENT_LAYER_USERS_KEY`
        for (( CUR_USER=0; CUR_USER<NUM_USERS; CUR_USER++ )); do
            USER=`redis-cli -h $REDISHOST lindex $CURRENT_LAYER_USERS_KEY $CUR_USER`
            echo 'creating user aro:' $USER
            $CAKEBINPATH acl create aro $CURRENT_LAYER_ACL $USER
        done
    fi

    CURRENT_LAYER_TREE_KEY=$PLUGINNAME'_acl_layer_'$CURRENT_LAYER
    echo 'pass4-layer_exists'
    if [ `redis-cli -h $REDISHOST exists $CURRENT_LAYER_TREE_KEY` == 1 ]; then
        echo 'pass4-layer_exists in if clause'
        LAYER_TO_ADD=`redis-cli -h $REDISHOST spop $CURRENT_LAYER_TREE_KEY`
        redis-cli -h $REDISHOST rpush $TMPQUEUE_KEY $LAYER_TO_ADD
    fi

    #PARENT_LAYER_ACL=$CURRENT_LAYER_ACL
    PARENT_LAYER_ACL='/'
done

