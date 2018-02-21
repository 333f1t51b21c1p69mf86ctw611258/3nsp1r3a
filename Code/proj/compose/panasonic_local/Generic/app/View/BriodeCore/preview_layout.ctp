<?php 
$this->Html->script('BriodeFormHandler', array('inline' => false)); 
$this->Html->script('tinymce_readonly', array('inline' => false)); 
$this->Html->script('../'.strtolower($pluginName).'/js/GenericHandler_generated', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/FormulaFromExcel_Generated.js', array('inline' => false));
?>

<?php
if( isset($diff1) && isset($diff2) ){
    echo $this->ColumnsDiff->display_column_diff($diff1, $diff2);
}
echo $this->BriodeForm->display_form($pluginName, $source_id=NULL, $doc, $actions);
?>



