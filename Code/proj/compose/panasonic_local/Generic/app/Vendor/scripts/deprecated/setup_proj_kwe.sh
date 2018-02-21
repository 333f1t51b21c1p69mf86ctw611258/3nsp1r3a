#! /bin/bash
. ./env.sh

bash ./redis_app38.sh

bash ./redis_setup_selector_kwe.sh

bash ./gen_app.sh "38"

bash ./redis_app_base_uri.sh https://kwe.briode.com
