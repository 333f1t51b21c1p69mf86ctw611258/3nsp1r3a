<?php
$this->Html->script('DbConfigLibrary', array('inline' => false));
#$this->Html->script('BriodeFormHandler', array('inline' => false));

echo $this->BriodeForm->display_form($pluginName, $source_id=NULL, $doc, $actions);
#echo $this->element('layoutConfigDialog');
