# ./redis_set_ui_options_list.sh  <LIST_NAME> <LIST_VALUE> <LIST_VALUE>...
./redis_set_ui_options_list.sh Response_list QPR QIR FQR FTR Other
./redis_set_ui_options_list.sh Term_list 'Short Term C/M' 'Long Term C/M' 'Long Term C/M Plan'
./redis_set_ui_options_list.sh Yes_no Yes No
./redis_set_ui_options_list.sh Yes_na Yes N/A

./redis_set_ui_options_list.sh check1 c_opt1 c_opt2 c_opt3
./redis_set_ui_options_list.sh radio1 r_opt1 r_opt2 r_opt3

./redis_set_ui_options_list.sh Suspected Suspected
./redis_set_ui_options_list.sh A_B_C_D A B C D
./redis_set_ui_options_list.sh S_I_C Supplier Internal Customer
./redis_set_ui_options_list.sh Judgment_select 'Request Approved "As Is"' 'Requet Approved "Under the Following Conditions"' 'Request Rejected "For the Following Reason(s)"'
./redis_set_ui_options_list.sh To_Pass_Parts_select 'Required: Account for Serial #' 'Not required: Account for Serial #'
./redis_set_ui_options_list.sh NON_CONFORMITY_RANK_select 'Safety Critical Dimension and Process' 'Vital Characteristic Dimension and Process' 'Critical defect can not be detected and affects function' 'Critical Defect can be detected and affects function' 'Defect is non-functional'
./redis_set_ui_options_list.sh Ringi_category 'Contract' 'Investment' 'external organization' 'events' 'disposal' 'Price' 'Hiring and others' 'Tangible fixed assets' 'Intangible fixed assets'
./redis_set_ui_options_list.sh OK_NG OK NG
./redis_set_ui_options_list.sh Safety 'Safety Critical Dimension and Process'
./redis_set_ui_options_list.sh Vital 'Vital Characteristic Dimension and Process'
./redis_set_ui_options_list.sh Critical1 'Critical defect can not be detected and affects'
./redis_set_ui_options_list.sh Critical2 'Critical Defect can be detected and affects function'
./redis_set_ui_options_list.sh Defect 'Defect is non-functional'

# Enspirea Expense
./redis_set_ui_options_list.sh EnsExpenseCat '' 'Automobile Expense' 'Computer and Internet Expenses' 'Meals and Entertainment' 'Office Supplies' 'Postage and Delivery' 'Rent Expense' 'Travel Expense'

# App19 - Okaya
./redis_set_ui_options_list.sh Branch_list 'AL' 'ATL' 'CHG' 'HO' 'HTN' 'LA' 'LEX' 'RD' 'SD' 'TN'
./redis_set_ui_options_list.sh AMEX_Personal_list 'AMEX' 'Personal'
./redis_set_ui_options_list.sh Acount_AB '' 84831 84832 85190 85200 85240 85510 85410 85110 85111 85150 85210 85220 85270 85500 85250 85490 85420 85520 85360 84800
./redis_set_ui_options_list.sh Acount_C '' 85140 85142 85141
./redis_set_ui_options_list.sh Acount_DE '' 14512 14513 14514 82284

# App20 - ITA
./redis_set_ui_options_list.sh Customer_name 'Yusen Logistics' NADC Meiji Noritake Mazak 'Ohio Metal' Senko
./redis_set_ui_options_list.sh Contact_name 'Osamu Ohta' 'Shin Kishioka' 'Toru Hironaka' 'Enokida'
./redis_set_ui_options_list.sh YES_NO YES NO
./redis_set_ui_options_list.sh Status Quoted PO Invoiced Paid Cancelled Pending

# App22 - TokyoMaker maintenance
./redis_set_ui_options_list.sh 3D_printer 'Scoovo' 'Replicator' '3D Touch' 'Davinci'

# App33,34 - Matsutani options
./redis_set_ui_options_list.sh Matsutani_Expense_Category '' 'Airfare/Rail' 'Car Rental' 'Rental Car Gas' 'Taxi/Bus/Limo' 'Parking/Tolls' 'Breakfast**' 'Lunch**' 'Dinner**' 'Entertainment**' 'Lodging/Room' 'Office Phone/Fax' 'Internet' 'Cell Phone' 'Postage/Shipping' 'Office Supplies*' 'Product Samples*' 'Tradeshow/Conference' 'Miscellaneous*'

