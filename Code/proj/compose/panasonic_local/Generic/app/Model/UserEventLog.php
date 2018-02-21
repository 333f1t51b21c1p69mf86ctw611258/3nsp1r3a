<?php
App::uses('AppModel', 'Model');
 
class UserEventLog extends AppModel {
    var $useTable = 'user_event_logs';
    public $useDbConfig = 'genericdata';
}
