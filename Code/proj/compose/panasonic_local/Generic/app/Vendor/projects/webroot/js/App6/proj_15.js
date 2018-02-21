$(document).ready(function(){
    // Common config for all rows
    function runSub_Total15(){
        var tmpval = parseFloat($('#NT_Techno15').val()||"0")+
                     parseFloat($('#In_Transit15').val()||"0")+
                     parseFloat($('#Yard_Press15').val()||"0")+
                     parseFloat($('#PC_Loc15').val()||"0")+
                     parseFloat($('#CL_Mach15').val()||"0")+
                     parseFloat($('#Oflow15').val()||"0")+
                     parseFloat($('#Lineside15').val()||"0");
        $('#Sub_Total15').val(tmpval||"0");
        NDigitCheck($('#Sub_Total15'));
    }

    function run_all(){
        runSub_Total15();
    }

    $('#NT_Techno15').change(function(){ run_all(); }).change();
    $('#In_Transit15').change(function(){ run_all(); }).change();
    $('#Yard_Press15').change(function(){ run_all(); }).change();
    $('#PC_Loc15').change(function(){ run_all(); }).change();
    $('#CL_Mach15').change(function(){ run_all(); }).change();
    $('#Oflow15').change(function(){ run_all(); }).change();
    $('#Lineside15').change(function(){ run_all(); }).change();
});
