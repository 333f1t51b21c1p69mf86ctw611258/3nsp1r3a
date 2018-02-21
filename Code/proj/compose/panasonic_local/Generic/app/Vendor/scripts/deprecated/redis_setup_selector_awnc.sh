# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App3 'Customer Countermeasure' 'Customer Countermeasure'
bash ./redis_set_labels.sh App7 'Internal Countermeasure' 'Internal Countermeasure'
bash ./redis_set_labels.sh App4 'Daily Audit' 'Daily Audit'
bash ./redis_set_labels.sh App11 'Daily Audit(2)' 'Daily Audit(2)'
bash ./redis_set_labels.sh App8 'Deviation' 'Deviation'
bash ./redis_set_labels.sh App5 'Ringi' 'Ringi'
bash ./redis_set_labels.sh App6 'Inventory' 'Inventory'
bash ./redis_set_labels.sh App10 'Inventory Check' 'Inventory Check'
bash ./redis_set_labels.sh App12 'Test(App12)' '(None)'
bash ./redis_set_labels.sh App13 'Test(App13)' '(None)'

bash ./redis_set_initial_plugin.sh App3

