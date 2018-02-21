#! /bin/bash
. ./env.sh
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 4 ]; then
    echo "usage: runTrigger.sh <plugin_name> <subject_id> <before_or_after> <approval_action>"
    exit 1
fi

PLUGIN_NAME_LOWERCASE=${1,,}
SUBJECT_ID=$2
BEFORE_OR_AFTER=$3
APPROVAL_ACTION=$4

# script name to execute
#
# if before
#   pre_<approval_action>_<plugin_name>.sh
# if after 
#   post_<approval_action>_<plugin_name>.sh
SCRIPT_FILENAME=''
SCRIPT_FILENAME_BASE="$APPROVAL_ACTION"'_'"$PLUGIN_NAME_LOWERCASE".sh
if [ "$BEFORE_AND_AFTER" == "after" ]; then
    SCRIPT_FILENAME='pre_'$SCRIPT_FILENAME_BASE
else # [ $BEFORE_AND_AFTER == "after" ]
    SCRIPT_FILENAME='post_'$SCRIPT_FILENAME_BASE
fi

echo $SCRIPT_FILENAME
bash $SCRIPT_FILENAME $SUBJECT_ID
