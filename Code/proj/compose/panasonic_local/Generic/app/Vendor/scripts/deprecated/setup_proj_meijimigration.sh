#! /bin/bash
. ./env.sh

bash ./redis_app29.sh
bash ./redis_app30.sh
bash ./redis_app31.sh

bash ./gen_app.sh "29 30 31"

bash ./redis_setup_selector_meijimigration.sh

for i in {37..86}
do
    bash ./redis_config_user.sh $i App29
done

