$(document).ready(function(){
    // Common config for all rows
    function runSub_Total12(){
        var tmpval = parseFloat($('#NT_Techno12').val()||"0")+
                     parseFloat($('#In_Transit12').val()||"0")+
                     parseFloat($('#Yard_Press12').val()||"0")+
                     parseFloat($('#PC_Loc12').val()||"0")+
                     parseFloat($('#CL_Mach12').val()||"0")+
                     parseFloat($('#Oflow12').val()||"0")+
                     parseFloat($('#Lineside12').val()||"0");
        $('#Sub_Total12').val(tmpval||"0");
        NDigitCheck($('#Sub_Total12'));
    }

    function run_all(){
        runSub_Total12();
    }

    $('#NT_Techno12').change(function(){ run_all(); }).change();
    $('#In_Transit12').change(function(){ run_all(); }).change();
    $('#Yard_Press12').change(function(){ run_all(); }).change();
    $('#PC_Loc12').change(function(){ run_all(); }).change();
    $('#CL_Mach12').change(function(){ run_all(); }).change();
    $('#Oflow12').change(function(){ run_all(); }).change();
    $('#Lineside12').change(function(){ run_all(); }).change();
});
