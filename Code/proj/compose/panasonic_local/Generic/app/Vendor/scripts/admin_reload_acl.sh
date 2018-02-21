#! /bin/bash
. ./env.sh

APPS_ACL=`redis-cli -h $REDISHOST keys App*_acl`
APPS=`echo $APPS_ACL |sed 's/_acl//g'`
for name in ${APPS[@]}; do
    bash acl_app.sh $name reload
done

chmod -R a+w $SCRIPTROOT/../../tmp
