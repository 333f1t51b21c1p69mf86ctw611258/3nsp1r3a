$(document).ready(function(){
    // Common config for all rows
    function runSub_Total13(){
        var tmpval = parseFloat($('#NT_Techno13').val()||"0")+
                     parseFloat($('#In_Transit13').val()||"0")+
                     parseFloat($('#Yard_Press13').val()||"0")+
                     parseFloat($('#PC_Loc13').val()||"0")+
                     parseFloat($('#CL_Mach13').val()||"0")+
                     parseFloat($('#Oflow13').val()||"0")+
                     parseFloat($('#Lineside13').val()||"0");
        $('#Sub_Total13').val(tmpval||"0");
        NDigitCheck($('#Sub_Total13'));
    }

    function run_all(){
        runSub_Total13();
    }

    $('#NT_Techno13').change(function(){ run_all(); }).change();
    $('#In_Transit13').change(function(){ run_all(); }).change();
    $('#Yard_Press13').change(function(){ run_all(); }).change();
    $('#PC_Loc13').change(function(){ run_all(); }).change();
    $('#CL_Mach13').change(function(){ run_all(); }).change();
    $('#Oflow13').change(function(){ run_all(); }).change();
    $('#Lineside13').change(function(){ run_all(); }).change();
});
