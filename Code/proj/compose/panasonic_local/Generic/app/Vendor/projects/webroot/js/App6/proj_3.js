$(document).ready(function(){
    // Common config for all rows
    function runSub_Total3(){
        var tmpval = parseFloat($('#NT_Techno3').val()||"0")+
                     parseFloat($('#In_Transit3').val()||"0")+
                     parseFloat($('#Yard_Press3').val()||"0")+
                     parseFloat($('#PC_Loc3').val()||"0")+
                     parseFloat($('#CL_Mach3').val()||"0")+
                     parseFloat($('#Oflow3').val()||"0")+
                     parseFloat($('#Lineside3').val()||"0");
        $('#Sub_Total3').val(tmpval||"0");
        NDigitCheck($('#Sub_Total3'));
    }

    function run_all(){
        runSub_Total3();
    }

    $('#NT_Techno3').change(function(){ run_all(); }).change();
    $('#In_Transit3').change(function(){ run_all(); }).change();
    $('#Yard_Press3').change(function(){ run_all(); }).change();
    $('#PC_Loc3').change(function(){ run_all(); }).change();
    $('#CL_Mach3').change(function(){ run_all(); }).change();
    $('#Oflow3').change(function(){ run_all(); }).change();
    $('#Lineside3').change(function(){ run_all(); }).change();
});
