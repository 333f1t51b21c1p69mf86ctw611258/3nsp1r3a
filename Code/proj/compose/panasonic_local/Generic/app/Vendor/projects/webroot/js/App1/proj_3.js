$(document).ready(function(){
    // Common config for all rows
    function run1_Achievement_Ratio_3(){
        var tmpval = parseFloat($('#1_Actual_Bklg_3').val()||"0")/
                     parseFloat($('#1_Sales_budget_3').val()||"0")*100;
        $('#1_Achievement_Ratio_3').val(tmpval||"0");
        NDigitCheck($('#1_Achievement_Ratio_3'));
    }

    function run_all(){
        run1_Achievement_Ratio_3();
    }

    $('#1_Sales_budget_3').change(function(){ run_all(); } ).change();
    $('#1_Actual_Bklg_3').change(function(){ run_all(); } ).change();
});

