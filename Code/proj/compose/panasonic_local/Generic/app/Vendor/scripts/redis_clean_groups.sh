#! /bin/bash
. ./env.sh

TMPFILE=/tmp/_redis_clean_groups.sh
rm -f $TMPFILE

redis-cli -h $REDISHOST keys "App_acl_*" |grep -v _db_ | grep -v _excel_ |awk "BEGIN{printf(\"#! /bin/sh\n\");}{printf(\"redis-cli -h ${REDISHOST} del %s\n\",\$1);}" > $TMPFILE
echo >> $TMPFILE
sed -i ":a;N;\$!ba;s/redis-cli -h $REDISHOST del \n//g" $TMPFILE
chmod +x $TMPFILE
bash $TMPFILE

