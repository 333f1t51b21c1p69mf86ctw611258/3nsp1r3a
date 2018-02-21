#! /bin/bash
. ./env.sh

# Tomcat configuration (WP-460)
# 
# add following config in web.xml and tomcat-users.xml
# see http://www.avajava.com/tutorials/lessons/how-do-i-use-basic-authentication-with-tomcat.html
if [ 1 != 1 ]; then
apt-get install realpath -y
apt-get install python-pip python-dev build-essential -y
apt-get install python3-pip python3-dev -y
pip install redis
pip3 install redis
fi

# first, change layer config by adding slynum in App2,App32
# then, run deploy.sh
# below is the final step before using the application

# take latest database
#if [ 1 == 1 ]; then
TS=`date +"%Y%m%d_%H%M%S"`
MYSQLDUMPFN=/tmp/genericdata_${TS}.sql
ssh -i ~/PKI/enspirea2.pem ubuntu@panasonic.briode.com "/opt/lampp/bin/mysqldump -u root genericdata > $MYSQLDUMPFN"
scp -i ~/PKI/enspirea2.pem ubuntu@panasonic.briode.com:$MYSQLDUMPFN $MYSQLDUMPFN
$MYSQLPATH/mysql -u root genericdata < $MYSQLDUMPFN

#take latest LDAP snapshot
ssh -i ~/PKI/enspirea2.pem ubuntu@panasonic.briode.com "cd ~/Generic/app/Vendor/scripts; bash ldap_backup.sh"
LDAP_CONFIG_FN=`ssh -i ~/PKI/enspirea2.pem ubuntu@panasonic.briode.com "ls -t  ~/Generic/app/Vendor/projects/data/*.master.ldif|head -n 1"`
LDAP_DATABASE_FN=`ssh -i ~/PKI/enspirea2.pem ubuntu@panasonic.briode.com "ls -t  ~/Generic/app/Vendor/projects/data/*.database.ldif|head -n 1"`
scp -i ~/PKI/enspirea2.pem ubuntu@panasonic.briode.com:$LDAP_CONFIG_FN ../projects/data
scp -i ~/PKI/enspirea2.pem ubuntu@panasonic.briode.com:$LDAP_DATABASE_FN ../projects/data
LDAP_TS=`echo $LDAP_CONFIG_FN | sed 's/\/data\// /' |sed 's/\./ /'|awk '{print $2}'`
bash admin_restore_ldap.sh $LDAP_TS
#fi
exit 1

# modify production data into dev safe
# reset email address
#$MYSQLPATH/mysql -u root genericdata -e "update users set mail='enspirea.dev@gmail.com'"
# change Win-Win ownership from dcohen(105) to slynum(101)
$MYSQLPATH/mysql -u root genericdata -e "update attrapp2s set creator_id='slynum' where creator_id='dcohen'"
$MYSQLPATH/mysql -u root genericdata -e "update wfapp2s set assignee='slynum' where assignee='dcohen'"
$MYSQLPATH/mysql -u root genericdata -e "update users set username='slynum' where username='_slynum'"
$MYSQLPATH/mysql -u root genericdata -e "update users set username='yyamashita' where username='_yyamashita'"
#exit 1
#fi


# Adding new users
$MYSQLPATH/mysql -u root genericdata -e "INSERT INTO users (id,DN,username,title,mail,department,name,usertype,manager,created_at,creator_id,activeflag) VALUES ('134','uid=avolpe,ou=Headquarter,dc=enspirea,dc=com','avolpe','Sales Manager','enspirea.dev@gmail.com','Headquarter','A Volpe','1','Yoichi Yamashita','2016-03-04T09:53:37.582116','LDAP','1')"
$MYSQLPATH/mysql -u root genericdata -e "INSERT INTO users (id,DN,username,title,mail,department,name,usertype,manager,created_at,creator_id,activeflag) VALUES ('135','uid=jvasque,ou=Headquarter,dc=enspirea,dc=com','jvasque','Sales Manager','enspirea.dev@gmail.com','Headquarter','Jose Vasque','1','','2016-03-04T09:53:37.582116','LDAP','1')"
$MYSQLPATH/mysql -u root genericdata -e "INSERT INTO users (id,DN,username,title,mail,department,name,usertype,manager,created_at,creator_id,activeflag) VALUES ('136','uid=tszeto,ou=Headquarter,dc=enspirea,dc=com','tszeto','Sales Manager','enspirea.dev@gmail.com','Headquarter','Tony Szeto','3','Michael Marciniak','2016-03-04T09:53:37.582116','LDAP','1')"

