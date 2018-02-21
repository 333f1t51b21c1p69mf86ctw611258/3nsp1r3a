<?php
App::uses('AppModel', 'Model');
 
class BriodeSession extends AppModel {
    var $useTable = 'briodesessions';
    public $useDbConfig = 'genericdata';
}
