#! /bin/bash
. ./env.sh

# 'loading app config for redis....'
bash ./redis_app14.sh
bash ./redis_app15.sh
bash ./redis_app16.sh
bash ./redis_app17.sh
bash ./redis_app18.sh
bash ./redis_app21.sh

# 'loading selector for redis....'
bash ./redis_setup_selector_demo.sh

bash ./gen_app.sh "14 15 16 17 18 21"
#bash ./gen_app.sh "15 16 17 18 21"

bash ./redis_app_base_uri.sh https://www.briode.com
