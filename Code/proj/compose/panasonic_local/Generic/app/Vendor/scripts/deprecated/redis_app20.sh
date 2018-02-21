#! /bin/bash

PLUGIN_NAME='App20'
APP_KIND='db'
STRING_SIZE=256
MAIN_PAGE='main_menu'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

MAINVIEW_COLUMNS='id Customer_name Detail Fee Quotation_date Invoice_date PO Payment_date Status'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS" "Payment_date Payment_date Invoice_date Quotation_date Fee" "IS_NULL_DESC desc desc desc desc"
./redis_config_searchview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_notification.sh $PLUGIN_NAME 'disable'

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'readable_by_all'

# Report
./redis_config_report.sh $PLUGIN_NAME 'disable'

