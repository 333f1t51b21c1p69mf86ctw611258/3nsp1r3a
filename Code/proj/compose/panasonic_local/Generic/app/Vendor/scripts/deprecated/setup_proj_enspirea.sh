#! /bin/bash
. ./env.sh

bash ./redis_app14.sh
bash ./redis_app20.sh

bash ./redis_setup_selector_enspirea.sh

bash ./gen_app.sh "14 20"

# reset password for Mr. Kishioka
bash resetPassword.sh $LDAPHOST "uid=shink,ou=IL,dc=enspirea,dc=com" shink
# Login app for Kishioka,Taira,Watanabe->App20
bash ./redis_config_user.sh 33 App20  # Kishioka
bash ./redis_config_user.sh 24 App14  # Enspirea Employees
bash ./redis_config_user.sh 25 App14
bash ./redis_config_user.sh 26 App14
bash ./redis_config_user.sh 27 App14
bash ./redis_config_user.sh 28 App14
bash ./redis_config_user.sh 29 App14
bash ./redis_config_user.sh 30 App14
bash ./redis_config_user.sh 31 App14
bash ./redis_config_user.sh 32 App14

bash ./redis_app_base_uri.sh http://remote.enspirea.com:8089 http://192.168.1.7:8080
