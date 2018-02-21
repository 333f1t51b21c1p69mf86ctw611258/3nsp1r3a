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
    function run1_Total_unit_2(){
        var tmpval= parseFloat($('#1_Landed_Cost_2').val()||"0")+
                    parseFloat($('#1_D2D_Freight_2').val()||"0")+
                    parseFloat($('#1_Inside_Delivery_2').val()||"0")+
                    parseFloat($('#1_Accessory1_2').val()||"0")+
                    parseFloat($('#1_Accessory2_2').val()||"0");
        $('#1_Total_unit_2').val(tmpval||"0");
        NDigitCheck($('#1_Total_unit_2'));
    }
    function run1_Cost_Total_2(){
        var tmpval =    parseFloat($('#1_Qty_2').val()||"0")*
                        parseFloat($('#1_Total_unit_2').val()||"0");
        $('#1_Cost_Total_2').val(tmpval||"0");
        NDigitCheck($('#1_Cost_Total_2'));
    }
    function run1_Discount_Rate_2(){
        var tmpval = 100*( 1 -( parseFloat($('#1_Win_Win_2').val()||"0") /  
                            parseFloat($('#1_List_Price_2').val()||"0") ) );
        $('#1_Discount_Rate_2').val(tmpval||"0");
        NDigitCheck($('#1_Discount_Rate_2'));
    }
    function run1_Revenue_Total_2(){
        var tmpval =    parseFloat($('#1_Qty_2').val()||"0")*
                        parseFloat($('#1_Win_Win_2').val()||"0");
        $('#1_Revenue_Total_2').val(tmpval||"0");
        NDigitCheck($('#1_Revenue_Total_2'));
    }
    function run1_Gross_Profit_2(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_2').val()||"0")-
                        parseFloat($('#1_Cost_Total_2').val()||"0") ) / 
                      parseFloat($('#1_Revenue_Total_2').val()||"0") ;
        $('#1_Gross_Profit_2').val(tmpval||"0");
        NDigitCheck($('#1_Gross_Profit_2'));
    }
    function run1_Rep_Commission_cost_2(){
        var tmpval =    parseFloat($('#1_Revenue_Total_2').val()||"0")*
                        0.01*parseFloat($('#1_Rep_Commission_ratio_2').val()||"0");
        $('#1_Rep_Commission_cost_2').val(tmpval||"0");
        NDigitCheck($('#1_Rep_Commission_cost_2'));
    }
    function run1_Net_Profit_2(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_2').val()||"0")-
                        parseFloat($('#1_Cost_Total_2').val()||"0")-  
                        parseFloat($('#1_Rep_Commission_cost_2').val()||"0") )/
                      parseFloat($('#1_Revenue_Total_2').val()||"0");
        $('#1_Net_Profit_2').val(tmpval||"0");
        NDigitCheck($('#1_Net_Profit_2'));
    }
    function run1_all(){
        run1_Total_unit_2();
        run1_Cost_Total_2();
        run1_Discount_Rate_2();
        run1_Revenue_Total_2();
        run1_Gross_Profit_2();
        run1_Rep_Commission_cost_2();
        run1_Net_Profit_2();
    }

    $('#1_Landed_Cost_2').change(function(){ run_all(); }).change();
    $('#1_D2D_Freight_2').change(function(){ run_all(); }).change();
    $('#1_Inside_Delivery_2').change(function(){ run_all(); }).change();
    $('#1_Accessory1_2').change(function(){ run_all(); }).change();
    $('#1_Accessory2_2').change(function(){ run_all(); }).change();
    $('#1_List_Price_2').change(function(){ run_all(); }).change();
    $('#1_Win_Win_2').change(function(){ run_all(); }).change();
    $('#1_Qty_2').change(function(){ run_all(); }).change();
    $('#1_Win_Win_2').change(function(){ run_all(); }).change();
    $('#1_Rep_Commission_ratio_2').change(function(){ run_all(); }).change();
});
