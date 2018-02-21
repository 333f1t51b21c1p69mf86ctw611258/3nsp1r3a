$(document).ready(function(){
    // Common config for all rows
    function runSub_Total17(){
        var tmpval = parseFloat($('#NT_Techno17').val()||"0")+
                     parseFloat($('#In_Transit17').val()||"0")+
                     parseFloat($('#Yard_Press17').val()||"0")+
                     parseFloat($('#PC_Loc17').val()||"0")+
                     parseFloat($('#CL_Mach17').val()||"0")+
                     parseFloat($('#Oflow17').val()||"0")+
                     parseFloat($('#Lineside17').val()||"0");
        $('#Sub_Total17').val(tmpval||"0");
        NDigitCheck($('#Sub_Total17'));
    }

    function run_all(){
        runSub_Total17();
    }

    $('#NT_Techno17').change(function(){ run_all(); }).change();
    $('#In_Transit17').change(function(){ run_all(); }).change();
    $('#Yard_Press17').change(function(){ run_all(); }).change();
    $('#PC_Loc17').change(function(){ run_all(); }).change();
    $('#CL_Mach17').change(function(){ run_all(); }).change();
    $('#Oflow17').change(function(){ run_all(); }).change();
    $('#Lineside17').change(function(){ run_all(); }).change();
});
