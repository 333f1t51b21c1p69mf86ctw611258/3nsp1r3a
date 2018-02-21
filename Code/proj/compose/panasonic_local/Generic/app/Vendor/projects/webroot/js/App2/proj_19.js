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
    function run1_Total_unit_19(){
        var tmpval= parseFloat($('#1_Landed_Cost_19').val()||"0")+
                    parseFloat($('#1_D2D_Freight_19').val()||"0")+
                    parseFloat($('#1_Inside_Delivery_19').val()||"0")+
                    parseFloat($('#1_Accessory1_19').val()||"0")+
                    parseFloat($('#1_Accessory2_19').val()||"0");
        $('#1_Total_unit_19').val(tmpval||"0");
        NDigitCheck($('#1_Total_unit_19'));
    }
    function run1_Cost_Total_19(){
        var tmpval =    parseFloat($('#1_Qty_19').val()||"0")*
                        parseFloat($('#1_Total_unit_19').val()||"0");
        $('#1_Cost_Total_19').val(tmpval||"0");
        NDigitCheck($('#1_Cost_Total_19'));
    }
    function run1_Discount_Rate_19(){
        var tmpval = 100*( 1 -( parseFloat($('#1_Win_Win_19').val()||"0") /  
                            parseFloat($('#1_List_Price_19').val()||"0") ) );
        $('#1_Discount_Rate_19').val(tmpval||"0");
        NDigitCheck($('#1_Discount_Rate_19'));
    }
    function run1_Revenue_Total_19(){
        var tmpval =    parseFloat($('#1_Qty_19').val()||"0")*
                        parseFloat($('#1_Win_Win_19').val()||"0");
        $('#1_Revenue_Total_19').val(tmpval||"0");
        NDigitCheck($('#1_Revenue_Total_19'));
    }
    function run1_Gross_Profit_19(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_19').val()||"0")-
                        parseFloat($('#1_Cost_Total_19').val()||"0") ) / 
                      parseFloat($('#1_Revenue_Total_19').val()||"0") ;
        $('#1_Gross_Profit_19').val(tmpval||"0");
        NDigitCheck($('#1_Gross_Profit_19'));
    }
    function run1_Rep_Commission_cost_19(){
        var tmpval =    parseFloat($('#1_Revenue_Total_19').val()||"0")*
                        0.01*parseFloat($('#1_Rep_Commission_ratio_19').val()||"0");
        $('#1_Rep_Commission_cost_19').val(tmpval||"0");
        NDigitCheck($('#1_Rep_Commission_cost_19'));
    }
    function run1_Net_Profit_19(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_19').val()||"0")-
                        parseFloat($('#1_Cost_Total_19').val()||"0")-  
                        parseFloat($('#1_Rep_Commission_cost_19').val()||"0") )/
                      parseFloat($('#1_Revenue_Total_19').val()||"0");
        $('#1_Net_Profit_19').val(tmpval||"0");
        NDigitCheck($('#1_Net_Profit_19'));
    }
    function run1_all(){
        run1_Total_unit_19();
        run1_Cost_Total_19();
        run1_Discount_Rate_19();
        run1_Revenue_Total_19();
        run1_Gross_Profit_19();
        run1_Rep_Commission_cost_19();
        run1_Net_Profit_19();
    }

    $('#1_Landed_Cost_19').change(function(){ run_all(); }).change();
    $('#1_D2D_Freight_19').change(function(){ run_all(); }).change();
    $('#1_Inside_Delivery_19').change(function(){ run_all(); }).change();
    $('#1_Accessory1_19').change(function(){ run_all(); }).change();
    $('#1_Accessory2_19').change(function(){ run_all(); }).change();
    $('#1_List_Price_19').change(function(){ run_all(); }).change();
    $('#1_Win_Win_19').change(function(){ run_all(); }).change();
    $('#1_Qty_19').change(function(){ run_all(); }).change();
    $('#1_Win_Win_19').change(function(){ run_all(); }).change();
    $('#1_Rep_Commission_ratio_19').change(function(){ run_all(); }).change();
});
