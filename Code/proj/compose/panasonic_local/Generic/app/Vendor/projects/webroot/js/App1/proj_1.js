$(document).ready(function(){
    // Common config for all rows
    function run1_Achievement_Ratio_1(){
        var tmpval = parseFloat($('#1_Actual_Bklg_1').val()||"0")/
                     parseFloat($('#1_Sales_budget_1').val()||"0")*100;
        $('#1_Achievement_Ratio_1').val(tmpval||"0");
        NDigitCheck($('#1_Achievement_Ratio_1'));
    }

    function run_all(){
        run1_Achievement_Ratio_1();
    }

    $('#1_Sales_budget_1').change(function(){ run_all(); } ).change();
    $('#1_Actual_Bklg_1').change(function(){ run_all(); } ).change();
});

