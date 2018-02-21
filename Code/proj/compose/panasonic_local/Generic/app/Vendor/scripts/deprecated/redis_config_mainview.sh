#! /bin/bash
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 2 ]; then
    echo "usage: redis_config_mainview.sh <plugin_name> <mainview_columns> <sort_by_columns> <asc/desc-s>"
    echo "  *<sort_by> and <asc/desc> are optional"
    exit 1
fi

PLUGIN_NAME="$1"
MAINVIEW_COLUMNS="$2"
SORTBY_COLUMNS="$3"
SORTBY_ORDERS="$4"
#echo 'Mainview_columns=' $MAINVIEW_COLUMNS
#####################################################

# Excel import initial configuration
redis-cli ltrim "$PLUGIN_NAME"_mainview_list 1 0
redis-cli rpush "$PLUGIN_NAME"_mainview_list $MAINVIEW_COLUMNS

if [ -z "$SORTBY_COLUMNS" ]; then
    # sort condition not set, exiting
    exit 0
fi
redis-cli ltrim "$PLUGIN_NAME"_mainview_sort_colname 1 0
redis-cli rpush "$PLUGIN_NAME"_mainview_sort_colname $SORTBY_COLUMNS

redis-cli ltrim "$PLUGIN_NAME"_mainview_sort_order 1 0
for i in ${SORTBY_ORDERS[@]}
do
    ORDER=`echo "$i" |tr "_" " "`
    redis-cli rpush "$PLUGIN_NAME"_mainview_sort_order "$ORDER"
    echo "$i"
done

