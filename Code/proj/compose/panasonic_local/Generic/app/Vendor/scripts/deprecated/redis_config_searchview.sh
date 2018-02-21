#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 2 ]; then
    echo "usage: redis_config_searchview.sh <plugin_name> <searchview_columns>"
    exit 1
fi

PLUGIN_NAME="$1"
SEARCHVIEW_COLUMNS=$2
#####################################################

# Excel import initial configuration
redis-cli ltrim "$PLUGIN_NAME"_searchview_list 1 0
redis-cli rpush "$PLUGIN_NAME"_searchview_list $SEARCHVIEW_COLUMNS