# Updating immediate managers
#jbergen slynum
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Steven Lynum' where username = 'jbergen'"
#mmarciniak slynum
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Steven Lynum' where username = 'mmarciniak'"
#pdecarlo slynum
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Steven Lynum' where username = 'pdecarlo'"
#ahey jbergen
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='John Bergen' where username = 'ahey'"
#fgonzales mmarciniak
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Michael Marciniak' where username = 'fgonzales'"
#dlong  mmarciniak
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Michael Marciniak' where username = 'dlong'"
#dcassanelli jbergen
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='John Bergen' where username = 'dcassanelli'"
#gtakamatsu yyamashita
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Yoichi Yamashita' where username = 'gtakamatsu'"
#slynum tnakamura
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Tomohiro Nakamura' where username = 'slynum'"
#tnakamura jvasque
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Jose Vasque' where username = 'tnakamura'"
#cmanthe mmarciniak
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Michael Marciniak' where username = 'cmanthe'"
#mkarr pdecarlo
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Peter Decarlo' where username = 'mkarr'"
#pfanning  mmarciniak
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Michael Marciniak' where username = 'pfanning'"
#seller pdecarlo
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Peter Decarlo' where username = 'seller'"
#jfellman jbergen
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='John Bergen' where username = 'jfellman'"
#*NEW* yyamashita slynum
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Steven Lynum' where username = 'yyamashita'"
#*NEW* jvasque  None
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'jvasque'"
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Steven Lynum' where username = 'dcohen'"
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Steven Lynum' where username = 'gwisniewski'"
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='John Bergen' where username = 'sjamani'"
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='John Bergen' where username = 'rladson'"
# GONE: cstannard 104
# GONE: dcohen 105
# GONE: dhall 92
# GONE: dlong 98 - no longer sales
$MYSQLPATH/mysql -u root genericdata -e "update users set username='_cstannard' where username = 'cstannard'"
$MYSQLPATH/mysql -u root genericdata -e "update users set username='_dhall', manager='' where username = 'dhall'"
$MYSQLPATH/mysql -u root genericdata -e "update users set username='_dlong', manager='' where username = 'dlong'"


# GROUP
#mholguin mmarciniak, pdecarlo
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'mholguin'"
#scybulski mmarciniak, jbergen
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'scybulski'"
#mdauksza yyamashita
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'mdauksza'"
#mortiz mmarciniak, pdecarlo
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'mortiz'"
#dlangmaack mmarciniak, jbergen
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'dlangmaack'"
#plindy mmarciniak, pdecarlo
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'plindy'"
#*NEW* avolpe yyamashita
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='' where username = 'avolpe'"
# GONE: flong 115
# GONE: cnakashima 117
# gwisniewski 129  -> save as admin
# GONE: awitt 132
$MYSQLPATH/mysql -u root genericdata -e "update users set username='_flong' where username = 'flong'"
$MYSQLPATH/mysql -u root genericdata -e "update users set username='_cnakashima' where username = 'cnakashima'"
#$MYSQLPATH/mysql -u root genericdata -e "update users set username='_gwisniewski' where username = 'gwisniewski'"
$MYSQLPATH/mysql -u root genericdata -e "update users set username='_awitt' where username = 'awitt'"

# additional columns
$MYSQLPATH/mysql -u root genericdata -e "alter table wfapp2s add column case_state varchar(255), add case_state_text varchar(255)"
$MYSQLPATH/mysql -u root genericdata -e "alter table workflow_event_logs add column case_state varchar(255), add case_state_text varchar(255)"
bash postDeploy_panasonic.sh

echo 'postDeploy_panasonic completed'

# below needs to be run only at first deployment
if [ 1 != 1 ]; then
bash dumpUser.sh
bash createLDAPTree.sh $LDAPHOST
bash resetPassword.sh $LDAPHOST uid=jvasque,ou=Headquarter,dc=enspirea,dc=com root
bash resetPassword.sh $LDAPHOST uid=avolpe,ou=Headquarter,dc=enspirea,dc=com root
bash resetPassword.sh $LDAPHOST uid=tszeto,ou=Headquarter,dc=enspirea,dc=com root
bash resetPassword.sh $LDAPHOST uid=rladson,ou=Headquarter,dc=enspirea,dc=com root
#bash resetPassword.sh $LDAPHOST "uid=gwisniewski ,ou=Headquarter,dc=enspirea,dc=com" root
#bash resetPassword.sh $LDAPHOST "uid=scybulski,ou=Headquarter,dc=enspirea,dc=com" root

