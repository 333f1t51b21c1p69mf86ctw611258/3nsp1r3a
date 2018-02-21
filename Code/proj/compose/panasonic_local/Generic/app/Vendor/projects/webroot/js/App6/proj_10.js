$(document).ready(function(){
    // Common config for all rows
    function runSub_Total10(){
        var tmpval = parseFloat($('#NT_Techno10').val()||"0")+
                     parseFloat($('#In_Transit10').val()||"0")+
                     parseFloat($('#Yard_Press10').val()||"0")+
                     parseFloat($('#PC_Loc10').val()||"0")+
                     parseFloat($('#CL_Mach10').val()||"0")+
                     parseFloat($('#Oflow10').val()||"0")+
                     parseFloat($('#Lineside10').val()||"0");
        $('#Sub_Total10').val(tmpval||"0");
        NDigitCheck($('#Sub_Total10'));
    }

    function run_all(){
        runSub_Total10();
    }

    $('#NT_Techno10').change(function(){ run_all(); }).change();
    $('#In_Transit10').change(function(){ run_all(); }).change();
    $('#Yard_Press10').change(function(){ run_all(); }).change();
    $('#PC_Loc10').change(function(){ run_all(); }).change();
    $('#CL_Mach10').change(function(){ run_all(); }).change();
    $('#Oflow10').change(function(){ run_all(); }).change();
    $('#Lineside10').change(function(){ run_all(); }).change();
});
