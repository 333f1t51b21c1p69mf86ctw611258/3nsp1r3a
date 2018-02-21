#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 2 ]; then
    echo "usage: redis_config_report.sh <plugin_name> <report_mode>"
    exit 1
fi

PLUGIN_NAME="$1"
REPORT_MODE=$2
#####################################################

# Excel import initial configuration
redis-cli hset "$PLUGIN_NAME" report_mode $REPORT_MODE
