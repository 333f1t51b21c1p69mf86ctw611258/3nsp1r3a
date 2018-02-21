#! /bin/sh

grep "logged in from" ~/Generic/app/tmp/logs/debug.log | grep '^2015-' > ext_session.out
awk -f ext_tomcat.awk ~/tomcat7/logs/localhost_access_log.2015* > ext_tomcat.out
cat ext_session.out ext_tomcat.out |sort > ext.out

