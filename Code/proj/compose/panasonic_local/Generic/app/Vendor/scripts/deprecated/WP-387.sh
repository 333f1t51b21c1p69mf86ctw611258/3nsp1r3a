#! /bin/bash
. ./env.sh

TS_FORMAT=`date +%Y%m%d_%H%I%S`
DATE_FORMAT=`date +%Y%m%d`
mkdir -p ~/update/$DATE_FORMAT/
echo 'running MYSQL queries'
mysqldump -u $DBUSER $DBNAME > ~/update/$DATE_FORMAT/${DBNAME}_${TS_FORMAT}.sql
mysql -u $DBUSER $DBNAME -e "delete from attribute_event_logs where subject_id>=350 and update_time < '2015-05-19 00:00:00';" 2>&1 
mysql -u $DBUSER $DBNAME -e "delete from attribute_event_logs where subject_id>=275 and update_time < '2015-05-28 00:00:00';" 2>&1
mysql -u $DBUSER $DBNAME -e "delete from attribute_event_logs where subject_id>=112 and update_time < '2015-04-02 00:00:00';" 2>&1
mysql -u $DBUSER $DBNAME -e "delete from attribute_event_logs where id=56 or id=55 or (id<=832 and id>=829) or (id>=1150 and id<=1153) or (id>=1166 and id<=1169) or id=1164 or id=1259;" 2>&1
mysql -u $DBUSER $DBNAME -e "update wfapp2s a left join (select subject_id,min(update_time) as ut from attribute_event_logs group by subject_id) b on a.subject_id = b.subject_id set a.created_at=b.ut;" 2>&1

# Synchronize Comment and History timestamp
bash SyncCommAndHistory.sh

# Validate database
echo "generating db output to WP-387.log"
mysql -u $DBUSER $DBNAME -e "select id,subject_id,update_time from attribute_event_logs order by subject_id;" 2>&1 > WP-387.log
mysql -u $DBUSER $DBNAME -e "select id,subject_id,created_at from wfapp2s order by created_at;" 2>&1 >> WP-387.log

