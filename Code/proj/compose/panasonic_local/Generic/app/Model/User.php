<?php
App::uses('AppModel', 'Model');
 
class User extends AppModel {
    public $useDbConfig = 'genericdata';

    public $validate = array(
        'username' => array(
            'required' => true,
                'rule' => array('notEmpty'),
                'message' => 'A username is required'
        ),
        'usertype' => array(
            'required' => true,
                'rule' => array('between', 0, 4),
                'message' => 'Please enter a valid usertype'
        )
    );
    
    public function beforeSave($options = array()) {
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        }
        return true;
    }

}
