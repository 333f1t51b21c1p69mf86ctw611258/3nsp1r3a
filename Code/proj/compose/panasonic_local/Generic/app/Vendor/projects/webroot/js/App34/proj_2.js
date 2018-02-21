// Auto completion for date
$(document).ready(function() {
    function pad(n, width, z) {
      z = z || '0';
      n = n + '';
      return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }
    function genDateOfTheWeek(startDateString, offset){
        var baseDate = new Date(startDateString);
        baseDate.setDate(baseDate.getDate() + offset);
        var month = pad(baseDate.getMonth() + 1, 2);
        var date = pad(baseDate.getDate(), 2);
        var year = pad(baseDate.getFullYear(), 4); //baseDate.getFullYear().toString().substring(2,4);
        return month + '/' + date + '/' + year;
    }
    function setDateFields( date_begin ){
        $('#1_Mileage_date_1').val( date_begin );
        var target_fields_base = ['#1_Expenses_date_', '#1_Mileage_date_'];
        for( i=2; i<=7; i++ ){
            target_fields_base.forEach( function( cssid_base ){
                var target_cssid = cssid_base+i;
                $(target_cssid).val( genDateOfTheWeek( date_begin, i-1 ) );
            });
        }
        // Report_Period_Start, Report_Period_End
        var report_begin_cssid = '#Report_Period_Start';
        var report_end_cssid = '#Report_Period_End';
        $(report_begin_cssid).val( date_begin );
        $(report_end_cssid).val( genDateOfTheWeek( date_begin, 6 ) );
    }

    $('#1_Expenses_date_1').change(function(e) {
        var date_begin = $(this).val();
        var d = new Date(date_begin);
        if( d.getDay()!=0 ){
            //e.preventDefault();
            alert('Please enter Sunday');
            $(this).val('');
            
            return;
        } 
        setDateFields(date_begin);
    });

    // initialize date fields after excel upload
    if( $('#1_Expenses_date_1').val()!='' ){
        var date_begin = $('#1_Expenses_date_1').val();
        setDateFields(date_begin);
    }
});
