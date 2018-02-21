$(document).ready(function(){
    // Common config for all rows
    function run1_Achievement_Ratio_5(){
        var tmpval = parseFloat($('#1_Actual_Bklg_5').val()||"0")/
                     parseFloat($('#1_Sales_budget_5').val()||"0")*100;
        $('#1_Achievement_Ratio_5').val(tmpval||"0");
        NDigitCheck($('#1_Achievement_Ratio_5'));
    }

    function run_all(){
        run1_Achievement_Ratio_5();
    }

    $('#1_Sales_budget_5').change(function(){ run_all(); } ).change();
    $('#1_Actual_Bklg_5').change(function(){ run_all(); } ).change();
});

