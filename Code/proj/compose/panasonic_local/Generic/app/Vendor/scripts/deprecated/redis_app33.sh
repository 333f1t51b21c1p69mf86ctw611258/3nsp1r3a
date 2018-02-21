#! /bin/bash

PLUGIN_NAME='App33'
APP_KIND='workflow'
STRING_SIZE=256
MAIN_PAGE='list_waiting_for_your_action'
./redis_create_app.sh $PLUGIN_NAME $APP_KIND $STRING_SIZE $MAIN_PAGE
./redis_config_workflow.sh $PLUGIN_NAME 'enable' 'enable' "external_baseurl|https://matutani.briode.com/Generic"

# Visibility
./redis_config_db_visibility.sh $PLUGIN_NAME 'acl'

# Report
./redis_config_report.sh $PLUGIN_NAME 'disable'

MAINVIEW_COLUMNS='subject_id created_at creator_id summary assignee'
SEARCHVIEW_COLUMNS='id created_at creator_id Employee_Name Employee_Number Reason_For_Expenditures'
./redis_config_mainview.sh $PLUGIN_NAME "$MAINVIEW_COLUMNS"
./redis_config_searchview.sh $PLUGIN_NAME "$SEARCHVIEW_COLUMNS"

CALCULATED_FIELDS='Report_Period_Start Report_Period_End 1_Expenses_date_2 1_Expenses_date_3 1_Expenses_date_4 1_Expenses_date_5 1_Expenses_date_6 1_Expenses_date_7 1_Item1_1 1_Item1_2 1_Item1_3 1_Item1_4 1_Item1_5 1_Item1_6 1_Item1_7 2_Item1_total 2_Item2_total 2_Item3_total 2_Item4_total 2_Item5_total 2_Item6_total 2_Item7_total 2_Item8_total 2_Item9_total 2_Item10_total 2_Item11_total 2_Item12_total 2_Item13_total 2_Item14_total 2_Item15_total 2_Item16_total 2_Item17_total 2_Item18_total 2_Item19_total 3_Sun_total 3_Mon_total 3_Tue_total 3_Wed_total 3_Thu_total 3_Fri_total 3_Sat_total 4_Total 1_Item2_Code_1 1_Item3_Code_1 1_Item4_Code_1 1_Item5_Code_1 1_Item6_Code_1 1_Item7_Code_1 1_Item8_Code_1 1_Item9_Code_1 1_Item10_Code_1 1_Item11_Code_1 1_Item12_Code_1 1_Item13_Code_1 1_Item14_Code_1 1_Item15_Code_1 1_Item16_Code_1 1_Item17_Code_1 1_Item18_Code_1 1_Item19_Code_1 1_Mileage_date_1 1_Mileage_date_2 1_Mileage_date_3 1_Mileage_date_4 1_Mileage_date_5 1_Mileage_date_6 1_Mileage_date_7 7_Mileage_Amount_1 7_Mileage_Amount_2 7_Mileage_Amount_3 7_Mileage_Amount_4 7_Mileage_Amount_5 7_Mileage_Amount_6 7_Mileage_Amount_7 Total_Expenses Due_to_Company Due_to_Employee'
./redis_config_js.sh $PLUGIN_NAME "$CALCULATED_FIELDS" true



