// Common config for all rows
function run2_Total_Cost_Total_1(){
    var tmpval = parseFloat($('#1_Cost_Total_1').val()||"0")+
                 parseFloat($('#1_Cost_Total_2').val()||"0")+
                 parseFloat($('#1_Cost_Total_3').val()||"0")+
                 parseFloat($('#1_Cost_Total_4').val()||"0")+
                 parseFloat($('#1_Cost_Total_5').val()||"0")+
                 parseFloat($('#1_Cost_Total_6').val()||"0")+
                 parseFloat($('#1_Cost_Total_7').val()||"0")+
                 parseFloat($('#1_Cost_Total_8').val()||"0")+
                 parseFloat($('#1_Cost_Total_9').val()||"0")+
                 parseFloat($('#1_Cost_Total_10').val()||"0")+
                 parseFloat($('#1_Cost_Total_11').val()||"0")+
                 parseFloat($('#1_Cost_Total_12').val()||"0")+
                 parseFloat($('#1_Cost_Total_13').val()||"0")+
                 parseFloat($('#1_Cost_Total_14').val()||"0")+
                 parseFloat($('#1_Cost_Total_15').val()||"0")+
                 parseFloat($('#1_Cost_Total_16').val()||"0")+
                 parseFloat($('#1_Cost_Total_17').val()||"0")+
                 parseFloat($('#1_Cost_Total_18').val()||"0")+
                 parseFloat($('#1_Cost_Total_19').val()||"0")+
                 parseFloat($('#1_Cost_Total_20').val()||"0")+
                 parseFloat($('#1_Cost_Total_21').val()||"0")+
                 parseFloat($('#1_Cost_Total_22').val()||"0")+
                 parseFloat($('#1_Cost_Total_23').val()||"0")+
                 parseFloat($('#1_Cost_Total_24').val()||"0")+
                 parseFloat($('#1_Cost_Total_25').val()||"0");
    $('#2_Total_Cost_Total_1').val(tmpval||"0");
    NDigitCheck($('#2_Total_Cost_Total_1'));
}
function run2_Total_Revenue_Total_1(){
    var tmpval = parseFloat($('#1_Revenue_Total_1').val()||"0")+
                 parseFloat($('#1_Revenue_Total_2').val()||"0")+
                 parseFloat($('#1_Revenue_Total_3').val()||"0")+
                 parseFloat($('#1_Revenue_Total_4').val()||"0")+
                 parseFloat($('#1_Revenue_Total_5').val()||"0")+
                 parseFloat($('#1_Revenue_Total_6').val()||"0")+
                 parseFloat($('#1_Revenue_Total_7').val()||"0")+
                 parseFloat($('#1_Revenue_Total_8').val()||"0")+
                 parseFloat($('#1_Revenue_Total_9').val()||"0")+
                 parseFloat($('#1_Revenue_Total_10').val()||"0")+
                 parseFloat($('#1_Revenue_Total_11').val()||"0")+
                 parseFloat($('#1_Revenue_Total_12').val()||"0")+
                 parseFloat($('#1_Revenue_Total_13').val()||"0")+
                 parseFloat($('#1_Revenue_Total_14').val()||"0")+
                 parseFloat($('#1_Revenue_Total_15').val()||"0")+
                 parseFloat($('#1_Revenue_Total_16').val()||"0")+
                 parseFloat($('#1_Revenue_Total_17').val()||"0")+
                 parseFloat($('#1_Revenue_Total_18').val()||"0")+
                 parseFloat($('#1_Revenue_Total_19').val()||"0")+
                 parseFloat($('#1_Revenue_Total_20').val()||"0")+
                 parseFloat($('#1_Revenue_Total_21').val()||"0")+
                 parseFloat($('#1_Revenue_Total_22').val()||"0")+
                 parseFloat($('#1_Revenue_Total_23').val()||"0")+
                 parseFloat($('#1_Revenue_Total_24').val()||"0")+
                 parseFloat($('#1_Revenue_Total_25').val()||"0");
    $('#2_Total_Revenue_Total_1').val(tmpval||"0");
    NDigitCheck($('#2_Total_Revenue_Total_1'));
}
function run2_Total_Rep_Commission_cost_1(){
    var tmpval = parseFloat($('#1_Rep_Commission_cost_1').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_2').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_3').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_4').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_5').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_6').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_7').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_8').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_9').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_10').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_11').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_12').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_13').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_14').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_15').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_16').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_17').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_18').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_19').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_20').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_21').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_22').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_23').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_24').val()||"0")+
                 parseFloat($('#1_Rep_Commission_cost_25').val()||"0"),
        rep_ratio = "0";
    $('#2_Total_Rep_Commission_cost_1').val(tmpval||"0");
    NDigitCheck($('#2_Total_Rep_Commission_cost_1'));

    // update total rep commission ratio
    if (parseFloat($('#2_Total_Revenue_Total_1').val())!=0.0) {
        rep_ratio = (parseFloat($('#2_Total_Rep_Commission_cost_1').val()) / 
            parseFloat($('#2_Total_Revenue_Total_1').val()) * 100).toString();
    }
    $('#2_Total_Rep_Commission_ratio_1').val(rep_ratio);
    NDigitCheck($('#2_Total_Rep_Commission_ratio_1'));
}
function run2_Total_Gross_Profit_1(){
    var tmpval = 100*(  parseFloat($('#2_Total_Revenue_Total_1').val()||"0")-
                    parseFloat($('#2_Total_Cost_Total_1').val()||"0") )/
                 parseFloat($('#2_Total_Revenue_Total_1').val()||"0");
    $('#2_Total_Gross_Profit_1').val(tmpval||"0");
    NDigitCheck($('#2_Total_Gross_Profit_1'));
}
function run2_Total_Net_Profit_1(){
    var tmpval = 100*(  parseFloat($('#2_Total_Revenue_Total_1').val()||"0")-
                    parseFloat($('#2_Total_Cost_Total_1').val()||"0") -
                    parseFloat($('#2_Total_Rep_Commission_cost_1').val()||"0") )/
                 parseFloat($('#2_Total_Revenue_Total_1').val()||"0");
    $('#2_Total_Net_Profit_1').val(tmpval||"0");
    NDigitCheck($('#2_Total_Net_Profit_1'));
}
function run2_all(){
    run2_Total_Cost_Total_1();
    run2_Total_Revenue_Total_1();
    run2_Total_Rep_Commission_cost_1();
    run2_Total_Gross_Profit_1();
    run2_Total_Net_Profit_1();
}

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
    function run1_Total_unit_1(){
        var tmpval= parseFloat($('#1_Landed_Cost_1').val()||"0")+
                    parseFloat($('#1_D2D_Freight_1').val()||"0")+
                    parseFloat($('#1_Inside_Delivery_1').val()||"0")+
                    parseFloat($('#1_Accessory1_1').val()||"0")+
                    parseFloat($('#1_Accessory2_1').val()||"0");
        $('#1_Total_unit_1').val(tmpval||"0");
        NDigitCheck($('#1_Total_unit_1'));
    }
    function run1_Cost_Total_1(){
        var tmpval =    parseFloat($('#1_Qty_1').val()||"0")*
                        parseFloat($('#1_Total_unit_1').val()||"0");
        $('#1_Cost_Total_1').val(tmpval||"0");
        NDigitCheck($('#1_Cost_Total_1'));
    }
    function run1_Discount_Rate_1(){
        var tmpval = 100*( 1 -( parseFloat($('#1_Win_Win_1').val()||"0") /  
                            parseFloat($('#1_List_Price_1').val()||"0") ) );
        $('#1_Discount_Rate_1').val(tmpval||"0");
        NDigitCheck($('#1_Discount_Rate_1'));
    }
    function run1_Revenue_Total_1(){
        var tmpval =    parseFloat($('#1_Qty_1').val()||"0")*
                        parseFloat($('#1_Win_Win_1').val()||"0");
        $('#1_Revenue_Total_1').val(tmpval||"0");
        NDigitCheck($('#1_Revenue_Total_1'));
    }
    function run1_Gross_Profit_1(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_1').val()||"0")-
                        parseFloat($('#1_Cost_Total_1').val()||"0") ) / 
                      parseFloat($('#1_Revenue_Total_1').val()||"0") ;
        $('#1_Gross_Profit_1').val(tmpval||"0");
        NDigitCheck($('#1_Gross_Profit_1'));
    }
    function run1_Rep_Commission_cost_1(){
        var tmpval =    parseFloat($('#1_Revenue_Total_1').val()||"0")*
                        0.01*parseFloat($('#1_Rep_Commission_ratio_1').val()||"0");
        $('#1_Rep_Commission_cost_1').val(tmpval||"0");
        NDigitCheck($('#1_Rep_Commission_cost_1'));
    }
    function run1_Net_Profit_1(){
        var tmpval =  100*( parseFloat($('#1_Revenue_Total_1').val()||"0")-
                        parseFloat($('#1_Cost_Total_1').val()||"0")-  
                        parseFloat($('#1_Rep_Commission_cost_1').val()||"0") )/
                      parseFloat($('#1_Revenue_Total_1').val()||"0");
        $('#1_Net_Profit_1').val(tmpval||"0");
        NDigitCheck($('#1_Net_Profit_1'));
    }

    function run1_all(){
        run1_Total_unit_1();
        run1_Cost_Total_1();
        run1_Discount_Rate_1();
        run1_Revenue_Total_1();
        run1_Gross_Profit_1();
        run1_Rep_Commission_cost_1();
        run1_Net_Profit_1();
    }

    $('#1_Landed_Cost_1').change(function(){ run_all(); }).change();
    $('#1_D2D_Freight_1').change(function(){ run_all(); }).change();
    $('#1_Inside_Delivery_1').change(function(){ run_all(); }).change();
    $('#1_Accessory1_1').change(function(){ run_all(); }).change();
    $('#1_Accessory2_1').change(function(){ run_all(); }).change();
    $('#1_List_Price_1').change(function(){ run_all(); }).change();
    $('#1_Win_Win_1').change(function(){ run_all(); }).change();
    $('#1_Qty_1').change(function(){ run_all(); }).change();
    $('#1_Win_Win_1').change(function(){ run_all(); }).change();
    $('#1_Rep_Commission_ratio_1').change(function(){ run_all(); }).change();
});
