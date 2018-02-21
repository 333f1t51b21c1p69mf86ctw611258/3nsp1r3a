<?php 
$this->Html->script('BriodeFormHandler', array('inline' => false));
//$this->Html->script('jquery.plugin', array('inline' => false));
//$this->Html->script('jquery.keypad', array('inline' => false));
$this->Html->script('tinymce_readwrite', array('inline' => false));
$this->Html->script('typeahead.bundle.js', array('inline' => false));
$ts = time();
// FIXME: typeahead css is read from default.ctp
$this->Html->script('../'.strtolower($pluginName).'/js/BriodeValidation_generated', array('inline' => false));
//$this->Html->script('../'.strtolower($pluginName).'/js/GenericHandler_generated', array('inline' => false));
//$this->Html->script('../'.strtolower($pluginName).'/js/FormulaFromExcel_Generated', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_merged.js?'.$ts, array('inline' => false));
echo $this->Html->css(array('../'.strtolower($pluginName).'/css/proj'));
 
echo $this->WorkflowForm->display_form($pluginName, $source_id=NULL, $doc, $actions, $logs, $options, $wf_options);
 
?>
<!--<script type="text/javascript" src="/Generic/js/<?php echo '../'.strtolower($pluginName).'/js/proj_11.js?v='.time();?>"></script> -->

