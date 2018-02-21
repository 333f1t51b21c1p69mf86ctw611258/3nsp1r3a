$(document).ready(function(){
    // Common config for all rows
    function runSub_Total1(){
        var tmpval = parseFloat($('#NT_Techno1').val()||"0")+
                     parseFloat($('#In_Transit1').val()||"0")+
                     parseFloat($('#Yard_Press1').val()||"0")+
                     parseFloat($('#PC_Loc1').val()||"0")+
                     parseFloat($('#CL_Mach1').val()||"0")+
                     parseFloat($('#Oflow1').val()||"0")+
                     parseFloat($('#Lineside1').val()||"0");
        $('#Sub_Total1').val(tmpval||"0");
        NDigitCheck($('#Sub_Total1'));
    }

    function run_all(){
        runSub_Total1();
    }

    $('#NT_Techno1').change(function(){ run_all(); }).change();
    $('#In_Transit1').change(function(){ run_all(); }).change();
    $('#Yard_Press1').change(function(){ run_all(); }).change();
    $('#PC_Loc1').change(function(){ run_all(); }).change();
    $('#CL_Mach1').change(function(){ run_all(); }).change();
    $('#Oflow1').change(function(){ run_all(); }).change();
    $('#Lineside1').change(function(){ run_all(); }).change();
});
