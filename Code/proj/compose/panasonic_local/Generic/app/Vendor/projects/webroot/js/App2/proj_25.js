$(document).ready(function(){
    function run_all(){
        ///var checker = document.getElementById("load_checker").value;
        var checker = false;
        if(!checker){
            run1_all();
            run2_all();
        }
    }

    // row specific functions
    function run1_Total_unit_25(){
        var tmpval= parseFloat($('#1_Landed_Cost_25').val()||"0")+
                    parseFloat($('#1_D2D_Freight_25').val()||"0")+
                    parseFloat($('#1_Inside_Delivery_25').val()||"0")+
                    parseFloat($('#1_Accessory1_25').val()||"0")+
                    parseFloat($('#1_Accessory2_25').val()||"0");
        $('#1_Total_unit_25').val(tmpval||"0");
        NDigitCheck($('#1_Total_unit_25'));
    }
    function run1_Cost_Total_25(){
        var tmpval =    parseFloat($('#1_Qty_25').val()||"0")*
                        parseFloat($('#1_Total_unit_25').val()||"0");
        $('#1_Cost_Total_25').val(tmpval||"0");
        NDigitCheck($('#1_Cost_Total_25'));
    }
    function run1_Discount_Rate_25(){
        var tmpval = 100*( 1 -( parseFloat($('#1_Win_Win_25').val()||"0") /  
                            parseFloat($('#1_List_Price_25').val()||"0") ) );
        $('#1_Discount_Rate_25').val(tmpval||"0");
        NDigitCheck($('#1_Discount_Rate_25'));
    }
    function run1_Revenue_Total_25(){
        var tmpval =    parseFloat($('#1_Qty_25').val()||"0")*
                        parseFloat($('#1_Win_Win_25').val()||"0");
        $('#1_Revenue_Total_25').val(tmpval||"0");
        NDigitCheck($('#1_Revenue_Total_25'));
    }
    function run1_Gross_Profit_25(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_25').val()||"0")-
                        parseFloat($('#1_Cost_Total_25').val()||"0") ) / 
                      parseFloat($('#1_Revenue_Total_25').val()||"0") ;
        $('#1_Gross_Profit_25').val(tmpval||"0");
        NDigitCheck($('#1_Gross_Profit_25'));
    }
    function run1_Rep_Commission_cost_25(){
        var tmpval =    parseFloat($('#1_Revenue_Total_25').val()||"0")*
                        0.01*parseFloat($('#1_Rep_Commission_ratio_25').val()||"0");
        $('#1_Rep_Commission_cost_25').val(tmpval||"0");
        NDigitCheck($('#1_Rep_Commission_cost_25'));
    }
    function run1_Net_Profit_25(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_25').val()||"0")-
                        parseFloat($('#1_Cost_Total_25').val()||"0")-  
                        parseFloat($('#1_Rep_Commission_cost_25').val()||"0") )/
                      parseFloat($('#1_Revenue_Total_25').val()||"0");
        $('#1_Net_Profit_25').val(tmpval||"0");
        NDigitCheck($('#1_Net_Profit_25'));
    }
    function run1_all(){
        run1_Total_unit_25();
        run1_Cost_Total_25();
        run1_Discount_Rate_25();
        run1_Revenue_Total_25();
        run1_Gross_Profit_25();
        run1_Rep_Commission_cost_25();
        run1_Net_Profit_25();
    }

    $('#1_Landed_Cost_25').change(function(){ run_all(); }).change();
    $('#1_D2D_Freight_25').change(function(){ run_all(); }).change();
    $('#1_Inside_Delivery_25').change(function(){ run_all(); }).change();
    $('#1_Accessory1_25').change(function(){ run_all(); }).change();
    $('#1_Accessory2_25').change(function(){ run_all(); }).change();
    $('#1_List_Price_25').change(function(){ run_all(); }).change();
    $('#1_Win_Win_25').change(function(){ run_all(); }).change();
    $('#1_Qty_25').change(function(){ run_all(); }).change();
    $('#1_Win_Win_25').change(function(){ run_all(); }).change();
    $('#1_Rep_Commission_ratio_25').change(function(){ run_all(); }).change();
});
