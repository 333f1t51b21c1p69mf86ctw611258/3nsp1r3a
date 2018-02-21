#! /bin/bash
. ./env.sh

bash ./redis_app36.sh
bash ./redis_app37.sh

bash ./redis_setup_selector_cci.sh

bash ./gen_app.sh "36 37"

bash ./redis_app_base_uri.sh https://cci.briode.com
