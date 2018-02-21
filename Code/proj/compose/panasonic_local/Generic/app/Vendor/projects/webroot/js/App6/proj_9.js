$(document).ready(function(){
    // Common config for all rows
    function runSub_Total9(){
        var tmpval = parseFloat($('#NT_Techno9').val()||"0")+
                     parseFloat($('#In_Transit9').val()||"0")+
                     parseFloat($('#Yard_Press9').val()||"0")+
                     parseFloat($('#PC_Loc9').val()||"0")+
                     parseFloat($('#CL_Mach9').val()||"0")+
                     parseFloat($('#Oflow9').val()||"0")+
                     parseFloat($('#Lineside9').val()||"0");
        $('#Sub_Total9').val(tmpval||"0");
        NDigitCheck($('#Sub_Total9'));
    }

    function run_all(){
        runSub_Total9();
    }

    $('#NT_Techno9').change(function(){ run_all(); }).change();
    $('#In_Transit9').change(function(){ run_all(); }).change();
    $('#Yard_Press9').change(function(){ run_all(); }).change();
    $('#PC_Loc9').change(function(){ run_all(); }).change();
    $('#CL_Mach9').change(function(){ run_all(); }).change();
    $('#Oflow9').change(function(){ run_all(); }).change();
    $('#Lineside9').change(function(){ run_all(); }).change();
});
