$(document).ready(function(){
    // Common config for all rows
    function runSub_Total5(){
        var tmpval = parseFloat($('#NT_Techno5').val()||"0")+
                     parseFloat($('#In_Transit5').val()||"0")+
                     parseFloat($('#Yard_Press5').val()||"0")+
                     parseFloat($('#PC_Loc5').val()||"0")+
                     parseFloat($('#CL_Mach5').val()||"0")+
                     parseFloat($('#Oflow5').val()||"0")+
                     parseFloat($('#Lineside5').val()||"0");
        $('#Sub_Total5').val(tmpval||"0");
        NDigitCheck($('#Sub_Total5'));
    }

    function run_all(){
        runSub_Total5();
    }

    $('#NT_Techno5').change(function(){ run_all(); }).change();
    $('#In_Transit5').change(function(){ run_all(); }).change();
    $('#Yard_Press5').change(function(){ run_all(); }).change();
    $('#PC_Loc5').change(function(){ run_all(); }).change();
    $('#CL_Mach5').change(function(){ run_all(); }).change();
    $('#Oflow5').change(function(){ run_all(); }).change();
    $('#Lineside5').change(function(){ run_all(); }).change();
});
