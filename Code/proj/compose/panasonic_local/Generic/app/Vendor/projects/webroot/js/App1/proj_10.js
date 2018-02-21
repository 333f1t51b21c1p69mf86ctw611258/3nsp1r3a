$(document).ready(function(){
    // Common config for all rows
    function run1_Achievement_Ratio_10(){
        var tmpval = parseFloat($('#1_Actual_Bklg_10').val()||"0")/
                     parseFloat($('#1_Sales_budget_10').val()||"0")*100;
        $('#1_Achievement_Ratio_10').val(tmpval||"0");
        NDigitCheck($('#1_Achievement_Ratio_10'));
    }

    function run_all(){
        run1_Achievement_Ratio_10();
    }

    $('#1_Sales_budget_10').change(function(){ run_all(); } ).change();
    $('#1_Actual_Bklg_10').change(function(){ run_all(); } ).change();
});

