$(document).ready(function(){
    // Common config for all rows
    function run1_Achievement_Ratio_6(){
        var tmpval = parseFloat($('#1_Actual_Bklg_6').val()||"0")/
                     parseFloat($('#1_Sales_budget_6').val()||"0")*100;
        $('#1_Achievement_Ratio_6').val(tmpval||"0");
        NDigitCheck($('#1_Achievement_Ratio_6'));
    }

    function run_all(){
        run1_Achievement_Ratio_6();
    }

    $('#1_Sales_budget_6').change(function(){ run_all(); } ).change();
    $('#1_Actual_Bklg_6').change(function(){ run_all(); } ).change();
});

