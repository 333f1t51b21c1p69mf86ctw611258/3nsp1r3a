$(document).ready(function(){
    function run2_Total_1(){
        var tmpval = parseFloat($('#1_Cost_1').val()||"0")+
                     parseFloat($('#1_Cost_2').val()||"0")+
                     parseFloat($('#1_Cost_3').val()||"0")+
                     parseFloat($('#1_Cost_4').val()||"0")+
                     parseFloat($('#1_Cost_5').val()||"0")+
                     parseFloat($('#1_Cost_6').val()||"0")+
                     parseFloat($('#1_Cost_7').val()||"0")+
                     parseFloat($('#1_Cost_8').val()||"0")+
                     parseFloat($('#1_Cost_9').val()||"0")+
                     parseFloat($('#1_Cost_10').val()||"0")+
                     parseFloat($('#1_Cost_11').val()||"0")+
                     parseFloat($('#1_Cost_12').val()||"0")+
                     parseFloat($('#1_Cost_13').val()||"0")+
                     parseFloat($('#1_Cost_14').val()||"0")+
                     parseFloat($('#1_Cost_15').val()||"0")+
                     parseFloat($('#1_Cost_16').val()||"0")+
                     parseFloat($('#1_Cost_17').val()||"0")+
                     parseFloat($('#1_Cost_18').val()||"0")+
                     parseFloat($('#1_Cost_19').val()||"0")+
                     parseFloat($('#1_Cost_20').val()||"0")+
                     parseFloat($('#1_Cost_21').val()||"0")+
                     parseFloat($('#1_Cost_22').val()||"0")+
                     parseFloat($('#1_Cost_23').val()||"0")+
                     parseFloat($('#1_Cost_24').val()||"0")+
                     parseFloat($('#1_Cost_25').val()||"0")+
                     parseFloat($('#1_Cost_26').val()||"0")+
                     parseFloat($('#1_Cost_27').val()||"0")+
                     parseFloat($('#1_Cost_28').val()||"0");
        $('#2_Total_1').val(tmpval||"0");
        NDigitCheck($('#2_Total_1'));
    }

    function run_all(){
        run2_Total_1();
    }

    $('#1_Cost_1').change(function(){ run_all(); }).change();
    $('#1_Cost_2').change(function(){ run_all(); }).change();
    $('#1_Cost_3').change(function(){ run_all(); }).change();
    $('#1_Cost_4').change(function(){ run_all(); }).change();
    $('#1_Cost_5').change(function(){ run_all(); }).change();
    $('#1_Cost_6').change(function(){ run_all(); }).change();
    $('#1_Cost_7').change(function(){ run_all(); }).change();
    $('#1_Cost_8').change(function(){ run_all(); }).change();
    $('#1_Cost_9').change(function(){ run_all(); }).change();
    $('#1_Cost_10').change(function(){ run_all(); }).change();
    $('#1_Cost_11').change(function(){ run_all(); }).change();
    $('#1_Cost_12').change(function(){ run_all(); }).change();
    $('#1_Cost_13').change(function(){ run_all(); }).change();
    $('#1_Cost_14').change(function(){ run_all(); }).change();
    $('#1_Cost_15').change(function(){ run_all(); }).change();
    $('#1_Cost_16').change(function(){ run_all(); }).change();
    $('#1_Cost_17').change(function(){ run_all(); }).change();
    $('#1_Cost_18').change(function(){ run_all(); }).change();
    $('#1_Cost_19').change(function(){ run_all(); }).change();
    $('#1_Cost_20').change(function(){ run_all(); }).change();
    $('#1_Cost_21').change(function(){ run_all(); }).change();
    $('#1_Cost_22').change(function(){ run_all(); }).change();
    $('#1_Cost_23').change(function(){ run_all(); }).change();
    $('#1_Cost_24').change(function(){ run_all(); }).change();
    $('#1_Cost_25').change(function(){ run_all(); }).change();
    $('#1_Cost_26').change(function(){ run_all(); }).change();
    $('#1_Cost_27').change(function(){ run_all(); }).change();
    $('#1_Cost_28').change(function(){ run_all(); }).change();
});
