#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 3 ]; then
    echo "usage: redis_config_workflow.sh <pluginName> <email> <single_excel_up_down> <label|url_pairs(optional)>"
    exit 1
fi

PLUGIN_NAME=$1
EMAIL_MODE=$2
EXCEL_UP_DOWN=$3
LABEL_URLS=$4
#####################################################

redis-cli hset "$PLUGIN_NAME"_workflow email_mode $EMAIL_MODE
redis-cli hset "$PLUGIN_NAME"_workflow single_excel_up_down $EXCEL_UP_DOWN

LABEL_URL=' ' read -ra IDS <<< "$LABEL_URLS"
for i in "${IDS[@]}"; do
    LABEL_SP_URL=`echo "$i" |tr "|" " "`
    redis-cli hset "$PLUGIN_NAME"_workflow_email_urls $LABEL_SP_URL
done


