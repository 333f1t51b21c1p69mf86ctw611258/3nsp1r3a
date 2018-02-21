// formulas
$(document).ready(function() {
    function calcOnChange(rowCssclass, totalCssid){
        $(rowCssclass).change( function() {
            var sum = 0;
            $(rowCssclass).each( function(e) {
                var val = parseFloat($(this).val()) || 0.0;
                sum = sum + val;
            });
            $(totalCssid).val( sum );
        });
    }
    function setSum(elemBaseCssclass, totalCssidPre, totalCssidPost, iterArray){
        iterArray.forEach( function(e) {
            var row_cssclass = "." + elemBaseCssclass + e;
            var total_cssid = totalCssidPre + e + totalCssidPost;
            calcOnChange( row_cssclass, total_cssid );
        });
    }
    function setMileage( mile_cssid, rate_cssid, amount_cssid ) {
        var mileage_val = parseFloat( $(mile_cssid).val() ) || 0.0;
        var rate_val = parseFloat( $(rate_cssid).val() );
        var amount = Math.round(rate_val * mileage_val * 100) / 100;
        $(amount_cssid).val( amount );
    }
    function calcDue(){
        var expense_total = 0.0;
        var due_cssid = '#Due_to_Company';
        var nodue_cssid = '#Due_to_Employee';

        $('.duebase').each( function(e) {
            expense_total += parseFloat($(this).val()) || 0.0;
        });
        $('.duebase_less').each( function(e) {
            expense_total -= parseFloat($(this).val()) || 0.0;
        });
         
        if( expense_total > 0 ){
            due_cssid = '#Due_to_Employee';
            nodue_cssid = '#Due_to_Company';
        }

        expense_total = Math.round(expense_total * 100) / 100;

        $(due_cssid).val(expense_total);
        $(nodue_cssid).val(0);
    }
    
    ////// Expense
    // set vertical subtotal
    setSum('er', '#2_Item', '_total', (function(){var a=[];for(i=1;i<=19;i++){a.push(i);}return a;})());
    // set horizontal subtotal
    setSum('ec', '#3_', '_total', ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']);
    // set expense total
    
    ////// Mileage
    for( i=1;i<=7;i++ ){
        var mile_cssid = '#7_Mileage_Miles_'+i;
        $(mile_cssid).on('change', function() {
            var idx = $(this).attr('id').split("_")[3];
            var rate_cssid = '#Rate_'+idx;
            var amount_cssid = '#7_Mileage_Amount_'+idx;
            var amount_exp_cssid = '#1_Item'+mileage_exp_idx+'_'+idx;
            var mileage_val = parseFloat( $(this).val() ) || 0.0;
            var rate_val = parseFloat( $(rate_cssid).html() );
            var amount = Math.round(rate_val * mileage_val * 100) / 100;
            $(amount_cssid).val( amount );
            $(amount_exp_cssid).val( amount );
        });
    }

    ////// Total - due for corp/employee
    $('.input').change( function() {
        var expense_sum = 0;
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach( function(seq_cssclassbase){
            var subtotal = 0;
            $('.ec'+seq_cssclassbase).each( function() {
                var val = parseFloat($(this).val()) || 0.0;
                subtotal = subtotal + val;
            });
            $('#3_'+seq_cssclassbase+'_total').val(subtotal);
            expense_sum += subtotal;
        });
        [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19].forEach( function(seq_cssclassbase){
            var subtotal = 0;
            $('.er'+seq_cssclassbase).each( function() {
                var val = parseFloat($(this).val()) || 0.0;
                subtotal = subtotal + val;
            });
            $('#2_Item'+seq_cssclassbase+'_total').val(subtotal);
        });
           
        expense_sum = Math.round(expense_sum * 100) / 100;
        $('#4_Total').val( expense_sum );
        $('#Total_Expenses').val( expense_sum );

        calcDue();
    });

    // initialize after excel load
    calcDue();
});
