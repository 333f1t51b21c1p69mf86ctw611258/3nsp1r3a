#! /bin/bash
. ./env.sh

#bash ./redis_app33.sh
bash ./redis_app34.sh
#bash ./redis_app35.sh

bash ./redis_setup_selector_matsutani.sh

bash ./gen_app.sh "34" # 33, 35 removed

bash ./redis_app_base_uri.sh https://matsutani.briode.com
