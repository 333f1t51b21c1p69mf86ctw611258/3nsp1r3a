$(document).ready(function(){
    // Common config for all rows
    function runSub_Total4(){
        var tmpval = parseFloat($('#NT_Techno4').val()||"0")+
                     parseFloat($('#In_Transit4').val()||"0")+
                     parseFloat($('#Yard_Press4').val()||"0")+
                     parseFloat($('#PC_Loc4').val()||"0")+
                     parseFloat($('#CL_Mach4').val()||"0")+
                     parseFloat($('#Oflow4').val()||"0")+
                     parseFloat($('#Lineside4').val()||"0");
        $('#Sub_Total4').val(tmpval||"0");
        NDigitCheck($('#Sub_Total4'));
    }

    function run_all(){
        runSub_Total4();
    }

    $('#NT_Techno4').change(function(){ run_all(); }).change();
    $('#In_Transit4').change(function(){ run_all(); }).change();
    $('#Yard_Press4').change(function(){ run_all(); }).change();
    $('#PC_Loc4').change(function(){ run_all(); }).change();
    $('#CL_Mach4').change(function(){ run_all(); }).change();
    $('#Oflow4').change(function(){ run_all(); }).change();
    $('#Lineside4').change(function(){ run_all(); }).change();
});
