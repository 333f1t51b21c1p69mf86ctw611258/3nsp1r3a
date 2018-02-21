#! /bin/bash
# undo workflow approval by modifying database
. ./env.sh

if [ ! -z ${DEBUG} ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"
fi

if [ $# -ne 2 ]; then
    echo "Usage: $0 <appname> <id>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

APPNAME=$1
SUBJECT_ID=$2

#Follow the same procedure done before
#mysql> select id,state,state_new,action,username,assignee,prev_assignee from workflow_event_logs where subject_id=644;
#+------+-------+-----------+---------+------------+-----------+---------------+
#| id   | state | state_new | action  | username   | assignee  | prev_assignee |
#+------+-------+-----------+---------+------------+-----------+---------------+
#| 1989 | 0     | 1         | next    | mmarciniak | dcohen    | mmarciniak    |
#| 1991 | 1     | 1         | create  | mmarciniak | dcohen    | dcohen        |
#| 1997 | 1     | 2         | approve | mmarciniak | tnakamura | dcohen        |
#+------+-------+-----------+---------+------------+-----------+---------------+

QUERY_LOG_CHECK="select id,subject_id,action,prev_assignee,state from workflow_event_logs where subject_id=$SUBJECT_ID order by id desc limit 1;"
# return value example
# id subject_id action 25 10 approve dcohen
LOG_CHECK_RES=`$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "$QUERY_LOG_CHECK"`
#echo "$MYSQLPATH/mysql -u $DBUSER $DBNAME -e $QUERY_LOG_CHECK"
#echo $LOG_CHECK_RES
WFLOG_ID=`echo $LOG_CHECK_RES | awk '{print $6}'`
LAST_ACTION=`echo $LOG_CHECK_RES | awk '{print $8}'`
PREV_ASSIGNEE=`echo $LOG_CHECK_RES | awk '{print $9}'`
STATE=`echo $LOG_CHECK_RES | awk '{print $10}'`

#echo $LAST_ACTION
if [ ${LAST_ACTION} != "approve" ]; then 
    echo "Last action is not approve"
    exit 1
fi

# validation passed, convert approve into 'save' action
# approve does not change nth
WORKFLOW_TABLENAME=wf${APPNAME,,}s
QUERY_UPDATE_WFTABLE="update $WORKFLOW_TABLENAME set summary='create', assignee='$PREV_ASSIGNEE', prev_assignee='$PREV_ASSIGNEE', state=$STATE, prev_state=$STATE where subject_id=$SUBJECT_ID;"
QUERY_UPDATE_WFLOGTABLE="update workflow_event_logs set state_new=$STATE,action='create',assignee='$PREV_ASSIGNEE' where id=$WFLOG_ID;"
UPDATE_WFTABLE_RES=`$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "$QUERY_UPDATE_WFTABLE"`
UPDATE_WFLOGTABLE_RES=`$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "$QUERY_UPDATE_WFLOGTABLE"`

if [ ! -z ${UPDATE_WFTABLE_RES} ]; then
    echo "Workflow table update failed"
    exit 1
fi
if [ ! -z ${UPDATE_WFLOGTABLE_RES} ]; then
    echo "Workflow log table update failed"
    exit 1
fi

# add 'cancelled' at the end of comment list
bash admin_add_comment.sh $APPNAME $SUBJECT_ID undo_approval
