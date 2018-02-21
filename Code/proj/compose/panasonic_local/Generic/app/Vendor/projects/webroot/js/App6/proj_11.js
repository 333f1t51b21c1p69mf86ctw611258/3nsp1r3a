$(document).ready(function(){
    // Common config for all rows
    function runSub_Total11(){
        var tmpval = parseFloat($('#NT_Techno11').val()||"0")+
                     parseFloat($('#In_Transit11').val()||"0")+
                     parseFloat($('#Yard_Press11').val()||"0")+
                     parseFloat($('#PC_Loc11').val()||"0")+
                     parseFloat($('#CL_Mach11').val()||"0")+
                     parseFloat($('#Oflow11').val()||"0")+
                     parseFloat($('#Lineside11').val()||"0");
        $('#Sub_Total11').val(tmpval||"0");
        NDigitCheck($('#Sub_Total11'));
    }

    function run_all(){
        runSub_Total11();
    }

    $('#NT_Techno11').change(function(){ run_all(); }).change();
    $('#In_Transit11').change(function(){ run_all(); }).change();
    $('#Yard_Press11').change(function(){ run_all(); }).change();
    $('#PC_Loc11').change(function(){ run_all(); }).change();
    $('#CL_Mach11').change(function(){ run_all(); }).change();
    $('#Oflow11').change(function(){ run_all(); }).change();
    $('#Lineside11').change(function(){ run_all(); }).change();
});
