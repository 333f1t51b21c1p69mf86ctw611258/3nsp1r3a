#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 2 ]; then
    echo "usage: redis_config_cols_security.sh <plugin_name> <cols_restricted_access>"
    exit 1
fi

PLUGIN_NAME=$1
MODEL_NAME="Attr${PLUGIN_NAME,,}"
COLS_RESTRICTED_ACCESS=$2

#####################################################
READ_PROHIBITED="$PLUGIN_NAME"_db_read_prohibited
READ_PROHIBITED_SET=_"$PLUGIN_NAME"_rp_1
redis-cli hset $PLUGIN_NAME read_prohibited $READ_PROHIBITED
redis-cli hset $READ_PROHIBITED $PLUGIN_NAME/$MODEL_NAME $READ_PROHIBITED_SET
#IFS=' ' read -ra IDS <<< "$COLS_RESTRICTED_ACCESS"
for column in $COLS_RESTRICTED_ACCESS; do
    redis-cli sadd $READ_PROHIBITED_SET $column
done
