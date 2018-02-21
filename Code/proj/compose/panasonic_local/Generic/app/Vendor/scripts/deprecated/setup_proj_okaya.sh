#! /bin/bash
. ./env.sh

bash ./redis_app19.sh

bash ./redis_setup_selector_okaya.sh

bash ./gen_app.sh "19"

bash ./redis_app_base_uri.sh https://okaya.briode.com
