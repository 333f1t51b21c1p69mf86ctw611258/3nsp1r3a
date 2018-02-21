#! /bin/bash
. ./env.sh

bash ./redis_app24.sh
bash ./redis_app25.sh
bash ./redis_app26.sh
bash ./redis_app27.sh
bash ./redis_app28.sh

bash ./gen_app.sh "24 25 26 27 28"

bash ./redis_setup_selector_meijicorp.sh

bash ./redis_app_base_uri.sh http://remote.enspirea.com:8990 http://192.168.1.22

for i in {37..86}
do
    bash ./redis_config_user.sh $i App26
done

# admin needs to be presented with admin menu
HOST_IP_ADDR=172.16.4.145
#bash ./redis_app_login_redirect_by_usertype.sh 1 "http://$HOST_IP_ADDR:8080/birtmgr/frameset?__report=P1.rptdesign"
bash ./redis_app_login_redirect_by_usertype.sh 2 "http://$HOST_IP_ADDR/birtmgr/frameset?__report=P1-MGR.rptdesign"
bash ./redis_app_login_redirect_by_usertype.sh 3 "http://$HOST_IP_ADDR/birtmgr/frameset?__report=P1-MGR.rptdesign"
bash ./redis_app_login_redirect_by_usertype.sh 4 "http://$HOST_IP_ADDR/birt/frameset?__report=P1.rptdesign"
#bash ./redis_app_login_redirect_by_usertype.sh 4 "http://remote.enspirea.com:8990/birt/frameset?__report=P1.rptdesign"

# TODO
# This was copied to postDeploy as data base is not ready at build time
#$MYSQLPATH/mysql -u root genericdata -e 'create view target as select * from attrapp25s;'
