<?php 
$this->Html->script('BriodeFormHandler', array('inline' => false));
$this->Html->script('tinymce_readonly', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/BriodeValidation_generated', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/GenericHandler_generated', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_1', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_2', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_3', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_4', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_5', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_6', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_7', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_8', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_9', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_10', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_11', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_12', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_13', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_14', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_15', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_16', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_17', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_18', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/proj_19', array('inline' => false));
echo $this->Html->css(array('../'.strtolower($pluginName).'/css/proj'));

echo $this->BriodeForm->display_form($pluginName, $source_id, $doc, $actions, $logs, $options);
 

