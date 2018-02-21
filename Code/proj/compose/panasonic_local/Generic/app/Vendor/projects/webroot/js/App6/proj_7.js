$(document).ready(function(){
    // Common config for all rows
    function runSub_Total7(){
        var tmpval = parseFloat($('#NT_Techno7').val()||"0")+
                     parseFloat($('#In_Transit7').val()||"0")+
                     parseFloat($('#Yard_Press7').val()||"0")+
                     parseFloat($('#PC_Loc7').val()||"0")+
                     parseFloat($('#CL_Mach7').val()||"0")+
                     parseFloat($('#Oflow7').val()||"0")+
                     parseFloat($('#Lineside7').val()||"0");
        $('#Sub_Total7').val(tmpval||"0");
        NDigitCheck($('#Sub_Total7'));
    }

    function run_all(){
        runSub_Total7();
    }

    $('#NT_Techno7').change(function(){ run_all(); }).change();
    $('#In_Transit7').change(function(){ run_all(); }).change();
    $('#Yard_Press7').change(function(){ run_all(); }).change();
    $('#PC_Loc7').change(function(){ run_all(); }).change();
    $('#CL_Mach7').change(function(){ run_all(); }).change();
    $('#Oflow7').change(function(){ run_all(); }).change();
    $('#Lineside7').change(function(){ run_all(); }).change();
});
