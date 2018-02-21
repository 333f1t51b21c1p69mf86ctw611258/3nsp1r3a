# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App22 'Maintenance' 'Maintenance'
bash ./redis_set_labels.sh App23 'Know-how' 'Know-how'

bash ./redis_set_initial_plugin.sh App22

