$(document).ready(function(){
    // Common config for all rows
    function runSub_Total14(){
        var tmpval = parseFloat($('#NT_Techno14').val()||"0")+
                     parseFloat($('#In_Transit14').val()||"0")+
                     parseFloat($('#Yard_Press14').val()||"0")+
                     parseFloat($('#PC_Loc14').val()||"0")+
                     parseFloat($('#CL_Mach14').val()||"0")+
                     parseFloat($('#Oflow14').val()||"0")+
                     parseFloat($('#Lineside14').val()||"0");
        $('#Sub_Total14').val(tmpval||"0");
        NDigitCheck($('#Sub_Total14'));
    }

    function run_all(){
        runSub_Total14();
    }

    $('#NT_Techno14').change(function(){ run_all(); }).change();
    $('#In_Transit14').change(function(){ run_all(); }).change();
    $('#Yard_Press14').change(function(){ run_all(); }).change();
    $('#PC_Loc14').change(function(){ run_all(); }).change();
    $('#CL_Mach14').change(function(){ run_all(); }).change();
    $('#Oflow14').change(function(){ run_all(); }).change();
    $('#Lineside14').change(function(){ run_all(); }).change();
});
