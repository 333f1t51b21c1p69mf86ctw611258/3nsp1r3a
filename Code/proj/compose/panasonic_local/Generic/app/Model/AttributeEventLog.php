<?php
App::uses('AppModel', 'Model');
 
class AttributeEventLog extends AppModel {
    var $useTable = 'attribute_event_logs';
    public $useDbConfig = 'genericdata';
}
