# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App29 'Test1' 'Test1'
bash ./redis_set_labels.sh App30 'Test2' 'Test2'
bash ./redis_set_labels.sh App31 'Test3' 'Test3'

bash ./redis_set_initial_plugin.sh App29

