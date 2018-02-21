$(document).ready(function(){
    // Common config for all rows
    function runSub_Total16(){
        var tmpval = parseFloat($('#NT_Techno16').val()||"0")+
                     parseFloat($('#In_Transit16').val()||"0")+
                     parseFloat($('#Yard_Press16').val()||"0")+
                     parseFloat($('#PC_Loc16').val()||"0")+
                     parseFloat($('#CL_Mach16').val()||"0")+
                     parseFloat($('#Oflow16').val()||"0")+
                     parseFloat($('#Lineside16').val()||"0");
        $('#Sub_Total16').val(tmpval||"0");
        NDigitCheck($('#Sub_Total16'));
    }

    function run_all(){
        runSub_Total16();
    }

    $('#NT_Techno16').change(function(){ run_all(); }).change();
    $('#In_Transit16').change(function(){ run_all(); }).change();
    $('#Yard_Press16').change(function(){ run_all(); }).change();
    $('#PC_Loc16').change(function(){ run_all(); }).change();
    $('#CL_Mach16').change(function(){ run_all(); }).change();
    $('#Oflow16').change(function(){ run_all(); }).change();
    $('#Lineside16').change(function(){ run_all(); }).change();
});
