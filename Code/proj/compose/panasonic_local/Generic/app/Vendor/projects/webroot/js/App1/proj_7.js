$(document).ready(function(){
    // Common config for all rows
    function run1_Achievement_Ratio_7(){
        var tmpval = parseFloat($('#1_Actual_Bklg_7').val()||"0")/
                     parseFloat($('#1_Sales_budget_7').val()||"0")*100;
        $('#1_Achievement_Ratio_7').val(tmpval||"0");
        NDigitCheck($('#1_Achievement_Ratio_7'));
    }

    function run_all(){
        run1_Achievement_Ratio_7();
    }

    $('#1_Sales_budget_7').change(function(){ run_all(); } ).change();
    $('#1_Actual_Bklg_7').change(function(){ run_all(); } ).change();
});

