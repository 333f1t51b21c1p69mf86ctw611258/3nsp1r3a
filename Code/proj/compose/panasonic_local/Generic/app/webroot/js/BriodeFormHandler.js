// validation
var color_mandatory = "#8888ff";
var color_NG = "#ff0000";
var color_OK = "#ffffff";

function rgb2hex(rgb){
    rgb_raw = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\).*/);
    function hex(x) {
        return ("0" + parseInt(x).toString(16)).slice(-2);
    }
    return "#" + hex(rgb_raw[1]) + hex(rgb_raw[2]) + hex(rgb_raw[3]);
}
// check if all mandatory fields are set (no empty fields)
function isAllExist(){
    var ret = true;
    $('.validate_mandatory').each(function(){
        var v = $(this).val();
        if( $(this).val() === "" ){
            ret = false;
        }
    });
    if( ret == true ){
        $('input[name="mandatory_flag"]').val(1);
    }else{
        $('input[name="mandatory_flag"]').val(0);
    }
 
    return ret;
}
// check if OK_NG fields are all selected to OK
function isAllNonNG(){
    var ret = true;
    // FIXME: OK_NG cell not visible thru .ringicell selector
    $('.validate_OK').each(function(){
        var v = $(this).val();
        if( $(this).val() == "NG" ){
            ret = false;
        }
    });
    $('.ringicell').each(function(){
        var color_rgb = $(this).css("background");
        if( color_rgb == null ) return true; // skip null
        var color = rgb2hex(color_rgb);
        if( color == color_NG ){
            ret = false;
        }
    });
    if( ret == true ){
        $('input[name="validation_flag"]').val(1);
    }else{
        $('input[name="validation_flag"]').val(0);
    } 
    return ret;
}
function isSelectedOptionOK(val){
    if( val != "OK" ){
        return false;
    }
    return true;
}
function getBGColorEmpty(element){
    if( element.hasClass("validate_mandatory") ){
        return color_mandatory;
    }

    return color_OK;
}
function getBGColorForOKNG(element){
    // value not entered
    var val = element.val();
    if( val == '' ) return getBGColorEmpty(element);

    if( element.hasClass("validate_OK") ){
        if( isSelectedOptionOK(val) ){
            //if( element.hasClass("validate_mandatory") ){
            //    return color_mandatory;
            //}else{
                return color_OK;
            //}
        }else{
            return color_NG;
        }
    }

    return color_OK;
}

function getBGColorForRange(thLow, bLowInclEqual, thHigh, bHighInclEqual, element){
    // value not entered
    var val = element.val();
    if( val == '' ) return getBGColorEmpty(element);

    // range validation
    var flLow = true;
    var flHigh = true;
    if( thLow != null ){
        if( bLowInclEqual ){
            flLow = (val <= thLow) ? true : false;
        }else{
            flLow = (val < thLow) ? true : false;
        }
    }
    if( thHigh != null ){
        if( bHighInclEqual ){
            flHigh = (thHigh <= val) ? true : false;
        }else{
            flHigh = (thHigh < val) ? true : false;
        }
    }
    if( flLow && flHigh ){
        //if( element.hasClass("validate_mandatory") ){
        //    return color_mandatory;
        //}else{
            return color_OK;
        //}
    }

    // false means value is NG
    return color_NG;
}

// DigitCheck gets called from generated script
function NDigitCheck(element){
    if(element.val().length==0){
        return;
    }
    var classes = element.attr('class').split(" ");
    var numOfDecimals = 0; // two-digits as default
    if( $.inArray('one-digit', classes)>=0 ){
        numOfDecimals = 1;
    }else if( $.inArray('two-digits', classes)>=0 ){
        numOfDecimals = 2;
    }else if( $.inArray('three-digits', classes)>=0 ){
        numOfDecimals = 3;
    }

    if(element.val().indexOf('.')!=-1){
        var existingZero = element.val().split(".")[1].length;
        if(existingZero > numOfDecimals){
            if( isNaN( parseFloat( element.val() ) ) ) return;
            //element.value = parseFloat(element.value).toFixed(numOfDecimals);
        }
        else{
            element.value = element.value + "0000000000";
        }
    }else{
        element.value = element.value + ".0000000000";
    }

    element.val(parseFloat(element.val()).toFixed(numOfDecimals));

    return element;
}

