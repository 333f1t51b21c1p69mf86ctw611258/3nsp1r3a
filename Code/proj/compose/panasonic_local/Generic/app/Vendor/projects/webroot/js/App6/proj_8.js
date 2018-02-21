$(document).ready(function(){
    // Common config for all rows
    function runSub_Total8(){
        var tmpval = parseFloat($('#NT_Techno8').val()||"0")+
                     parseFloat($('#In_Transit8').val()||"0")+
                     parseFloat($('#Yard_Press8').val()||"0")+
                     parseFloat($('#PC_Loc8').val()||"0")+
                     parseFloat($('#CL_Mach8').val()||"0")+
                     parseFloat($('#Oflow8').val()||"0")+
                     parseFloat($('#Lineside8').val()||"0");
        $('#Sub_Total8').val(tmpval||"0");
        NDigitCheck($('#Sub_Total8'));
    }

    function run_all(){
        runSub_Total8();
    }

    $('#NT_Techno8').change(function(){ run_all(); }).change();
    $('#In_Transit8').change(function(){ run_all(); }).change();
    $('#Yard_Press8').change(function(){ run_all(); }).change();
    $('#PC_Loc8').change(function(){ run_all(); }).change();
    $('#CL_Mach8').change(function(){ run_all(); }).change();
    $('#Oflow8').change(function(){ run_all(); }).change();
    $('#Lineside8').change(function(){ run_all(); }).change();
});
