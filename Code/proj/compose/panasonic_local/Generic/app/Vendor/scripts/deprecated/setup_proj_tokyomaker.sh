#! /bin/bash
. ./env.sh

bash ./redis_app22.sh
bash ./redis_app23.sh

bash ./redis_setup_selector_tokyomaker.sh

bash ./gen_app.sh "22 23"

bash ./redis_app_base_uri.sh https://tokyomaker.briode.com