function OnSubmitForm(docAction,docForm)
{
    docForm.action = docAction;

    if( docAction == 'delete' ){
        var ret = confirm( "Are you sure to delete this entry?" );
        if( !ret ) return false;
        return true;
    }

    if (docAction == 'cond_approve') {
        // App2 - run check_approve_condition
        if (!check_approve_condition()) {
            $('#cond_approve_res').val('next')
            alert("Condition didn't meet, sending to next level");
        } else {
            $('#cond_approve_res').val('approve')
            alert("Condition met, completing approval");
        }
    }

    // FIXME actions are hardcoded
    // all update actions needs to be listed
    if( !(docAction == 'save') &&
        !(docAction == 'create') &&
        !(docAction == 'create_check') ){
        return true;
    }

    // if any of the validations fails return false
    if( !isAllExist() ){
        var ret = confirm( "Not all mandatory values are not set. Okay to save?" );
        if( !ret ) return false;
    }

    if( !isAllNonNG() ){
        var ret = confirm( "NG value(s) still exist. Okay to save?" );
        if( !ret ) return false;
    }

    return true;
}

$(document).ready(function(){
    // initial color setting
    $('.validate_mandatory').each(function(){
        if( $(this).val().length == 0 ){
            $(this).css("background", color_mandatory);
        }
    });
    $('.validate_OK').each(function(){
        var color_OKNG = getBGColorForOKNG($(this));
        $(this).css("background", color_OKNG);
    });

    $('.validate_mandatory').change(function(){
        if( !$(this).hasClass('validate_OK') &&
            !$(this).hasClass('validate_range') ){
            if( $(this).val().length == 0 ){
                $(this).css("background", color_mandatory);
            }else{
                $(this).css("background", color_OK);
            }
        }
    });

    $('.validate_OK').change(function(){
        bg = getBGColorForOKNG($(this));
        $(this).css("background", bg);
    });

    ///////////////////////////////////
    // layout formatter for supported types
    //  display       db       class
    //  --------------------------------
    //  string        string   string
    //  integer       int      integer
    //  1st decimal   decimal1 one-digit
    //  10th decimal  decimal2 two-digits
    //  100th decimal decimal3 three-digits
    //  currency      string   currency
    //  date          date     date
    ///////////////////////////////////

    // there is no check for string 
    $('.one-digit').change(function(){
        if( !decimalCheck($(this)) ) return;
        return NDigitCheck($(this));
    }).change();
    $('.integer').change(function(){
        if( !integerCheck($(this)) ) return;
        //return IntegerCheck($(this));
    }).change();
    $('.two-digits').change(function(){
        if( !decimalCheck($(this)) ) return;
        return NDigitCheck($(this));
    }).change();
    $('.three-digits').change(function(){
        if( !decimalCheck($(this)) ) return;
        return NDigitCheck($(this));
    }).change();
/*
    if (navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i)){
        $('.integer').keypad({
                keypadOnly: false,
                layout: ['789'+$.keypad.CLEAR, '456'+$.keypad.BACK, '123'+$.keypad.CLOSE, '.0-'],
                prompt: 'Input numbers'});
        $('.one-digits').keypad({
                keypadOnly: false,
                layout: ['789'+$.keypad.CLEAR, '456'+$.keypad.BACK, '123'+$.keypad.SPACE, '.0-'],
                prompt: 'Input numbers'});
        $('.two-digits').keypad({
                keypadOnly: false,
                layout: ['789'+$.keypad.CLEAR, '456'+$.keypad.BACK, '123'+$.keypad.SPACE, '.0-'],
                prompt: 'Input numbers'});
        $('.three-digits').keypad({
                keypadOnly: false,
                layout: ['789'+$.keypad.CLEAR, '456'+$.keypad.BACK, '123'+$.keypad.SPACE, '.0-'],
                prompt: 'Input numbers'});
        $('.currency').keypad({
                keypadOnly: false,
                layout: ['789'+$.keypad.CLEAR, '456'+$.keypad.BACK, '123'+$.keypad.SPACE, '.0-'],
                prompt: 'Input numbers'});
    }
*/

    // currency,date uses jquery-ui library
    $('.currency').change(function(){
        if( !decimalCheck($(this)) ) return;
        $(this).formatCurrency({symbol:''});
    }).change();
    $('.date').change(function(){
        if( $(this).val().length == 0 ) return;
        if( !dateCheckBySlash($(this)) ) return;
    }).change();

    $('.date:not(.dpoff)').datepicker({ autoclose: true, todayHighlight: true });

    //var nums = $('.integer .one-digit .two-digits .three-digits');
    //jQuery.each(nums,function(number){
    //    $(this).val(0);
    //});

    $('.ringicell').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        if(key == 13) {
            e.preventDefault();
            var inputs = $(this).closest('form').find(':input:visible');
            inputs.eq( inputs.index(this)+ 1 ).focus();
        }
    });

    ///////////////////////////////////
    // Alignment
    //  except otherwise specified, nums are aligned to the right
    ///////////////////////////////////
    $('.currency').css('text-align', 'right');
    $('.integer').css('text-align', 'right');
    $('.one-digit').css('text-align', 'right');
    $('.two-digits').css('text-align', 'right');
    $('.three-digits').css('text-align', 'right');

    ///////////////////////////////////
    // Data validation 
    //  decimal,integer,currency - decimalCheck
    //  date - dateCheckBySlash
    //  otherwise - no check
    ///////////////////////////////////
    function decimalCheck(element){
        element.css('border', '');
        var pattern = /^(\d+(\.\d{1,2})?)?$/;
        var y = element.val();
        var x = y.replace(/,/g,'');
        if (x.match(pattern)){
            return true;
        }
        else{
            element.css("border", "2px solid #ff0000");
            element.focus();
            element.select();
            alert('Please input a numerical value.');
            return false;
        }
    }

    function integerCheck(element){
        element.css('border', '');
        var pattern = /^([\-]*\d+(\d{1,2})?)?$/;
        var y = element.val();
        var x = y.replace(/,/g,'');
        if (x.match(pattern)){
            return true;
        }
        else{
            element.css("border", "2px solid #ff0000");
            element.focus();
            element.select();
            alert('Please input an integer value.');
            return false;
        }
    }

    // date - dateCheckBySlash
    function dateCheckBySlash(element){
        element.css('border', '');
        var x = element.val();
        var dateFormatPattern1 = /^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/](19|20)\d{2}$/;
        if (x.match(dateFormatPattern1)){
            var date = x.split('/');
            var mm = parseInt(date[0]);
            var dd = parseInt(date[1]);
            var yy = parseInt(date[2]);
            var ListofDays = [31,28,31,30,31,30,31,31,30,31,30,31];
            if (mm != 2){
                if (dd > ListofDays[mm-1]){
                    element.css("border", "2px solid #ff0000");
                    element.focus();
                    element.select();
                    alert('Please input a valid date with format mm/dd/yyyy!');
                    return false;
                }
            }
            if (mm == 2){
                var leapYear = true;
                if( yy%4 || (!(yy%100) && (yy%400))){
                    leapYear = false;
                }
                if (leapYear && dd > 29){
                    element.css("border", "2px solid #ff0000");
                    element.focus();
                    element.select();
                    alert('Please input a valid date with format mm/dd/yyyy!');
                    return false;
                }
                if (!leapYear && dd > 28){
                    element.css("border", "2px solid #ff0000");
                    element.focus();
                    element.select();
                    alert('Please input a valid date with format mm/dd/yyyy!');
                    return false;
                }
            }
            return true;
        }
        else {
            element.css("border", "2px solid #ff0000");
            element.focus();
            element.select();
            alert('Please input a valid date with format mm/dd/yyyy!');
            return false;
        }
    }
});
