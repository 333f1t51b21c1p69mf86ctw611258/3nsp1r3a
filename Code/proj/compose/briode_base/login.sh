#! /bin/bash
INST_NAME=$1
INST_ID=`docker ps |grep $INST_NAME |grep -v 'admin' |grep -v '_' |awk '{print $1}'`
docker exec -u 0 -it $INST_ID /bin/bash

