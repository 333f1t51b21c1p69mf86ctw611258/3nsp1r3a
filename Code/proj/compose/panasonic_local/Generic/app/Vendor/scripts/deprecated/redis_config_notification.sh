#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 2 ]; then
    echo "usage: redis_config_notification.sh <pluginName> <enableEmail> <emailAddress(optional)> <label|url_pairs(optional, space separated)>"
    exit 1
fi

PLUGIN_NAME=$1
EMAIL_MODE=$2
EMAIL_ADDRESS=$3
LABEL_URLS=$4
#####################################################

redis-cli hset "$PLUGIN_NAME"_notification email_mode $EMAIL_MODE
redis-cli hset "$PLUGIN_NAME"_notification email_address $EMAIL_ADDRESS

LABEL_URL=' ' read -ra IDS <<< "$LABEL_URLS"
for i in "${IDS[@]}"; do
    LABEL_SP_URL=`echo "$i" |tr "|" " "`
    redis-cli hset "$PLUGIN_NAME"_notification_email_urls $LABEL_SP_URL
done


