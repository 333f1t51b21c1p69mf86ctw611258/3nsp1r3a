$(document).ready(function(){
    // Common config for all rows
    function runSub_Total2(){
        var tmpval = parseFloat($('#NT_Techno2').val()||"0")+
                     parseFloat($('#In_Transit2').val()||"0")+
                     parseFloat($('#Yard_Press2').val()||"0")+
                     parseFloat($('#PC_Loc2').val()||"0")+
                     parseFloat($('#CL_Mach2').val()||"0")+
                     parseFloat($('#Oflow2').val()||"0")+
                     parseFloat($('#Lineside2').val()||"0");
        $('#Sub_Total2').val(tmpval||"0");
        NDigitCheck($('#Sub_Total2'));
    }

    function run_all(){
        runSub_Total2();
    }

    $('#NT_Techno2').change(function(){ run_all(); }).change();
    $('#In_Transit2').change(function(){ run_all(); }).change();
    $('#Yard_Press2').change(function(){ run_all(); }).change();
    $('#PC_Loc2').change(function(){ run_all(); }).change();
    $('#CL_Mach2').change(function(){ run_all(); }).change();
    $('#Oflow2').change(function(){ run_all(); }).change();
    $('#Lineside2').change(function(){ run_all(); }).change();
});
