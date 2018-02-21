<?php
# css files
echo $this->Html->css(array('node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css'));
?>

<div class="col-md-6">
    <H2> Export Win-Win sheet</H2>
    Specify the beginning and end of time frame that you want to export data.
    <BR>
<?php
echo $this->element('export_by_date');
?>
</div>

<?php
# scripts
echo $this->Html->script(
    array(
        'node_modules/moment/moment.js',
        'node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
    )
);
