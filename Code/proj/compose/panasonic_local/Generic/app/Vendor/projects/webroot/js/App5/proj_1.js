$(document).ready(function(){
    // Common config for all rows
    function runTotal_Asset(){
        var tmpval = parseFloat($('#Proposed_Asset_Current_Year').val()||"0")+
                     parseFloat($('#Proposed_Asset_After_Current_Year').val()||"0");
        $('#Total_Asset').val(tmpval||"0");
        NDigitCheck($('#Total_Asset'));
    }
    function runTotal_Expense(){
        var tmpval = parseFloat($('#Proposed_Expense_Current_Year').val()||"0")+
                     parseFloat($('#Budget_Expense_After_Current_Year').val()||"0");
        $('#Total_Expense').val(tmpval||"0");
        NDigitCheck($('#Total_Expense'));
    }
    function run1_A(){
        var tmpval = parseFloat($('#Proposed_Asset_Current_Year').val()||"0")+
                     parseFloat($('#Proposed_Expense_Current_Year').val()||"0");
        $('#1_A').val(tmpval||"0");
        NDigitCheck($('#1_A'));
    }
    function runT1_T2(){
        var tmpval = parseFloat($('#Total_Asset').val()||"0")+
                     parseFloat($('#Total_Expense').val()||"0");
        $('#T1_T2').val(tmpval||"0");
        NDigitCheck($('#T1_T2'));
    }


    function run_all(){
        runTotal_Asset();
        runTotal_Expense();
        run1_A();
        runT1_T2();
    }

    $('#Proposed_Asset_Current_Year').change(function(){ run_all(); }).change();
    $('#Proposed_Asset_After_Current_Year').change(function(){ run_all(); }).change();
    $('#Proposed_Expense_Current_Year').change(function(){ run_all(); }).change();
    $('#Budget_Expense_After_Current_Year').change(function(){ run_all(); }).change();
    $('#Total_Asset').change(function(){ run_all(); }).change();
    $('#Total_Expense').change(function(){ run_all(); }).change();
});