# reload acl configuration
bash reload_acl.sh panasonic
bash redis_load_global_config.sh panasonic

redis-cli -h $REDISHOST rpush App2_mainview_list 'case_state'
redis-cli -h $REDISHOST rpush App2_mainview_list 'case_state_text'

# create link for newly created page from App2
cd ../../Plugin/App2/View/App2
ln -s ../../../../View/Workflow/export_with_filter.ctp .
ln -s ../../../../View/Workflow/save_state.ctp .
ln -s ../../../../View/Workflow/list_approved_group.ctp .


# additional config for App2
KEY_WORKFLOW=App2_workflow
redis-cli -h $REDISHOST hset $KEY_WORKFLOW assignee_at_approve tnakamura

KEY_REPORT_COLUMNS=App2_workflow_report_columns
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS id
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS Date
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS Requested_by
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS Ship_to
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS updated_at
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS Sheet_No
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS 2_Total_Net_Profit_1
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS 2_Total_Revenue_Total_1
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS 2_Total_Rep_Commission_ratio_1
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS 2_Total_Rep_Commission_cost_1
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS Wfapp2.state
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS Wfapp2.case_state
redis-cli -h $REDISHOST rpush $KEY_REPORT_COLUMNS Wfapp2.updated_at

KEY_REPORT_PARAMS=App2_workflow_report_params
redis-cli -h $REDISHOST hset $KEY_REPORT_PARAMS form_id_from ts_from
redis-cli -h $REDISHOST hset $KEY_REPORT_PARAMS form_id_to ts_to
redis-cli -h $REDISHOST hset $KEY_REPORT_PARAMS filter_column Date


KEY_EMAIL_PARAMS=App2_workflow_email_params
redis-cli -h $REDISHOST hset $KEY_EMAIL_PARAMS subject_tag_column Rep_name_company

# add export_with_filter
redis-cli -h $REDISHOST hset App2_op_label export_with_filter "Export With Filter"
redis-cli -h $REDISHOST hset App2_acl_action_acos export_with_filter Attrapp2
redis-cli -h $REDISHOST hset App2_acl_action_ops export_with_filter delete

# add commission ratio calculation
redis-cli -h $REDISHOST sadd App2_upload_ignored 2_Total_Rep_Commission_ratio_1 
redis-cli -h $REDISHOST sadd App2_upload_ignored id

# admin UI baseurl -> need to change to original
redis-cli -h $REDISHOST hset App_base_url external https://panasonic.briode.com

# link to js
cd $SCRIPTROOT/../../Plugin/App2/webroot/js
ln -s ../../../../Vendor/projects/webroot/js/App2/proj_27.js .
ln -s ../../../../Vendor/projects/webroot/js/App2/proj_28.js .
ln -s ../../../../Vendor/projects/webroot/js/App2/proj_29.js .

# TODO MANUAL
echo 'add hasOne/belongsTo in corresponding models (Attrapp2, Wfappp2)'
echo 'update WorkflowChangeLogger.php to add case_state, case_state_text'
fi

# test env only
if [ 1 != 1 ]; then
# modify production data into dev safe - reset email address
$MYSQLPATH/mysql -h $DBHOST -u root genericdata -e "update users set mail='enspirea.dev@gmail.com'"

bash resetPassword.sh $LDAPHOST "uid=gwisniewski ,ou=Headquarter,dc=enspirea,dc=com" root
bash resetPassword.sh $LDAPHOST "uid=scybulski,ou=Headquarter,dc=enspirea,dc=com" root
bash resetPassword.sh $LDAPHOST "uid=dcohen,ou=Headquarter,dc=enspirea,dc=com" r00t
bash resetPassword.sh $LDAPHOST "uid=jbergen,ou=Headquarter,dc=enspirea,dc=com" root
bash resetPassword.sh $LDAPHOST "uid=tnakamura,ou=Headquarter,dc=enspirea,dc=com" root
bash resetPassword.sh $LDAPHOST "uid=slynum,ou=Headquarter,dc=enspirea,dc=com" root
bash resetPassword.sh $LDAPHOST "uid=dcassanelli,ou=Headquarter,dc=enspirea,dc=com" root

redis-cli -h $REDISHOST hset App_base_url external https://adminuitest.briode.com:8443
fi
