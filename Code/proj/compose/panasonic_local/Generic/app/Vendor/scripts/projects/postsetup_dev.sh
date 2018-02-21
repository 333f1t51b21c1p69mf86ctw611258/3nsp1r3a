#! /bin/sh
. ./env.sh

# overrides login redirect configuration
redis-cli -h $LDAPHOST hdel App_login_redirect_by_usertype 1
redis-cli -h $LDAPHOST hdel App_login_redirect_by_usertype 2
redis-cli -h $LDAPHOST hdel App_login_redirect_by_usertype 3
redis-cli -h $LDAPHOST hdel App_login_redirect_by_usertype 4
