#! /bin/bash
. ./env.sh
if [ $# -ne 1 ]; then
    echo "Usage: $0 <plugin_name>"
    exit 1
fi

PLUGINNAME=$1
TMPFILE=/tmp/_redis_clean.sh
rm -f $TMPFILE

redis-cli -h $REDISHOST keys "${PLUGINNAME}_acl_*" |grep -v _db_ | grep -v _excel_ |awk "BEGIN{printf(\"#! /bin/sh\n\");}{printf(\"redis-cli -h $REDISHOST del %s\n\",\$1);}" > $TMPFILE
redis-cli -h $REDISHOST keys "${PLUGINNAME}_workflow_post_approve*" |grep -v _db_ | grep -v _excel_ |awk "{printf(\"redis-cli -h $REDISHOST del %s\n\",\$1);}" >> $TMPFILE
redis-cli -h $REDISHOST keys "${PLUGINNAME}_create_as*" |grep -v _db_ | grep -v _excel_ |awk "{printf(\"redis-cli -h $REDISHOST del %s\n\",\$1);}" >> $TMPFILE
redis-cli -h $REDISHOST keys "${PLUGINNAME}_approval_op_as*" |grep -v _db_ | grep -v _excel_ |awk "{printf(\"redis-cli -h $REDISHOST del %s\n\",\$1);}" >> $TMPFILE
echo >> $TMPFILE
sed -i ":a;N;\$!ba;s/redis-cli -h $REDISHOST del \n//g" $TMPFILE
chmod +x $TMPFILE
bash $TMPFILE

