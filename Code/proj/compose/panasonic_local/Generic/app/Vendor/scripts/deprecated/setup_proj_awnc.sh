#! /bin/bash
. ./env.sh

bash ./redis_app3.sh
bash ./redis_app4.sh
bash ./redis_app5.sh
bash ./redis_app6.sh
bash ./redis_app7.sh
bash ./redis_app8.sh
bash ./redis_app10.sh
bash ./redis_app11.sh
bash ./redis_app12.sh
bash ./redis_app13.sh

bash ./gen_app.sh "3 4 5 6 7 8 10 11 12 13"

bash ./redis_app_base_uri.sh https://awnc.briode.com
