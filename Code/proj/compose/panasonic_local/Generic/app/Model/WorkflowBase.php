<?php
App::uses('AppModel', 'Model');
App::uses('CakeEvent', 'Event');
App::uses('WorkflowEventLog', 'Event');
 
class WorkflowBase extends AppModel {
    //var $useTable = 'wf_attr_bases';
    public $useDbConfig = 'genericdata';

    protected function getCurrentUser() {
        App::uses('CakeSession', 'Model/Datasource');
        $Session = new CakeSession();

        $user = $Session->read('Auth.User');
        return $user;
    }
    protected function createCakeEvent($eventName, $dbModelName, $plugin, $text){
        $user = $this->getCurrentUser();
        // TODO: update Event/WorkflowChangeLogger.php when adding new attrs
        return new CakeEvent($eventName, $this, array(
                    'subject_id' => $this->data[$dbModelName]['subject_id'],
                    'updated_at' => $this->data[$dbModelName]['created_at'],
                    'state'      => $this->data[$dbModelName]['prev_state'],
                    'state_new'  => $this->data[$dbModelName]['state'],
                    'action'     => $this->data[$dbModelName]['action'],
                    'username'   => $this->data[$dbModelName]['creator_id'],
                    'case_state'   => $this->data[$dbModelName]['case_state'],
                    'case_state_text'   => $this->data[$dbModelName]['case_state_text'],
                    'additional_text' => $text,
                    'plugin_name'     => $plugin, 
                    'assignee'        => $this->data[$dbModelName]['assignee'],
                    'prev_assignee'   => $this->data[$dbModelName]['prev_assignee'],
                    'nth'        => $this->data[$dbModelName]['nth'],
        ));
    }

    //public function afterSave($created) = 0;
}
