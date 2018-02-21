$(document).ready(function(){
    function runTotal(){
        var tmpval = parseFloat($('#Expense').val()||"0")+
                     parseFloat($('#Asset').val()||"0");
        $('#Total').val(tmpval||"0");
        NDigitCheck($('#Total'));
    }

    function run_all(){
        runTotal();
    }
    $('#Expense').change(function(){ run_all(); }).change();
    $('#Asset').change(function(){ run_all(); }).change();
});

