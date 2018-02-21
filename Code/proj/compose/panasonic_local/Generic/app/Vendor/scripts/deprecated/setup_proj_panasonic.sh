#! /bin/bash
. ./env.sh

bash ./redis_app2.sh
bash ./redis_app32.sh

bash ./redis_setup_selector_panasonic.sh

bash ./gen_app.sh "2 32"

bash ./redis_app_base_uri.sh https://panasonic.briode.com
