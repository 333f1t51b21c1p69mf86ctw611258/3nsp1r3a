#! /bin/bash

MAINVIEW_COLUMNS5='id Ringi_Number category Request_Project Total_Asset Total_Expense T1_T2'
MAINVIEW_COLUMNS6='id Date Parts_Number1 Parts_Number2 Parts_Number3 Parts_Number4 Parts_Number5'
MAINVIEW_COLUMNS7='id project_number part_name part_number response_to First_shipment First_good'
MAINVIEW_COLUMNS8='id Deviation WD_DCN Part_Number Part_Name Supply_Co Proj_Line'


for (( i=6; i<=6; i++ ))
do
    PLUGIN_NAME="App$i"
    APP_KIND='db'
    STRING_SIZE=256
    MAIN_PAGE='main_menu'
    ./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE

    MAINVIEW_COL_NAMES="MAINVIEW_COLUMNS$i"
    ./redis_config_mainview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
    ./redis_config_searchview.sh $PLUGIN_NAME "${!MAINVIEW_COL_NAMES}"
    ./redis_config_notification.sh $PLUGIN_NAME 'disable'

    # Visibility
    ./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

    # Report
    ./redis_config_report.sh $PLUGIN_NAME 'enable'
done

