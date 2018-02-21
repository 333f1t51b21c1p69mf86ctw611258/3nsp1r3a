#! /bin/bash
# disable useraccount by adding '_' to the first character of account name
. ./env.sh

TS=`date +%Y%m%d-%H%M%S`
SCRIPT_LOG=/tmp/admin_delete_user_${TS}.log

if [ ! -z ${DEBUG} ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"
fi

if [ $# -ne 1 ]; then
    echo "Usage: $0 <username>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

bash admin_backup_database.sh

USERNAME=$1
NEW_USERNAME=_$1

NEW_DN=`$XAMPPBINPATH/mysql -h $DBHOST -u $DBUSER genericdata -e "select dn from users where username like '$USERNAME';"|grep $USERNAME| awk '{print $1}'|sed "s/$USERNAME/_$USERNAME/"`
#echo $NEW_DN
#exit 1
$XAMPPBINPATH/mysql -h $DBHOST -u $DBUSER genericdata -e "update users set username='"$NEW_USERNAME"',dn='"$NEW_DN"' where username like '"$USERNAME"';"


# Delete user from layers, groups
USER_ID=`$XAMPPBINPATH/mysql -h $DBHOST -u $DBUSER genericdata -e "select id,username from users where username like '_$USERNAME';"|grep $USERNAME| awk '{print $1}'`
USER_ARO=User.$USER_ID

# get a list of groups and find a user to be deleted
REDIS_OUT_TMP=/tmp/redis_keys.log
redis-cli -h $REDISHOST keys App_acl_group* > $REDIS_OUT_TMP
REDIS_GROUPS=`cat $REDIS_OUT_TMP`
for gname in $REDIS_GROUPS; do
    echo redis-cli -h $REDISHOST lrem $gname 1 $USER_ARO >> $SCRIPT_LOG 2>&1
    DELETED=`redis-cli -h $REDISHOST lrem $gname 1 $USER_ARO`
    echo 'lrem status: '$DELETED >> $SCRIPT_LOG 2>&1
done

# get a list of layers and find a user to be deleted
REDIS_LAYER_KINDS='admin controller approver user manager'
TO_DELETE="0"
for layer in $REDIS_LAYER_KINDS; do
    REDIS_KEY_PTN="App*_acl_$layer"
    redis-cli -h $REDISHOST keys $REDIS_KEY_PTN > $REDIS_OUT_TMP
    REDIS_LAYERS=`cat $REDIS_OUT_TMP`
    for lname in $REDIS_LAYERS; do
        echo redis-cli -h $REDISHOST lrem $lname 1 $USER_ARO >> $SCRIPT_LOG 2>&1
        DELETED=`redis-cli -h $REDISHOST lrem $lname 1 $USER_ARO`
        echo 'lrem status: '$DELETED >> $SCRIPT_LOG 2>&1
        if [ "$DELETED" == "1" ]; then
            TO_DELETE="1"
        fi
    done 
done
# if user deleted, regenerate ACL tree
if [ "$TO_DELETE" == "1" ]; then
    echo "user deleted, reloading acl" >> $SCRIPT_LOG 2>&1
    bash admin_reload_acl.sh >> $SCRIPT_LOG 2>&1
fi

