#! /bin/sh
. ./env.sh
cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -ne 1 ]; then
    echo "usage: post_approve_app2.sh <subject_id>"
    exit 1
fi

SUBJECT_ID=$1

timestamp=$(date +%y%m%d_%H%M%S)
OUTFILE=/tmp/_post_approve_app2_"$timestamp".out
# query returns none when no preceding task of the day exists
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $RINGIDBNAME -e "select concat(DATE_FORMAT(CURDATE(),'%m%d%y-'),S) from (SELECT distinct IF(CHAR_LENGTH(Sheet_No)=0, '00', LPAD(CAST((CAST(RIGHT(Sheet_No,2) AS UNSIGNED)+1) AS CHAR(2)),2,'0')) as S FROM attrapp2s where LEFT(Sheet_No,6) like DATE_FORMAT(CURDATE(),'%m%d%y')) T order by S desc limit 1 INTO OUTFILE '$OUTFILE'"
NEXT_SHEET_ID=`cat $OUTFILE`
if [ "$NEXT_SHEET_ID" == "" ]; then
    # assign 01 for the day
    NEXT_SHEET_ID=`date +%m%d%y`-01
fi
echo $NEXT_SHEET_ID
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $RINGIDBNAME -e "update attrapp2s set Sheet_No='$NEXT_SHEET_ID' where id=$SUBJECT_ID"
