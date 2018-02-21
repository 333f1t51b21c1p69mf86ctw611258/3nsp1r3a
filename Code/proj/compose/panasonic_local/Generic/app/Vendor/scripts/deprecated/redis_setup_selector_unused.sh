# make sure to clean releveant Redis entry before setting the listing
bash ./redis_clear_selectors.sh

# bash ./redis_set_labels.sh <PLUGIN_NAME> <APP_LIST_LABELS> <APP_NAVNAME>
bash ./redis_set_labels.sh App33 'Expense Free Categories' 'Expense Free Categories'
#bash ./redis_set_labels.sh App34 'Expense Fixed Categories' 'Expense Fixed Categories'
bash ./redis_set_labels.sh App35 'Categories' 'Categories'

bash ./redis_set_initial_plugin.sh App33

