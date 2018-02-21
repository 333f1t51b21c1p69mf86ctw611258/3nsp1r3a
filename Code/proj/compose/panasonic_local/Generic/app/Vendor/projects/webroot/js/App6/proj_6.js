$(document).ready(function(){
    // Common config for all rows
    function runSub_Total6(){
        var tmpval = parseFloat($('#NT_Techno6').val()||"0")+
                     parseFloat($('#In_Transit6').val()||"0")+
                     parseFloat($('#Yard_Press6').val()||"0")+
                     parseFloat($('#PC_Loc6').val()||"0")+
                     parseFloat($('#CL_Mach6').val()||"0")+
                     parseFloat($('#Oflow6').val()||"0")+
                     parseFloat($('#Lineside6').val()||"0");
        $('#Sub_Total6').val(tmpval||"0");
        NDigitCheck($('#Sub_Total6'));
    }

    function run_all(){
        runSub_Total6();
    }

    $('#NT_Techno6').change(function(){ run_all(); }).change();
    $('#In_Transit6').change(function(){ run_all(); }).change();
    $('#Yard_Press6').change(function(){ run_all(); }).change();
    $('#PC_Loc6').change(function(){ run_all(); }).change();
    $('#CL_Mach6').change(function(){ run_all(); }).change();
    $('#Oflow6').change(function(){ run_all(); }).change();
    $('#Lineside6').change(function(){ run_all(); }).change();
});
