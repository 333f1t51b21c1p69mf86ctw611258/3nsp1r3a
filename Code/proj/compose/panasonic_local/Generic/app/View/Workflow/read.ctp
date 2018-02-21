<?php
$this->Html->script('BriodeFormHandler', array('inline' => false));
$this->Html->script('tinymce_readonly', array('inline' => false));
$this->Html->script('../'.strtolower($pluginName).'/js/BriodeValidation_generated', array('inline' => false));
//$this->Html->script('../'.strtolower($pluginName).'/js/GenericHandler_generated', array('inline' => false));

echo $this->WorkflowForm->display_form($pluginName, $source_id, $doc, $actions, $logs, $options, $wf_options);

