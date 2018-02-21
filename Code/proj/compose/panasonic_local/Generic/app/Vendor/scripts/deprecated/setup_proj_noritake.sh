#! /bin/bash
. ./env.sh

bash ./redis_app1.sh
bash ./redis_app9.sh
bash ./redis_app39.sh

bash ./redis_setup_selector_noritake.sh

bash ./gen_app.sh "1 9 39"

bash ./redis_app_base_uri.sh https://noritake.briode.com
