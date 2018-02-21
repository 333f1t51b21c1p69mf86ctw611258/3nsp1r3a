<?php
App::uses('AppModel', 'Model');
 
class WorkflowEventLog extends AppModel {
    var $useTable = 'workflow_event_logs';
    public $useDbConfig = 'genericdata';

    
}
