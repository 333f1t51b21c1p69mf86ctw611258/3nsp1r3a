# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App16 'Ringi' 'Ringi'
bash ./redis_set_labels.sh App15 'Function' 'Function'
bash ./redis_set_labels.sh App14 'Expense' 'Expense'
bash ./redis_set_labels.sh App17 'Check Sheet' 'Check Sheet'
bash ./redis_set_labels.sh App18 'Customer Information' 'Customer Information'
bash ./redis_set_labels.sh App21 'Validation' 'Validation'

bash ./redis_set_initial_plugin.sh App16

