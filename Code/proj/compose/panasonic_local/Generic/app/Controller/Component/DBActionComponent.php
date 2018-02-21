<?php
App::uses('Component', 'Controller');

class DBActionComponent extends Component {

    public $components = array('Auth', 'ConfigService', );

    // set by is_owner()
    private $creator_username;
    private $creator_userid;

    protected function is_read_request($current_action){
        $read_actions = array('read', 'back', 'main_menu');
        return in_array($current_action, $read_actions);
    }

    private function get_owner($pluginModelName, $form_id){
        $pluginModel = ClassRegistry::init($pluginModelName);
        return $pluginModel->findById($form_id);
    }

    protected function is_owner($pluginModelName, $form_id, $username ){
        $this->creator_userid = NULL;
        $this->creator_username = NULL;
        $ins = $this->get_owner($pluginModelName, $form_id);

        // if id is null, then at create and login should be the owner
        if( empty($ins) ) return true;
        $this->creator_username = $ins[$pluginModelName]['creator_id'];
        $userModel = ClassRegistry::init('User');
        $user = $userModel->findByUsername($this->creator_username);
        $this->creator_userid = $user['User']['id'];

        //print_r( $ins);
        if( strcmp($this->creator_username, $username)==0 ){
            return true;
        }
        return false;
    }

    // find if login user is one of supervisors of the issue owner
    private function exist_supervisor($owner, $loginUsername){
        $userModel = ClassRegistry::init('User');
        $user = $userModel->findByUsername($owner);
        $managerName = $user['User']['manager'];
        $managerNamesVisited = array();
        while( !empty($managerName) && !in_array($managerName, $managerNamesVisited) ){
            //$this->log('exist_supervisor, managerName='.$managerName, 'debug');
            //$this->log($managerNamesVisited, 'debug');
            array_push($managerNamesVisited, $managerName);
            $manager = $userModel->findByName($managerName);
            //$this->log('exist_supervisor, managerName='.$managerName, 'debug');
            $managerUsername = $manager['User']['username'];
            if( strcmp($managerUsername, $loginUsername)==0 ){
                return true;
            }
            if( strcmp($managerName, $manager['User']['manager'])==0 ) break;
            $managerName = $manager['User']['manager'];
        }
        return false;
    }

    protected function is_supervisor($pluginModelName, $pluginName, $id, $loginUsername){
        $userModel = ClassRegistry::init('User');
        $owner = $this->get_owner($pluginModelName, $id);
        $ownerUsername = $owner[$pluginModelName]['creator_id'];

        // check if the person is higher than the owner
        if( $this->exist_supervisor($ownerUsername, $loginUsername) ) return true;

        // ..or if this is workflow app, check if this person is currently assigned
        $workflowLogModel = ClassRegistry::init('WorkflowEventLog');
        $wflog = $workflowLogModel->find('first', array(
                   'conditions' => array(
                        'plugin_name' => $pluginName,
                        'subject_id' => $id,
                    ),
                    'order' => array('updated_at'=>'DESC')));
        if( !empty($wfLog) ){
            $assigned = $wfLog['WorkflowEventLog']['assignee'];
            if( strcmp($assignee, $loginUsername)==0 ) return true;
        }
        return false;
    }

    public function check_briode_privilege($pluginName, $userId, $current_action, $pluginModelName, $form_id){
        // Break if readable_by_all is set
        $username = $this->Auth->user('username');
        if( $this->is_read_request($current_action) &&
            $this->ConfigService->is_readable_by_all($pluginName) ){
            return true;
        }

        // Otherihwise, visible only if the logged in user is one of the following
        // owner
        if( $this->is_owner($pluginModelName, $form_id, $username) ) return true;
        $this->log('check_briode_privilege:is_owner() is false', 'debug');

        // higher supervisor in approval line
        if( $this->is_supervisor($pluginModelName, $pluginName, $form_id, $username) ) return true;
        
        // manager
        if( !empty($this->creator_userid) &&
            $this->ConfigService->is_group_manager($this->creator_userid,
                                                   $userId) ){
            return true;
        }
        $this->log('check_briode_privilege:is_group_manaer() is false', 'debug');

        // admin
        if( $this->ConfigService->is_admin($pluginName, $userId) ) return true;
        $this->log('check_briode_privilege:is_admin() is false', 'debug');
        return false;
    }

    public function get_actions($pluginName, $ctr_action, $pluginModelName, $form_id, $enableViewAttachment, $exportToExcel=true){
        $username = $this->Auth->user('username');
        //$owner = $this->is_owner($pluginModelName, $form_id, $username);
        $action_to_button = array(
                'preview'=>array('cancel_preview', 'preview_layout'),
                'reedit_layout'=> array('cancel_preview', 'preview_layout'),
                'preview_layout'=> array('reedit_layout', 'upload_confirmation'),
                'create'=> array('back', 'upload_single_data', 'create_check'),
                'upload_single_data'=> array('back', 'upload_single_data', 'create_check'),
                'read'=>array('back'),
                'update'=>array('back','save'), 
        );
        $ret = $action_to_button[$ctr_action];

        $export_excel = array(
                'read'=>array('export_to_excel'),
                'update'=>array('export_to_excel')
        );
        if( in_array($ctr_action, array_keys($export_excel))
            && $exportToExcel ){
            $ret = array_merge($ret, $export_excel[$ctr_action]);
        }

        $auth_required = array(
                'read'=>array('update'),
                'update'=>array('delete'),
        );
        $userId = $this->Auth->user('id');
        $aro = array('model'=>'User', 'foreign_key' => $userId);
        $this->log('array_keys(auth_req)=','debug');
        $this->log(array_keys($auth_required), 'debug');
        if( in_array($ctr_action, array_keys($auth_required)) ){
            foreach( $auth_required[$ctr_action] as $action_eval ){
                if( $this->ConfigService->get_acl_aco($pluginName, $action_eval ) &&
                    $this->check_briode_privilege(
                            $pluginName,
                            $userId,
                            $action_eval,
                            $pluginModelName,
                            $form_id ) ){
                    array_push($ret, $action_eval);
                }
            }
        }

        if($enableViewAttachment){
            $ret = array_merge($ret, array('view_attachment'));
        }
        
        $this->log('get_actions return $ret ->', 'debug');
        $this->log($ret, 'debug');
        return $ret;
    }
}
