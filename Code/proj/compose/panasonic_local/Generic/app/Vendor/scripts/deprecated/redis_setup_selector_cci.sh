# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App36 'Form' 'Form'
bash ./redis_set_labels.sh App37 'Form Approval' 'Form Approval'

bash ./redis_set_initial_plugin.sh App36

