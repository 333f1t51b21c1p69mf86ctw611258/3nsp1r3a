# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App1 'Monthly Report' 'Monthly Report'
bash ./redis_set_labels.sh App9 'Installation Experience' 'Installation Experience'
bash ./redis_set_labels.sh App39 'Marketing Report' 'Marketing Report'

bash ./redis_set_initial_plugin.sh App1

