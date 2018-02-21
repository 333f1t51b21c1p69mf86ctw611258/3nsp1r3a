# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App26 'Timesheet Summary' 'Timesheet Summary'
bash ./redis_set_labels.sh App24 'Timesheet' 'Timesheet'
bash ./redis_set_labels.sh App25 'Target Setting' 'Target Setting'
bash ./redis_set_labels.sh App27 'Holiday Setting' 'Holiday Setting'
bash ./redis_set_labels.sh App28 'Handler Setting' 'Handler Setting'

bash ./redis_set_initial_plugin.sh App26

