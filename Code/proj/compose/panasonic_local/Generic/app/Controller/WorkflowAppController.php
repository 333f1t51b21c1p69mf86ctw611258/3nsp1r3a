<?php
App::uses('AppController', 'Controller');

class WorkflowAppController extends AppController {

    public $components = array(
        'Workflow',
        'AWSSES', 
        'Acl', 
        'BriodeAcl',
        'EmailService'
    );
    public $uses = array('User','AttributeEventLog');

    protected $workflowDBName;
    protected $workflowModelName;
    protected $workflowModel;
    
    protected $workflowViewModel;
    protected $workflowViewDBName;
    protected $workflowViewModelName;

    protected $commentDBName;
    protected $commentModelName;
    protected $commentModel;

    private $subject_id;

    public function beforeFilter() {
        parent::beforeFilter();

        // $this->cleanupOrphans();

        Configure::write('State.open', 0);
        Configure::write('State.in_progress', 1);
        Configure::write('State.closed', 2);
    }

    /*
     * find orphans whose created_at is one minute older than current time
     */
    private function cleanupOrphans()
    {
        // $newerThan = date('Y-m-d H:i:s', strtotime("2018-01-01 00:00:00"));
        $minuteAgo = date('Y-m-d H:i:s', time() - 60);
        // var_dump($currentTime, $minuteAgo);
        $cond = array(
            'conditions' => array(
                $this->workflowModelName.'.creator_id' => NULL,
                // $this->workflowModelName.'.created_at >' => $newerThan,
                $this->workflowModelName.'.created_at <' => $minuteAgo,
             )
        );
 
        $orphans = $this->workflowModel->find('all', $cond);
        // var_dump(sizeof($orphans));

        // delete entries whose assignee=NULL
        foreach ($orphans as $o) {
            $this->log('cleanupOrphans, removing subject_id'.$o[$this->workflowModelName]['subject_id'], 'debug');
            $this->workflowModel->delete($o[$this->workflowModelName]['id']);
            $this->pluginModel->delete($o[$this->workflowModelName]['subject_id']);
        }
    }

    public function isAuthorized($user=null) {
        $this->log('isAuthorized WF', 'debug');
        $this->log($_GET, 'debug');
        $this->log($this->request->params, 'debug');
        $this->log($this->workflowModel, 'debug');
        if( $this->Workflow->check_briode_privilege(
                        $this->pluginName,
                        $this->workflowModel,
                        $this->Auth->user('id'),
                        $this->action,
                        $this->pluginModelName,
                        $this->workflowModelName,
                        $this->get_field_data('id',NULL),
                        $this->_get_subject_owner_aro()
            ) 
        ) {
            //$this->log('isAuthorized in WF returning true', 'debug');
            return true;
        }

        return parent::isAuthorized($user);
    }

    /*
        called when updating the page - consecutive calls might lose its 
        current session_id
    */
    private function _save_subject_id()
    {
        $subject_id = $this->get_field_data('id');
        $this->log('_save_subject_id: id='.$subject_id, 'debug');

        $this->Session->write('subject_id', $subject_id);
    }

    private function _load_subject_id()
    {
        $subject_id = $this->Session->read('subject_id');
        $this->log('_load_subject_id: id='.$subject_id, 'debug');
        return $subject_id;
    }

    protected function assigned(){
        $ins = $this->workflowModel->findBySubjectId($this->get_field_data('id', NULL));
        if( !empty($ins) &&
            strcmp($ins[$this->workflowModelName]['assignee'],$this->Auth->user('username'))==0 ){
            return true;
        }
        return false;
    }

    protected function setConfigFromDict($appname) {
        parent::setConfigFromDict($appname);
        $this->workflowModelName = $this->ConfigService->get_workflow_model_name(
                                    $this->pluginName);
        $this->workflowDBName = $this->ConfigService->get_workflow_db_name(
                                    $this->pluginName);
        $this->loadModel($this->pluginName.'.'.$this->workflowModelName);
        $this->workflowModel = eval('return $this->'.$this->workflowModelName.';');

        $this->workflowViewModelName = 'V'.strtolower($this->workflowModelName);
        $this->workflowViewDBName = 'v'.$this->workflowDBName;
        $this->loadModel($this->pluginName.'.'.$this->workflowViewModelName);
        $this->workflowViewModel = eval('return $this->'.$this->workflowViewModelName.';');

        $this->commentModelName = $this->ConfigService->get_workflow_comment_model_name(
                                    $this->pluginName);
        $this->commentDBName = $this->ConfigService->get_workflow_comment_db_name(
                                    $this->pluginName);
        $this->loadModel($this->commentModelName);
        $this->commentModel = eval('return $this->'.$this->commentModelName.';');
    }

    private function _get_me_as_manager_in_approved_as_group($user_aro)
    {
        $approval_op_group_ids = $this->ConfigService->get_approval_op_allowed_group($this->pluginName);
        // only pick up groups whose manager is login user
        $me_as_manager_approval_group_ids = array();
        foreach ($approval_op_group_ids as $g_id) {
            $manager_found = $this->ConfigService->get_managers_in_groups(array($g_id))[0];
            if ($manager_found == $user_aro) {
                array_push($me_as_manager_approval_group_ids, $g_id);
            }
        }
        $this->log('get_buddy_in_assigned_to_group, my_groups= ', 'debug');
        $this->log($me_as_manager_approval_group_ids, 'debug');

        return $me_as_manager_approval_group_ids;
    }

    private function _getCurrentPluginURL()
    {
        $orig_url = Router::url( $this->here, false);
        $url_arr = explode('/', $orig_url);
        // workaround:
        // somehow Generic gets embedded twice in absolute URL, removing it explicitly
        unset($url_arr[3]);
        // current action needs removal
        array_pop($url_arr);
        return 'https://'.implode('/', $url_arr);
    }

    // OVERRIDE
    protected function setList(){
        $this->paginate = $this->get_condition($this->action);
        $this->Paginator->settings = $this->paginate;
        $attrs = $this->Paginator->paginate($this->workflowViewModel);

        $isApprover = false;
        
        $userId = $this->Auth->user('id');
        $aro = array('model'=>'User', 'foreign_key' => $userId);
        $isApprover = $this->Acl->check($aro,
            $this->ConfigService->get_acl_aco($this->pluginName, 'approve'),
            $this->ConfigService->get_acl_operation($this->pluginName, 'approve')); 
      
        // flag for review mode
        $enableReview = false;
        $user_aro = 'User.'.$userId;
        $owner_aro = $this->_get_subject_owner_aro();
        if( !empty($this->ConfigService->get_post_action_name($this->pluginName, $owner_aro, 'approve')) ){
            $enableReview = true;
        }

        // flag for assigned-to-buddy
        $enableAssignedToBuddy = false;
        if( //!empty($this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromLower')) ||
            !empty($this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromUpper')) ){
          
            if (sizeof($this->_get_me_as_manager_in_approved_as_group($user_aro))>0){
                $enableAssignedToBuddy = true;
            }
            /* 
            $approval_op_allowed_groups = $this->ConfigService->get_approval_op_allowed_group($this->pluginName);
            foreach ($approval_op_allowed_groups as $approval_op_allowed_group) {
                $approval_grp_members_aros = $this->ConfigService->get_group_members($this->pluginName, $approval_op_allowed_group);
                foreach( $approval_grp_members_aros as $member_aro ){
                    $this->log('approval_grp_member_aro', 'debug');
                    $this->log($member_aro, 'debug');
                    $this->log('buddy member_aro='.$member_aro, 'debug');
                    $member_explode = explode(".", $member_aro);
                    $member_id = $member_explode[1];
                    if( $member_id == $userId ){
                        $enableAssignedToBuddy = true;
                        break;
                    }
                }
                if ($enableAssignedToBuddy) break;
            }*/
        }

        // display Approved only if create permission is not given
        $reviewOnly = false;
        // Disable below as there is no 'reviewer' in Panasonic V2
        /*
        $aco = $this->pluginModelName; 
        if( !$this->Acl->check($aro, $aco, 'create') 
            && $this->Acl->check($aro, $aco, 'read') ){
            $reviewOnly = true;
        }
        */

        // FIXME
        // Code assumes 'user' for this restriction of logged in user
        // set readonly if the login user belongs to a 'user' layer
        $readOnly = false;
        $users = $this->ConfigService->get_acl_layer_user($this->pluginName, 'user');
        if (in_array($user_aro, $users)) {
            $readOnly = true;
        }

        // set postApprover flag if login user is set to a member of post_action_group
        $isPostApprover = false;
        $aro_post_action = 'User.'.$userId;
        $owner_aro = $this->_get_subject_owner_aro();
        $post_action_groups = $this->ConfigService->get_post_action_allowed_group($this->pluginName, $owner_aro, 'approve');
        // assume only one instance for post_approve group
        if( !empty($post_action_groups) > 0 
            && !empty($this->ConfigService->get_post_action_name($this->pluginName, $owner_aro, 'approve'))
            && in_array( $aro_post_action, $this->ConfigService->get_managers_in_groups($post_action_groups) ) ){
            $isPostApprover = true;
        }

        $this->set('isApprover', $isApprover );
        $this->set('attrDataArray', $attrs);
        $this->set('pluginName', $this->pluginName);
        $this->set('listModelName', $this->workflowViewModelName);
        $this->set('detail_id', 'subject_id');
        $this->set('enableReview', $enableReview);
        $this->set('enableAssignedToBuddy', $enableAssignedToBuddy);
        $this->set('reviewOnly', $reviewOnly);
        $this->set('readOnly', $readOnly);
        $this->set('isPostApprover', $isPostApprover);
        $this->set('absolute_url', $this->_getCurrentPluginURL());
    }

    private function _set_dialog_options() {
        // FIXME
        //  multi-option name hardcoded
        $state_options = array();
        foreach ($this->ConfigService->get_multi_options('SalesStatusCategory') as $o) {
            $state_options[$o] = $o;
        } 
        $this->set('state_options', $state_options);
    }

    protected function get_field_data($field, $default=NULL)
    {
        $parent_val = parent::get_field_data($field, $default);

        if ($field == 'id' ){
            if (!empty($parent_val)) {
                $this->subject_id = $parent_val;
                return $parent_val;
            }
            if ($this->subject_id != null){
                return $this->subject_id;
            }
        }

        return $parent_val;
    }

    // if $status='*' that means all without owner check
    private function setPortal(){
        $this->_set_dialog_options();
        $this->save_sessionid();
        $this->setList();
        $this->setBadgeCount();
        //$this->set('attrs', $this->header);
    }

    public function main_menu(){
        $this->redirect('/'.$this->pluginName.'/'.$this->pluginMainPage);
    }

    public function list_waiting_for_your_action() {
        $this->setPortal();
    }
    public function list_being_reviewed() {
        $this->setPortal();
    }
    public function list_approved() {
        $this->setPortal();
    }
    public function list_approved_all() {
        $this->setPortal();
    }
    public function list_approved_group() {
        $this->setPortal();
    }
    public function list_assigned_to_buddy() {
        $this->setPortal();
    }
    public function list_closed() {
        $this->setPortal();
    }
    public function list_all() {
        $this->setPortal();
    }

    private function get_state($id){
        $this->log('get_state, id='.$id, 'debug');
        $wf = $this->workflowModel->findBySubjectId($id);
        $this->log('get_state, wf', 'debug');
        $this->log($wf, 'debug');

        if( !empty($wf) ){
            $login_user = $this->Auth->user('username');
            // if current_user = login_user, state is open
            //if ($wf[$this->workflowModelName]['assignee'] == $login_user) {
            //    return Configure::read('State.open');
            //}
            return $wf[$this->workflowModelName]['state'];
        }

        return Configure::read('State.open');
    }

    private function _get_buddies_constraint($buddies)
    {
        $buddy_conds = array();
        foreach ($buddies as $buddy_username) {
            array_push(
                $buddy_conds,
                array(
                    'and' => array(
                        'WorkflowJoin.assignee LIKE' => $buddy_username,
                        'or' => array(
                            array('WorkflowJoin.action LIKE' => 'next'),
                            array('WorkflowJoin.action LIKE' => 'prev'),
                        ),
                        $this->workflowViewModelName.'.assignee' => $buddy_username,
                    ),
                    $this->workflowViewModelName.'.state' => Configure::read('State.in_progress')
                )
            );
        }

        //$this->log('_get_buddies_constraint', 'debug');
        //$this->log($buddy_conds, 'debug');
        return array('or' => $buddy_conds);
    }

    // return array( 'kind' => {manager, member},
    //               'username' => value );
    private function get_buddy_in_assigned_to_group(){
        $userId = $this->Auth->user('id');
        $user_aro = 'User.'.$userId;

        $me_as_manager_approval_group_ids = $this->_get_me_as_manager_in_approved_as_group($user_aro);
        if (sizeof($me_as_manager_approval_group_ids)==0) return NULL;

        /*
        // first of all, check if login user belongs to approval_op groups
        $user_in_approval_op = false;
        foreach( $me_as_manager_approval_group_ids $group_id ){
            if( in_array( $user_aro, $this->ConfigService->get_group_members($this->pluginName, $group_id)) ){
                $user_in_approval_op = true;
                break;
            }
        }
        if( !$user_in_approval_op ) return NULL;
        */
        // if login is a member and fromLower is enabled
        // return the manager
        // 
        // FIXME
        // *Needs review when this option will start to be used* 
        // Not used in Panasonic V2.0
        // only one manager is returned for a given user
        /*
        $this->log('get_buddy_in_assigned_to_group, managers_found= ', 'debug');
        $this->log($managers_found, 'debug');
        if( in_array($user_aro, $managers_found) &&
            $this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromLower') ){
            $mgr_explode = explode(".", $user_aro);
            $group_mgr_username = $this->User->findById($mgr_explode[1])['User']['username'];
            return array('kind'=>'manager',
                         'usernames'=>array($group_mgr_username));
        }
        */

        // if login is a manager and fromUpper is enabled
        // return the user
        // Multiple subordinates can be returned as 'usernames' data structure
        $this->log('get_buddy_in_assigned_to_group, login is a manager ', 'debug');
        $this->log('get_buddy_in_assigned_to_group, user='.$user_aro, 'debug');
        if( //in_array($user_aro, $managers_found) &&
            $this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromUpper') ){
            #FIXME assume approval_op_group has only one element 
            $buddies_id = array();
            foreach ($me_as_manager_approval_group_ids as $approval_op_group_id) {
                $members_found = $this->ConfigService->get_group_members($this->pluginName, $approval_op_group_id);
                if (!in_array($user_aro, $members_found)) continue;
                $member_aro = ( strcmp($members_found[0], $user_aro)==0 ) ? $members_found[1] : $members_found[0];
                $member_explode = explode(".", $member_aro);
                array_push($buddies_id, $member_explode[1]);
                // $this->log('member_explode = ', 'debug');
                // $this->log($member_explode, 'debug');
            }
            $member_usernames = array();
            foreach ($buddies_id as $buddy_id) {
                $member_username = $this->User->findById($buddy_id)['User']['username'];
                array_push($member_usernames, $member_username);
            }
            $this->log('get_buddy_in_assigned_to_group, member found as buddy, usernames=', 'debug');
            $this->log($member_usernames, 'debug');
            return array('kind'=>'member', 
                         'usernames'=>$member_usernames);
        }
   
        // otherwise return NULL 
        $this->log('get_buddy_in_assigned_to_group, returning null', 'debug');
        return  NULL;
    }
   
    // FIXME
    // This code is dependent on Panasonic V2 customer care portal
    // should be removed if no one uses this UI
    private function _get_workflowJoin_username_in_login_user_post_approve_group()
    {
        // find post_approve group of the login user and get manager username
        // find create_as group whose manager is the same as post_approve and get members
        // iterate members to generate query array

        $ret = array();
        $user_aro = 'User.'.$this->Auth->user('id');
        $this->log('_get_workflowJoin_username_in_login_user_post_approve_group, user_aro='.$user_aro, 'debug');
        $my_post_approve_gids = $this->ConfigService->get_post_action_group_ids(
            $this->pluginName, 
            $user_aro, 
            'approve'
        );
        /*
        $this->log('_get_workflowJoin_username_in_login_user_post_approve_group, post_approve_gids:', 'debug');
        $this->log($post_approve_gids, 'debug');

        $my_post_approve_gids = array();
        foreach ($post_approve_gids as $g) {
            $members = $this->ConfigService->get_group_members($this->pluginName, array($g));
            if (in_array($user_aro, $members)) {
                array_push($my_post_approve_gids, $g);
            }
        }
        */
        $this->log('_get_workflowJoin_username_in_login_user_post_approve_group, my_post_approve_gids:', 'debug');
        $this->log($my_post_approve_gids, 'debug');

        $create_as_managers = array();
        foreach ($my_post_approve_gids as $g) {
            $manager = $this->ConfigService->get_managers_in_groups(array($g))[0];
            array_push($create_as_managers, $manager);
        }
        $this->log('_get_workflowJoin_username_in_login_user_post_approve_group, create_as_managers:', 'debug');
        $this->log($create_as_managers, 'debug');

        $my_create_as_gids = array();
        $create_as_gids = $this->ConfigService->get_create_as_allowed_group($this->pluginName);
        foreach ($create_as_gids as $g) {
            $manager = $this->ConfigService->get_managers_in_groups(array($g))[0];
            if (in_array($manager, $create_as_managers) && !in_array($g, $my_create_as_gids)) {
                array_push($my_create_as_gids, $g);
            }
        }
        $this->log('_get_workflowJoin_username_in_login_user_post_approve_group, my_create_as_gids:', 'debug');
        $this->log($my_create_as_gids, 'debug');
      
        $ret = array(); 
        foreach ($this->ConfigService->get_group_members($this->pluginName, $my_create_as_gids) as $member) {
            $memb_explode = explode('.', $member);
            $memb_inst = $this->User->findById($memb_explode[1]);
            $this->log('memb_inst:', 'debug');
            $this->log($memb_inst, 'debug');
            $memb_username = $memb_inst['User']['username'];
            array_push($ret, 
                array(
                    'WorkflowJoin.username LIKE' => $memb_username
                )
            );
        }
        $this->log('_get_workflowJoin_username_in_login_user_post_approve_group, ret=', 'debug');
        $this->log($ret, 'debug');
        return $ret;
    }

    protected function get_condition($actionName){
        $login_username = $this->Auth->user('username');
        if( strcmp($actionName,'list_waiting_for_your_action')==0 ){
            return array(
                'recursive' => -1,
                'conditions' => array(
                    'and'=>array(
                        $this->workflowViewModelName.'.summary not LIKE' => 
                                    'approved',
                        $this->workflowViewModelName.'.assignee LIKE' => 
                                    $login_username,
                        'or' => array(
                            array($this->workflowViewModelName.'.state !=' => 
                                    Configure::read('State.closed')),
                            array($this->workflowViewModelName.'.review_flag' => 0)
                        )
                    ),
                ),
                'order' => $this->get_order($this->workflowViewModelName),
            );
        }else if( strcmp($actionName,'list_being_reviewed')==0 ){
            // pick up cases when the login user was assgined at least once or took action
            $owner_array = array(
                        'WorkflowJoin.username LIKE' => $login_username,
                        'WorkflowJoin.assignee LIKE' => $login_username,
            );
            // add subordinates when the login user is a manager of create_as 
            $subordinates = array();
            foreach( $this->BriodeAcl->get_create_as_members_for_login_user($this->pluginName, $list_if_manager=true) as $uname ){
                array_push( $subordinates, array(
                        $this->workflowViewModelName.'.creator_id' => $uname ) );
            }
            $owner_array['or'] = $subordinates;
            return array(
                'joins' => array(
                    array(
                        'table' => 'workflow_event_logs',
                        'alias' => 'WorkflowJoin',
                        'type' => 'INNER',
                        'conditions' => array(
                            'WorkflowJoin.subject_id = '.$this->workflowViewModelName.'.subject_id'
                        )
                    )
                ),
                'conditions' => array(
                    'or' => $owner_array,
                    'and' => array(
                        $this->workflowViewModelName.'.assignee NOT LIKE' => 
                                    $login_username,
                        'or' => array(
                            //array('WorkflowJoin.assignee LIKE' => $this->Auth->user('username')),
                            array('WorkflowJoin.action LIKE' => 'next'),
                            array('WorkflowJoin.action LIKE' => 'prev'),
                        ),    
                    ),
                    $this->workflowViewModelName.'.state' => Configure::read('State.in_progress'),
                ),
                'order' => $this->get_order($this->workflowViewModelName),
                'group' => $this->workflowViewModelName.'.id'
            );
        }else if( strcmp($actionName, 'list_approved')==0 ){
            $owner_array = array(
                        'WorkflowJoin.username LIKE' => $login_username,
            );
            // if current login is manager, list all created by group members
            // otherwise only ones created by login
            $subordinates = array();
            if( $this->ConfigService->is_create_as_enabled($this->pluginName) ){
                $userId = $this->Auth->user('id');
                $user_aro = 'User.'.$userId;
                $create_as_groups = $this->ConfigService->get_create_as_allowed_group($this->pluginName);
                if( in_array($user_aro, $this->ConfigService->get_managers_in_groups($create_as_groups)) ){
                    foreach( $this->BriodeAcl->get_create_as_members_for_login_user($this->pluginName) as $uname ){
                        array_push( $subordinates, array(
                                $this->workflowViewModelName.'.creator_id' => $uname ) );
                    }
                    $owner_array['or'] = $subordinates;
                }
            }
            return array(
                'recursive' => -1,
                'joins' => array(
                    array(
                        'table' => 'workflow_event_logs',
                        'alias' => 'WorkflowJoin',
                        'type' => 'INNER',
                        'conditions' => array(
                            'WorkflowJoin.subject_id = '.$this->workflowViewModelName.'.subject_id'
                        )
                    )
                ),
                'conditions' => array(
                    'or' => $owner_array,
                    'and' => array(
                        //'WorkflowJoin.username LIKE' => $this->Auth->user('username'),
                        'or' => array(
                            array('WorkflowJoin.action LIKE' => 'approve'),
                        ),    
                    ),
                    $this->workflowViewModelName.'.state' => Configure::read('State.closed'),
                ),
                'order' => $this->get_order($this->workflowViewModelName),
                'group' => $this->workflowViewModelName.'.id'
            );
        }else if( strcmp($actionName, 'list_approved_all')==0 ){
            return array(
                'recursive' => -1,
                'joins' => array(
                    array(
                        'table' => 'workflow_event_logs',
                        'alias' => 'WorkflowJoin',
                        'type' => 'INNER',
                        'conditions' => array(
                            'WorkflowJoin.subject_id = '.$this->workflowViewModelName.'.subject_id'
                        )
                    )
                ),
                'conditions' => array(
                    'and' => array(
                        //'WorkflowJoin.username LIKE' => $this->Auth->user('username'),
                        'or' => array(
                            array('WorkflowJoin.action LIKE' => 'approve'),
                        ),    
                    ),
                    $this->workflowViewModelName.'.state' => Configure::read('State.closed'),
                ),
                'order' => $this->get_order($this->workflowViewModelName),
                'group' => $this->workflowViewModelName.'.id'
            );
        }else if( strcmp($actionName, 'list_approved_group')==0 ){
            return array(
                'joins' => array(
                    array(
                        'table' => 'workflow_event_logs',
                        'alias' => 'WorkflowJoin',
                        'type' => 'INNER',
                        'conditions' => array(
                            'WorkflowJoin.subject_id = '.$this->workflowViewModelName.'.subject_id'
                        )
                    )
                ),
                'conditions' => array(
                    'and' => array(
                        'or' => $this->_get_workflowJoin_username_in_login_user_post_approve_group(),
                        /*
                        array(
                            'WorkflowJoin.username LIKE' => 'jbergen',
                            'WorkflowJoin.username LIKE' => 'dcassanelli',
                        ),
                        */
                        'or' => array(
                            array('WorkflowJoin.action LIKE' => 'approve'),
                        ),    
                    ),
                    $this->workflowViewModelName.'.state' => Configure::read('State.closed'),
                ),
                'order' => $this->get_order($this->workflowViewModelName),
                'group' => $this->workflowViewModelName.'.id'
            );
        }else if( strcmp($actionName, 'list_assigned_to_buddy')==0 ){
            // condition should be evaluated only when approval_op is enabled
            // just return the other in the same group
            $buddy_usernames = $this->get_buddy_in_assigned_to_group()['usernames'];
            return array(
                'joins' => array(
                    array(
                        'table' => 'workflow_event_logs',
                        'alias' => 'WorkflowJoin',
                        'type' => 'INNER',
                        'conditions' => array(
                            'WorkflowJoin.subject_id = '.$this->workflowViewModelName.'.subject_id'
                        )
                    )
                ),
                'conditions' => $this->_get_buddies_constraint($buddy_usernames)
                /*
                array(
                    'and' => array(
                        'WorkflowJoin.assignee LIKE' => $buddy_username,
                        'or' => array(
                            array('WorkflowJoin.action LIKE' => 'next'),
                            array('WorkflowJoin.action LIKE' => 'prev'),
                        ),
                        $this->workflowViewModelName.'.assignee' => $buddy_username,
                    ),
                    $this->workflowViewModelName.'.state' => Configure::read('State.in_progress'),
                    //$this->workflowModelName.'.assignee' => $buddy_username,
                ) */
                ,
                'order' => $this->get_order($this->workflowViewModelName),
                'group' => $this->workflowViewModelName.'.id'
            );
        // includes all - no filter
        }else if( strcmp($actionName, 'list_closed')==0 ){
            return array(
                'joins' => array(
                    array(
                        'table' => 'workflow_event_logs',
                        'alias' => 'WorkflowJoin',
                        'type' => 'INNER',
                        'conditions' => array(
                            'WorkflowJoin.subject_id = '.$this->workflowViewModelName.'.subject_id'
                        )
                    )
                ),
                'conditions' => array(
                    'and' => array(
                        'WorkflowJoin.username LIKE' => $login_username,
                        'or' => array(
                            array('WorkflowJoin.action LIKE' => 'next'),
                            array('WorkflowJoin.action LIKE' => 'prev'),
                            array('WorkflowJoin.action LIKE' => 'approve'),
                            array('WorkflowJoin.action LIKE' => 'cancel'),
                        ),    
                    ),
                    $this->workflowViewModelName.'.state' => Configure::read('State.closed'),
                ),
                'order' => $this->get_order($this->workflowViewModelName),
                'group' => $this->workflowViewModelName.'.id'
            );
        }

        // includes all - no filter
        return array(
            'recursive' => -1,
            'conditions' => NULL,
            'order' => $this->get_order($this->workflowViewModelName),
        );
    }
    private function setBadgeCount(){
        $counts = array();
        $categories = array(
                    'list_waiting_for_your_action'=>'Waiting',
                    'list_being_reviewed'=>'In Progress',
                    'list_approved'=>'Approved',
                    'list_approved_all'=>'All Approved',
                    'list_closed'=>'Closed',
                    'list_all'=>'All',
        );
        $categories_buddy = array(
                    'list_assigned_to_buddy'=>'Administration',
        );

        $user_aro = 'User.'.$this->Auth->user('id');
        if( //!empty($this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromLower')) ||
            sizeof($this->_get_me_as_manager_in_approved_as_group($user_aro))>0 &&
            !empty($this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromUpper')) ){
            $categories = array_merge( $categories, $categories_buddy );
        }
        foreach( $categories as $category=>$label ){
            $counts[$label] = $this->workflowViewModel->find('count', $this->get_condition($category));
        }

        $this->set('countsPerState', $counts);
    }

    private function get_manager_for_create_as(){
        $create_as_group_ids = $this->ConfigService->get_create_as_allowed_group($this->pluginName);
        if( !empty($create_as_group_ids) ){
            $managers_found = $this->ConfigService->get_managers_in_groups($create_as_group_ids);
            $mgr_explode = explode(".", $managers_found[0]);
            return $this->User->findById($mgr_explode[1])['User']['username'];
        }
        return NULL;
    }

    private function is_manager_in_create_as($manager_username){
        $create_as_group_ids = $this->ConfigService->get_create_as_allowed_group($this->pluginName);
        if( empty($create_as_group_ids) ) return false;

        foreach( $create_as_group_ids as $group_id ){
            $managers_found = $this->ConfigService->get_managers_in_groups(array($group_id));
            if( !empty($managers_found) ){
                $mgr_explode = explode(".", $managers_found[0]);
                if( strcmp($manager_username, 
                                $this->User->findById($mgr_explode[1])['User']['username'])==0 ){
                    return true;
                }
            }
        }
        return false;
    }

    private function get_users_for_create_as_by_manager($manager_username){
        $members = array();
        $create_as_group_ids = $this->ConfigService->get_create_as_allowed_group($this->pluginName);
        if( empty($create_as_group_ids) ) return $members;
        
        foreach( $create_as_group_ids as $group_id ){
            $managers_found = $this->ConfigService->get_managers_in_groups(array($group_id));
            $userId = $this->Auth->user('id');
            $mgr_aro = 'User.'.$userId;
            $mgr_explode = explode(".", $managers_found[0]);
            if( strcmp( $this->User->findById($mgr_explode[1])['User']['username'],
                $manager_username)!=0 ){
                continue;
            }
            $group_members_aros = $this->ConfigService->get_group_members($this->pluginName, $group_id);
            $members = array();
            foreach( $group_members_aros as $member_aro ){
                if( strcmp($mgr_aro, $member_aro)==0 ){
                    continue;
                }
                $member_explode = explode(".", $member_aro);
                $member_username = $this->User->findById($member_explode[1])['User']['username'];
                array_push( $members, $member_username);
            }
            break;
        }
        return $members;
    }

    /* 
     * Return creator_id from DB when hidden id is set(updating)
     * otherwise return current user
     */
    private function _get_subject_owner_aro()
    {
        $this->log('_get_subject_owner_aro', 'debug');
        //$this->log($this->data, 'debug');
        //$this->log($_POST, 'debug');
        //$this->log($this->request, 'debug');
        $subject_id = $this->_load_subject_id();
        if (empty($subject_id)) {
            $owner_aro = 'User.'.$this->Auth->user('id');
            $this->log('_get_subject_owner_aro(1), aro='.$owner_aro, 'debug');
            return $owner_aro;
        }
        $wf_inst = $this->workflowModel->findBySubjectId($subject_id);
        $owner_aro = null;
        $this->log('subject_id='.$subject_id, 'debug');
        // var_dump($subject_id);
        if (!empty($wf_inst[$this->workflowModelName])) {
            $owner = $wf_inst[$this->workflowModelName]['creator_id'];
            $owner_inst = $this->User->findByUsername($owner);
            $owner_aro = 'User.'.$owner_inst['User']['id'];
        }
        
        $this->log('_get_subject_owner_aro(2), aro='.$owner_aro, 'debug');
        return $owner_aro;
    }

    private function setWorkflow(){
        $username = $this->Auth->user('username');
        $usertype = $this->Auth->user('usertype');
        $id     = $this->get_field_data('id');
        $status = $this->get_state($id);

        $this->log('setWorkflow, id,status='.$id.','.$status, 'debug');

        // FIXME use matric for complete action/role mapping
        $allowApproveCancel = false;
        $allowNextPrev = false;
        $enableViewAttachment = false;
        $enablePostApprove = false;
        $showApprovalOpAs = false;
        $userId = $this->Auth->user('id');
        $user_aro = 'User.'.$userId;
        $aro = array('model'=>'User', 'foreign_key' => $userId);

        $options = array();
        /*  this is now done in AppController::replaceFormatString
        // data security check
        foreach( $this->readProhibited as $aco=>$columns ){
            if( !$this->Acl->check($aro, $aco, 'update') ){
                $options['columnsToHide'] = $columns;
            }
        }
        */
        // FIXME review below
        // commments to NOT hide 
         $options['comments'] = $this->Workflow->get_comments(
                                                $id, $this->commentModelName);
        if( $this->Acl->check($aro, 
               $this->ConfigService->get_acl_aco($this->pluginName, 'approve'),
               $this->ConfigService->get_acl_operation($this->pluginName, 'approve'))){ 
            //$options['comments'] = $this->Workflow->get_comments(
            //                                    $id, $this->commentModelName);
            
            if( strcmp($this->action,'read')!=0 ){
                $allowApproveCancel = true;
            }
        }
        // FIXME: 
        // permission may be given too widely including admin
        // check post approval action is allowed
        $owner_aro = $this->_get_subject_owner_aro();
        $post_approval_action = $this->ConfigService->get_post_action_name($this->pluginName, $owner_aro, 'approve');
        if( !empty($post_approval_action) /*&& $this->Acl->check($aro, 
               $this->ConfigService->get_acl_aco($this->pluginName, $post_approval_action),
               $this->ConfigService->get_acl_operation($this->pluginName, $post_approval_action))*/ ){
            // only when login user is the manager of 'post_approver's group
            // show the post approval action button
            $groupsAllowedTo = $this->ConfigService->get_post_action_allowed_group($this->pluginName, $owner_aro, 'approve');
            // print_r('checking in_manager in group: '. $groupAllowedTo. '/'. $user_aro);
            if( !empty($groupsAllowedTo) 
                 && $this->ConfigService->in_manager_groups($user_aro, $groupsAllowedTo) ){
                $enablePostApprove = true;
                //print_r('enablePostApprove is TRUE');
            }
        }
        
        $showNext = false;
        $showPrev = false;
        $cond_approve = false;
        // if operation is read, not set next approvers
        if( strcmp($this->action,'read')!=0 ){
            $options['nextapprovers'] = $this->Workflow->get_forward($this->pluginName, $id, $username, $userId);
            if( //$status != Configure::read('State.closed') &&    //FIXME: This code is embedded in WorkflowComponent which is ugly!
                isset($options['nextapprovers']) && 
                count($options['nextapprovers']) >0 ) $showNext = true;
           
            // if the nth of the latest log is 0, do not show prev
            $last_workflow_step = $this->get_last_workflow_step($id);
            if( $last_workflow_step != 0 ) $showPrev = true;

            $cond_approve = $this->Workflow->user_in_aro_level($this->pluginName, $user_aro, 'controller') || $this->Workflow->user_in_aro_level($this->pluginName, $user_aro, 'admin');
            // conditional approve and approve/reject are exclusive
            //if ($cond_approve) $allowApproveCancel = false;
        }
        $this->log('cond_approve id,state,val='.$id.','.$status.','.$cond_approve, 'debug');

        // placefolder for:
        // - checking existence of all mandatory fields
        // - checking all validated fields do not contain errors
        // the default value is false and these values are overwritten in view
        $options['mandatory_flag'] = false;
        $options['validation_flag'] = false;

        if( strcmp($this->action,'create')!=0 ){
            if( $this->is_exist_attachment($id) ){
                $options['formid_for_download'] = $id;
                $enableViewAttachment = true;
            }
        }
       
        // if approve-as is enabled and the assignee is the buddy of logged in 
        // user, enable approve-as options 
        $form_inst = $this->workflowModel->findBySubjectId($id);
        if( //!empty($this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromLower')) ||
            !empty($this->ConfigService->is_approval_op_enabled($this->pluginName, 'fromUpper')) ){
            // TODO
            // Review required for buddy handling
            $buddy_usernames = $this->get_buddy_in_assigned_to_group()['usernames'];
            if( !empty($form_inst) &&
                sizeof($buddy_usernames) > 0 && 
                in_array( $form_inst[$this->workflowModelName]['assignee'], $buddy_usernames) ){
                $showApprovalOpAs = array('prev_as');
                $this->log('setWorkflow buddies=', 'debug');
                $this->log($buddy_usernames, 'debug');
                // check if manager of the buddy exists
                foreach ($buddy_usernames as $buddy_username) {
                    $mgr_instance = $this->User->findByUsername($buddy_username);
                    $sr_manager_name = $mgr_instance['User']['manager'];
                    $sr_manager_instance = $this->User->findByName($sr_manager_name);
                    if( !empty($sr_manager_instance) ){
                        array_push( $showApprovalOpAs, 'next_as' );
                        break;
                    }
                }
            }
        }

        $enableSingleExcelUpDown = false;
        if( $this->ConfigService->is_workflow_single_excel_enabled($this->pluginName) ){
            $enableSingleExcelUpDown = true;
        }

        // read-only/controller user has a permission to export excel
        // and so dows owners and people in the same group
        $enableSingleExcelDownOnly = false;
        if( isset($form_inst[$this->workflowModelName]) ){
            $creator_username = $form_inst[$this->workflowModelName]['creator_id'];
            $users_authorized = $this->get_postaction_group_notification_members(
                    'approve', $creator_username);
            if( $this->BriodeAcl->is_readonly_user($aro, $this->pluginName) ||
                in_array($username, $this->BriodeAcl->get_users_in_layer($this->pluginName, 'controller')) ||
                in_array($user_aro, $users_authorized) ){
                $enableSingleExcelDownOnly = true;
            }
        }

        $showUndo = false;
        // show undo if the case selected is created by login user
        if( isset($form_inst[$this->workflowModelName]) && 
            strcmp($form_inst[$this->workflowModelName]['creator_id'], $username)==0 ){
            $showUndo = true;
        }

        // set allowEditForCreateAsManager flag if login user is a manager of create_as
        // and the opened form is not created by self
        // and the form is currently assigned to his subordinate
        $allowEditForCreateAsManager = false;
        $create_as_groups = $this->ConfigService->get_create_as_allowed_group($this->pluginName);
        $aro_create_as_mgr = 'User.'.$userId;
        if( !empty($form_inst) && !empty($create_as_groups)
            && in_array($aro_create_as_mgr, $this->ConfigService->get_managers_in_groups($create_as_groups)) ){
            $group_members = array();
            foreach( $create_as_groups as $g ){
                $group_members = array_merge($group_members, 
                                    $this->ConfigService->get_group_members($this->pluginName, $g) );
            }
            $form_assignee_username = $form_inst[$this->workflowModelName]['assignee'];
            $form_assignee_instance = $this->User->findByUsername($form_assignee_username);
            if( !empty($form_assignee_instance) ){
                //$this->log('allowEditForCreateAsManager, form assignee inst not null', 'debug');
                $form_assignee_id = $form_assignee_instance['User']['id'];
                $form_assignee_aro = 'User.'.$form_assignee_id;
                if( //strcmp( $form_inst[$this->workflowModelName]['creator_id'], $username)!=0 &&
                    in_array( $form_assignee_aro, $group_members ) ){
                    $allowEditForCreateAsManager = true;
                }
            }
        }

        $assignee = NULL;
        if( !empty($form_inst) ){
            $assignee = ( strcmp( $form_inst[$this->workflowModelName]['assignee'],
                           $username )==0 );
        }

        $actions = $this->Workflow->get_actions(
                                        $this->pluginName, 
                                        $this->action,
                                        $this->pluginModelName,
                                        $this->_get_subject_owner_aro(),
                                        $id,
                                        false,  // enableViewAttchment
                                        $assignee,
                                        $status, 
                                        $usertype, 
                                        $this->is_assignee($id, $username),
                                        $showNext, 
                                        $showPrev, 
                                        $allowApproveCancel, 
                                        $enableViewAttachment,
                                        $enablePostApprove,
                                        $showApprovalOpAs,
                                        $enableSingleExcelUpDown,
                                        $enableSingleExcelDownOnly,
                                        $showUndo,
                                        $allowEditForCreateAsManager,
                                        $cond_approve);
        $this->set('actions', $actions);
        if( in_array('export_to_excel', $actions) ){
            $options['formid_for_download'] = $id;
        }
        // if action is create and the login user is a manager of create_as group
        // set list of members to 'create_as_users' attribute
        if( strcmp($this->action,'create')==0 &&
            $this->ConfigService->is_create_as_enabled($this->pluginName) &&
            $this->is_manager_in_create_as($username) ){
            $options['create_as_users'] = $this->get_users_for_create_as_by_manager($username);
            $options['create_as_manager'] = $username;
        }

        $this->set('wf_options', $options );
        $this->log('wf_options=', 'debug');
        $this->log($options, 'debug');
        $this->Session->write('PreviousURL', $this->redirectUrlToMenu);
    }

    private function get_assignee($subject_id, $next_workflow_step, $loginuser){
        //$this->log('get_assignee, id,next,login='.$subject_id.','.$next_workflow_step.','.$loginuser, 'debug');
        $this->log('get_assignee, action='.$this->action, 'debug');
        // at save, assignee is self
        if( strcmp($this->action, 'save')==0 ||
            strcmp($this->action, 'upload_single_data')==0 ){
            return $this->Auth->user('username');
        }
        // if reject, assignee is null
        if( strcmp($this->action, 'reject')==0 ){
            return '';
        }
        // if approve, assignee is the manager of post_action group
        // otherwise null
        // Panasonic V2
        //  at approve, assignee is always set to fixed user taken from config
        $approve_actions = array('approve', 'approve_as');
        if( in_array($this->action, $approve_actions) ){
            return $this->ConfigService->get_workflow_assignee_at_approve($this->pluginName);
            /*
            $userId = $this->Auth->user('id');
            $user_aro = 'User.'.$userId;
            $owner_aro = $this->_get_subject_owner_aro();
            $post_action_groups = $this->ConfigService->get_post_action_allowed_group($this->pluginName, $owner_aro, 'approve');
            if( !empty($post_action_groups) ){
                $this->log('approve group found:', 'debug');
                $this->log($post_action_groups, 'debug');
                // return the manager of post action group
                //$id     = $this->get_field_data('id');
                //$user_aro = 'User.'.$id;
                /// FIXME assume there is only one instanc
                $managers_found = $this->ConfigService->get_managers_in_groups($post_action_groups);
                $this->log('managers found:', 'debug');
                $this->log($managers_found, 'debug');

                if( sizeof($managers_found) > 0 ){
                    // convert aro into username
                    $user_id = explode(".", $managers_found[0]);
                    $username = $this->User->findById($user_id[1])['User']['username'];
                    return $username;
                }
            }
            return '';    
            */
        }
        if( strcmp($this->action, 'prev')==0 ){
            // for user, layer should be current
            return $this->Workflow->get_past_user_at($subject_id, $next_workflow_step+1, $loginuser);
        }
        return $this->get_field_data('assignee');
    }

    private function get_last_workflow_step($subject_id){
        $last_workflow_step = 0;
        $last_wf_activity = $this->workflowModel->find(
                                'first', array(
                                'recursive' => -1,
                                'conditions' => array('subject_id'=> $subject_id),
                                'order' => array('updated_at' => 'DESC') ));
        //$this->log('get_last_workflow_step subject_id:'.$subject_id,'debug');
        //$this->log('get_last_workflow_step found:','debug');
        //$this->log($last_wf_activity,'debug');
        if( !empty($last_wf_activity) ){
            //$this->log('last_workflow found!!','debug');
            $last_workflow_step = $last_wf_activity[$this->workflowModelName]['nth'];
        }
        //$this->log('last_workflow_step='.$last_workflow_step,'debug');
        return $last_workflow_step;
    }

    private function is_assignee($subject_id, $username){
        $workflow_found = $this->workflowModel->find(
                                'first', array(
                                'recursive' => -1,
                                'conditions' => array('subject_id'=> $subject_id),
                                'order' => array('updated_at' => 'DESC') ));

        if( empty($workflow_found) ) return false;
        if( strcmp($workflow_found[$this->workflowModelName]['assignee'],$username)==0 )
            return true;

        return false;
    }

    private function save_comment($subject_id, $summary, $timestamp=NULL){
        $e = new Exception();
        $this->log($e->getTraceAsString(), 'debug');
        $username = $this->Auth->user('username');
        $timestamp = (!empty($timestamp)) ? $timestamp : date('Y-m-d H:i:s', time());

        if( array_key_exists('addcomment', $this->data) ){
            $commentAttr = array();
            $commentAttr[$this->commentModelName]['subject_id'] = $subject_id;
            $commentAttr[$this->commentModelName]['created_at'] = $timestamp;
            $commentAttr[$this->commentModelName]['creator_id'] = $username;
            $commentAttr[$this->commentModelName]['comment'] = $summary . ':'. $this->get_field_data('addcomment');
            $this->commentModel->save($commentAttr);
        }
    }

    /**
     * Take timestamp for the adding comment from the most recent 
     * attribute_event_log table where subject_id matches
     */
    private function get_timestamp_from_attr_log($subject_id)
    {
        $attrlog = $this->AttributeEventLog->find(
            'first', 
            array(
                'conditions'=>array(
                    'subject_id'=>$subject_id
                ), 
                'order' => array(
                    'id'=> 'DESC'
                )
            )
        );
             
        if( !empty($attrlog) ){
            $utime = $attrlog['AttributeEventLog']['update_time'];
            //$this->log('get_timestamp_from_attr_log, returning utime found in log', 'debug');
            //$this->log($utime, 'debug');
            return $utime;
        }
        return date('Y-m-d H:i:s', time());
    }

    private function executeWfTrigger($subject_id, $beforeOrAfter, $action){
        if( strcmp($action, 'approve')==0 ){
            // pass subject_id all the time
            $this->exec_in_vendorpath('RunTrigger', $this->pluginName, $subject_id, $beforeOrAfter, $action);
            return $action;
        }
    }

    private function get_postaction_group_users($wf_action){
        $userId = $this->Auth->user('id');
        $user_aro = 'User.'.$userId;
        $owner_aro = $this->_get_subject_owner_aro();
        $grp_name = $this->ConfigService->get_post_action_allowed_group(
                                            $this->pluginName, $owner_aro, $wf_action);
        if( empty($grp_name) ) return array();

        $grp_name = $grp_name[0];
        $this->log('grp_name at approve='.$grp_name, 'debug');
        if( !empty($grp_name) ){
            $this->log('grp_name is not empty'.$grp_name, 'debug');
            // get all members of the group and iterate over
            return $this->ConfigService->get_group_members($this->pluginName, $grp_name);
        }
        return array();
    }

    // returns salesmanager-subordinate relationship, nothing more
    //  e.g. get all members User.89/92/94/99
    //       return  mgr-subordinate User.89/92
    private function get_postaction_group_notification_members($action, $username){
        // check if group notification is enabled
        $userId = $this->Auth->user('id');
        $user_aro = 'User.'.$userId;
        $owner_aro = $this->_get_subject_owner_aro();
        if( !$this->ConfigService->is_post_action_group_notification_enabled($this->pluginName, $owner_aro, $action) ){
            return array();
        }

        // if enabled, get a list of notification groups
        // iterate thru groups and check if the member is in the group
        $membs_to_notify = array();
        $user_inst = $this->User->findByUsername($username);
        $user_aro = 'User.'.$user_inst['User']['id'];
        foreach( $this->ConfigService->get_post_action_notification_group_ids($this->pluginName, $owner_aro, $action) as $grp_id ){
            $membs = $this->ConfigService->get_group_members($this->pluginName, $grp_id);
            $this->log('get_postaction_group_notification_members: useraro,membs:', 'debug');
            $this->log($user_aro, 'debug');
            $this->log($membs, 'debug');
            // if the member is in the group, send emails to all members
            if( in_array($user_aro, $membs) ){
                $mgr_aro = $this->ConfigService->get_managers_in_groups(array($grp_id))[0];
                if( strcmp($mgr_aro, $user_aro)!=0 ){
                    array_push( $membs_to_notify, $mgr_aro );
                }
                array_push( $membs_to_notify, $user_aro );
                // FIXME
                // assume member belongs to one group
                break;
            }
        }
        $this->log('get_postaction_group_notification_members: returning:', 'debug');
        $this->log($membs_to_notify, 'debug');

        return $membs_to_notify;
    }

    private function _aros_to_usernames($aros)
    {
        $this->log('_aros_to_usernames, aro=', 'debug');
        $this->log($aros, 'debug');
        $usernames = array();
        foreach ($aros as $a) {
            $user_id = explode(".", $a);
            $username = $this->User->findById($user_id[1])['User']['username'];
            array_push($usernames, $username);
        }
        return $usernames;
    }

    private function post_action_email($wf_pre, $wf){
        $action     = $wf[$this->workflowModelName]['action'];

        // ignore when saving(=create as actionname) for your own sake
        $this->log('post_action_email action='.$action, 'debug');
        if( count($wf_pre)!=0 
            && strcmp($action, 'create')==0 ){
            $this->log('post_action_email ignored', 'debug');
            return;
        }

        $subject_id = $wf[$this->workflowModelName]['subject_id'];
        $assignee   = $wf[$this->workflowModelName]['assignee'];
        $creator_username = $wf[$this->workflowModelName]['creator_id'];

        $email_data = $this->setWFEmail($wf);

        $username = $this->Auth->user('username');
        $userId = $this->Auth->user('id');
        $user_aro = 'User.'.$userId;
        // send email when post_approve action is defined
        // otherwise, all workflow progress is notified except upload_excel
        if( strcmp($action, 'approve')==0 ){
            // FIXME assume there is only one instance for post action
            // get all members of the group and iterate over
            $recipients = $this->get_postaction_group_users('approve');
            // add notification group if any
            $recipients = array_merge( $recipients, array($user_aro), 
                $this->get_postaction_group_notification_members($action, $creator_username));

            $usernames = $this->_aros_to_usernames($recipients);
            $usernames = array_merge($usernames, array($creator_username));
            $this->log('sending email to :', 'debug');
            $this->log($usernames, 'debug');

            // post approve script 
            $this->executeWfTrigger($subject_id, 'after', $action);

            //$this->sendEmail($usernames, 'issueApproved', $email_data);
            $recipient_emails = array_merge(
                $this->_usernames_to_emails($usernames),
                $this->ConfigService->get_post_action_default_notifier(
                    $this->pluginName,
                    $action
                )
            );
            $excel_out_params = $this->export_to_excel_ex($subject_id, $readonly=true);
            $attachment = $excel_out_params['fullpath'];
            $this->EmailService->send_approve_notification(
                array(
                    "contents" => $email_data,
                    "usernames" => $usernames,
                    "recipients" => $recipient_emails,
                    "attachment" => $attachment
                )
            );
        }
 
        if( strcmp($action, 'reject')==0 ){
            $usernames = array($creator_username);
            $this->log('sending email to :', 'debug');
            $this->log($usernames, 'debug');
            $this->sendEmail($usernames, 'issueRejected', $email_data);
            return;
        }

        // send email to read-only user when the case was created
        // also to the manager of an appliant
        if( in_array($action, array('next', 'next_as')) ){
            //$wf_id = $this->workflowModel->getLastInsertId();
            if( count($wf_pre) == 0 ){
                $post_approve_users = $this->get_postaction_group_users('approve');
                // readonly users
                $usernames = array();
                foreach( $post_approve_users as $user ){
                    $user_arr = split("\.", $user);
                    $user_id = $user_arr[1];
                    $aro = array('model'=>'User', 'foreign_key' => $user_id);
                    if( $this->BriodeAcl->is_readonly_user($aro, $this->pluginName) ){
                        $username = $this->User->findById($user_id)['User']['username'];
                        array_push($usernames, $username);
                    }
                }
                $this->log('sending email to readonly users:', 'debug');
                $this->log($usernames, 'debug');
                //$this->sendEmail($usernames, 'notifyWFCreation', $email_data);

                // manager for the applicant
                $creator_userid = $this->User->findByUsername($creator_username)['User']['id'];
                //$this->log('==>sending email to the applicant manager', 'debug');
                //$this->log('creator_userid='.$creator_userid, 'debug');
                $managers = $this->ConfigService->get_group_managers_for_user($this->pluginName, $creator_userid);
                //$this->log('managers=', 'debug');
                //$this->log($managers, 'debug');
                if( !empty($managers) ){
                    $mgr_usernames = $this->_aros_to_usernames($managers);
                    $this->log('sending email to manager=', 'debug');
                    $this->log($mgr_usernames, 'debug');
                    //$this->sendEmail($mgr_usernames, 'notifyWFCreation', $email_data);
                }
            }
        }

        // emails to assignee
        // FIXME: 
        // sendEmail is ignored at "Upload Excel"
        if (strcmp($action, 'upload_single_data')!=0) {
            // Panasonic V2
            // send actionRequired only at controller level
            $assignee_inst = $this->User->findByUsername($assignee);
            if (empty($assignee_inst)) return;

            $assignee_id = $assignee_inst['User']['id'];
            $assignee_aro = 'User.'.$assignee_id; 
            if ($this->Workflow->user_in_aro_level($this->pluginName, $assignee_aro, 'approver') &&
                strcmp($action, 'approve')!=0 &&
                strcmp($action, 'approve_as')!=0 ){
                $this->log('sendEmail actionRequired to :'.$assignee, 'debug');
                $this->sendEmail(array($assignee), 'actionRequired', $email_data);
            }
        }
    }

    // params_to_override=
    //    {  assignee => val,
    //       applicant => val,
    //       summary => val,
    //    };
    private function save_workflow($action, $subject_id, $summary_given, $params_to_override=NULL){
        //$this->log('save_workflow stacktrace', 'debug');
        //$e = new Exception();
        //$this->log($e->getTraceAsString(), 'debug');
        
        $username = $this->Auth->user('username');
        $timestamp = $this->get_timestamp_from_attr_log($subject_id); //date('Y-m-d H:i:s', time());
        $summary = $summary_given;

        //$wf_inst = $this->workflowModel->findBySubjectId($wf_id);
        $this->log('save_workflow, subject_id='.$subject_id, 'debug');
        $last_workflow_step = $this->get_last_workflow_step($subject_id);
        $next_workflow_step = $this->Workflow->get_next_workflow_step($last_workflow_step, $action);

        $this->log('save_workflow,last/next='.$last_workflow_step.'/'.$next_workflow_step, 'debug');

        $status = $this->get_state($subject_id);
        //$this->log('save_workflow, action='.$this->action, 'debug');
        $assignee = $this->get_assignee($subject_id, $next_workflow_step, $username);

        $nextstatus = $this->Workflow->get_next_status($status, $action, $this->action, $username, $assignee);

        $creator_id = $this->pluginModel->findById($subject_id)[$this->pluginModelName]['creator_id'];
        $updator_id = $username;
        if( !empty($params_to_override) ){
            $creator_id = ( isset($params_to_override['creator_id']) ) 
                    ? $params_to_override['creator_id'] 
                    : $creator_id;
            //$updator_id = ( isset($params_to_override['updator_id']) )
            //        ? $params_to_override['updator_id']
            //        : $username;
            $summary = ( isset($params_to_override['summary']) )
                    ? $params_to_override['summary']
                    : $summary;
            $assignee = ( isset($params_to_override['assignee']) )
                    ? $params_to_override['assignee']
                    : $assignee;
            $nextstatus = ( isset($params_to_override['nextstatus']) )
                    ? $params_to_override['nextstatus']
                    : $nextstatus;
        }

        // save comment
        $this->save_comment($subject_id, $summary, $timestamp);

        // check if workflow exists
        $wf = $this->workflowModel->findBySubjectId($subject_id);
        // remove existing workflow if exists
        $prev_assignee = $creator_id;
        $case_state = '';
        $case_state_text = '';
        if( count($wf) != 0 ){
            /*
            if( strcmp($action,'next')==0 ){
                $wofkflow_step ++;
            }else if( strcmp($action,'prev')==0 ){
                $workflow_step --;
            }*/
            $prev_assignee = $wf[$this->workflowModelName]['assignee'];
            $case_state = $wf[$this->workflowModelName]['case_state'];
            $case_state_text = $wf[$this->workflowModelName]['case_state_text'];
            $this->workflowModel->delete($wf[$this->workflowModelName]['id']);
        }
        $workflowAttr = array();
        $workflowAttr[$this->workflowModelName]['subject_id'] = $subject_id;
        $workflowAttr[$this->workflowModelName]['created_at'] = $timestamp;
        $workflowAttr[$this->workflowModelName]['creator_id'] = $creator_id;
        $workflowAttr[$this->workflowModelName]['updator_id'] = $updator_id;
        $workflowAttr[$this->workflowModelName]['state'] = $nextstatus;
        $workflowAttr[$this->workflowModelName]['prev_state'] = $status;
        $workflowAttr[$this->workflowModelName]['summary'] = $summary;
        $workflowAttr[$this->workflowModelName]['assignee'] = $assignee;
        $workflowAttr[$this->workflowModelName]['prev_assignee'] = $prev_assignee;
        $workflowAttr[$this->workflowModelName]['action'] = $action;
        $workflowAttr[$this->workflowModelName]['nth'] = $next_workflow_step;
        $workflowAttr[$this->workflowModelName]['validation_flag'] = 
                                        $this->get_field_data('validation_flag', NULL);
        $workflowAttr[$this->workflowModelName]['mandatory_flag'] = 
                                        $this->get_field_data('mandatory_flag', NULL);
        $workflowAttr[$this->workflowModelName]['review_flag'] = 0; //false
        $workflowAttr[$this->workflowModelName]['case_state'] = $case_state;
        $workflowAttr[$this->workflowModelName]['case_state_text'] = $case_state_text;
        $this->workflowModel->save($workflowAttr);

        //$wf_id = $this->workflowModel->getLastInsertId();
        $this->post_action_email($wf, $workflowAttr);
    }

    private function update_review_status($subject_id, $summary){
        $username = $this->Auth->user('username');
        $timestamp = date('Y-m-d H:i:s', time());
        // save comment
        $this->save_comment($subject_id, $summary, $timestamp);

        // remove existing workflow if exists
        $wf = $this->workflowModel->findBySubjectId($subject_id);
        if( count($wf) != 0 ){
            $this->workflowModel->delete($wf[$this->workflowModelName]['id']);
        }
        $wf[$this->workflowModelName]['created_at'] = $timestamp;
        $wf[$this->workflowModelName]['review_flag'] = 1; // true;
        $wf[$this->workflowModelName]['assignee'] = ''; // assigned to nobody
        $wf[$this->workflowModelName]['action'] = 'review'; 
        $this->workflowModel->save($wf);
    }

    private function override_workflow_params($override_kind,$subject_id=NULL){
        //$this->log('override_workflow_params, $subject_id='.$subject_id, 'debug');
        $login_user = $this->Auth->user('username');

        if( strcmp($override_kind, 'cond_approve_failed')==0 ){ 
            return array('nextstatus' => Configure::read('State.in_progress'));
        }
        if( strcmp($override_kind, 'cond_approve_ok')==0 ){
            return array(
                'nextstatus' => Configure::read('State.closed'),
                'assignee' => $this->ConfigService->get_workflow_assignee_at_approve($this->pluginName)
            );
        }


        if( strcmp($override_kind, 'create_as')==0 ){
            $create_as_user = $this->get_field_data('create_as_user', NULL);
            if( !empty($create_as_user) && 
                strcmp($create_as_user, $login_user)!=0 ){
                return array('creator_id' => $create_as_user, 
                             //'applicant' => $create_as_user, 
                             #'assignee'  => $login_user,
                             'summary'   => $login_user.' '.$override_kind.' '.$create_as_user);
            }
        }

        if( strcmp($override_kind, 'next_as')==0 ||
            strcmp($override_kind, 'approve_as')==0 ){
            // if login user is upper, set params as follows
            //    *THIS CASE IS HANDLED IN Panasonic V2*
            //    applicant = member
            //    assignee = self
            //    summary = $assignee . $override_kind . $applicant
            // else if login user is lower, set params as follows
            //    THIS CASE DOES NOT EXIST IN Panasonic V2
            //    applicant = manager
            //    assignee = manager of manager
            //    summary = same as above
            $buddies = $this->get_buddy_in_assigned_to_group();

            // FIXME
            // this overly assumes special condition - should be handled with better assumption
            // next_as as create_as manager
            //   applicant = creator of the form
            //   assignee = manager of creator
            //   summary = $loginuser $override_kind $applicant
            if( !empty($subject_id) && empty($buddies) ){
                $wf = $this->workflowModel->findBySubjectId($subject_id);
                $applicant_username = $wf[$this->workflowModelName]['creator_id'];
                $applicant_instance = $this->User->findByUsername($applicant_username);
                $mgr_instance = $this->User->findByName($applicant_instance['User']['manager']);
                $mgr_username = $mgr_instance['User']['username'];
                return array('creator_id' => $applicant_username,
                             'assignee'  => $mgr_username,
                             'summary'   => $login_user.' '.$override_kind.' '.$applicant_username);
            }

            //$buddy_usernames = $buddies['usernames'];
            // TODO
            // Review required
            // buddy_username must be current assignee of the case
            /*
            $wf = $this->workflowModel->findBySubjectId($subject_id);
            $this->log('override_workflow_params, wf instance=', 'debug');
            $this->log($wf, 'debug');
            $buddy_username = $wf[$this->workflowModelName]['assignee'];

            $this->log('override, login='.$login_user.',buddies=', 'debug');
            $this->log($buddy_usernames, 'debug');
            */
            if( strcmp($buddies['kind'], 'manager')!=0 ){
                $wf = $this->workflowModel->findBySubjectId($subject_id);
                $this->log('override_workflow_params, wf instance=', 'debug');
                $this->log($wf, 'debug');
                $buddy_username = '';
                if (isset($wf[$this->workflowModelName]['assignee'])) {
                    $buddy_username = $wf[$this->workflowModelName]['assignee'];
                }

                $this->log('override, login='.$login_user.',buddies=', 'debug');
                $this->log($buddy_usernames, 'debug');
                $this->log('override, buddy is not manager', 'debug');
                return array('creator_id' => $buddy_username,
                             'assignee'  => $login_user,
                             'summary'   => $login_user.' '.$override_kind.' '.$buddy_username);
            }
            /* Panasonic V2
            else{
                $this->log('override, buddy is manager', 'debug');
                $mgr_instance = $this->User->findByUsername($buddy_username);
                $sr_manager_name = $mgr_instance['User']['manager'];
                if (empty($sr_manager_name)) {
                    $this->Session->setFlash('Supervisor not found', 'flash_error');
                    $redirect_url = '/'.$this->pluginName.'/list_waiting_for_your_action';
                    $this->redirect($redirect_url);
                    return;
                }
                $sr_manager_instance = $this->User->findByName($sr_manager_name);
                $sr_manager_username = $sr_manager_instance['User']['username'];
                return array('creator_id' => $buddy_username,
                             'assignee'  => $sr_manager_username,
                             'summary'   => $sr_manager_username.' '.$override_kind.' '.$buddy_username);
            }
            */
        }

        if( strcmp($override_kind, 'prev_as')==0 ){
            // if login user is upper, set params as follows
            //    applicant = member
            //    assignee = prev
            //    summary = $assignee . $override_kind . $applicant
            // else if login user is lower, set params as follows
            //    THERE IS NO SUCH CASE FOR Panasonic V2
            //    applicant = manager
            //    assignee = self
            //    summary = same as above
            $buddies = $this->get_buddy_in_assigned_to_group();
            //$buddy_usernames = $buddies['usernames'];
            // TODO
            // Review required
            // buddy_username must be current assignee of the case
            $wf = $this->workflowModel->findBySubjectId($subject_id);
            $buddy_username = $wf[$this->workflowModelName]['assignee'];

            $this->log('override, buddy:login='.$buddy_username.':'.$login_user, 'debug');
            if( strcmp($buddies['kind'], 'manager')!=0 ){
                $this->log('override, buddy is not manager', 'debug');
                $last_wf_step = $this->get_last_workflow_step($subject_id);
                $this->log('override, prev_as, last_wf_step=', 'debug');
                $this->log($last_wf_step, 'debug');
                $prev_user = $this->Workflow->get_past_user_at($subject_id, $last_wf_step, $login_user); 
                $this->log('override, prev_user='.$prev_user, 'debug');
                return array('applicant' => $buddy_username,
                             'assignee'  => $prev_user,
                             'summary'   => $prev_user.' '.$override_kind.' '.$buddy_username);
            }
            /* Panasonic V2
            else{
                $this->log('override, buddy is manager', 'debug');
                $mgr_instance = $this->User->findByUsername($buddy_username);
                //$sr_manager_name = $mgr_instance['User']['manager'];
                //$sr_manager_instance = $this->User->findByName($sr_manager_name);
                //$sr_manager_username = $sr_manager_instance['User']['username'];
                return array('applicant' => $buddy_username,
                             'assignee'  => $login_user,
                             'summary'   => $login_user.' '.$override_kind.' '.$buddy_username);
            }
            */
        }

        // possible only if login user is the same as creator_id
        if( strcmp($override_kind, 'undo')==0 ){
            $field_id = $this->get_field_data('id', NULL);
            $wf_case = $this->workflowModel->findBySubjectId($field_id);
            if( !empty($wf_case) &&
                strcmp($wf_case[$this->workflowModelName]['creator_id'], $login_user)==0 ){
                return array('applicant' => $login_user,
                             'assignee'  => $login_user,
                             'summary'   => $login_user.' '.$override_kind);
            }
        }

        return NULL;
    }

    public function delete(){
        $id = $this->get_field_data('id', NULL);
        $wf = $this->workflowModel->findBySubjectId($id);
        $this->workflowModel->delete($wf[$this->workflowModelName]['id']);
        parent::delete();
    }

    public function read(){
        parent::read();
        $this->setWorkflow();
    }
    public function update($override=NULL){
        parent::update($override);
        $this->_save_subject_id();
        $this->setWorkflow();
    }
    public function update_as(){
        $this->update(array('action'=>'update'));
    }
    public function create(){
        parent::create();
        $this->_save_subject_id();
        $this->setWorkflow();
    }
    public function modify_after_close(){
        $subject_id = parent::create_check($forward=false);
        $this->save_comment($subject_id, 'modified after close');
        $this->forward_to_menu();
    }
    public function create_check($forward=false, $override=NULL){
        $subject_id = parent::create_check($forward);
        $this->save_workflow('create', $subject_id, 'created');
        $this->forward_to_menu();
    }
    public function upload_single_data(){
        $subject_id = parent::upload_single_data();
        //$this->setWorkflow();  // invoked in create() as part of op above
        $this->save_workflow('upload_single_data', $subject_id, 'upload_single_data');
        $this->forward_to_edit($subject_id, $_FILES['xlfile']['tmp_name'][0]);
        //return $subject_id; // make it polymorphic as base class
    }
    public function next($override_kind='create_as'){
        $wf_params = $this->override_workflow_params($override_kind);
        $subject_id = parent::create_check($forward=false, $wf_params);
        
        $this->save_workflow('next', $subject_id, 'in progress', $this->override_workflow_params($override_kind, $subject_id));
        $this->forward_to_menu();
    }
    public function next_as(){
        $this->next('next_as');
    }
    //public function next_as_lower(){
    //    $this->next('next_as');
    //}
    public function prev($override_kind=NULL){
        $subject_id = parent::create_check($forward=false);
        $this->save_workflow('prev', $subject_id, 'in progress', $this->override_workflow_params($override_kind, $subject_id));
        $this->forward_to_menu();
    }
    public function prev_as(){
        $this->prev('prev_as');
    }
    public function undo(){
        $this->prev('undo');
    }
    public function cond_approve(){
        // read cond_approve_res form value
        // if approve, approve, otherwise next_to
        $cond_op = $this->get_field_data('cond_approve_res', NULL);
        //$this->log('cond_approve, op='.$cond_op, 'debug');
        if ($cond_op == 'approve') {
            $this->approve('cond_approve_ok');
            return;
        }

        // default operation is next
        $this->next('cond_approve_failed');
    }

    public function approve($override_kind=NULL){
        $subject_id = parent::create_check($forward=false);
        $this->save_workflow('approve', $subject_id, 'approved', $this->override_workflow_params($override_kind));
        $this->forward_to_menu();
    }
    public function approve_as(){
        $this->approve('approve_as');
    }
    //public function approve_as_lower(){
    //    $this->approve('approve_as');
    //}
    public function reject(){
        $subject_id = parent::create_check($forward=false);
        $this->save_workflow('reject', $subject_id, 'rejected');
        $this->forward_to_menu();
    }
    public function review(){
        $subject_id = parent::create_check($forward=false);
        $this->update_review_status($subject_id, 'reviewed');
        $this->forward_to_menu();
    }

    private function setWFEmail($wfAttr){
        // FIXME
        // only external url is meainingfl
        $label_and_urls = $this->ConfigService->get_workflow_email_urls(
                                                        $this->pluginName);
        $subject_id = $wfAttr[$this->workflowModelName]['subject_id'];
        $url = $label_and_urls['external_baseurl']
                            .'/'
                            .$this->pluginName
                            .'/read?id='.$subject_id;
        $header = array('subject_id', 'creator_id', 'summary');
        $map = array();
        foreach( $header as $h ){
            $map[$h] = $wfAttr[$this->workflowModelName][$h];
        }
        $header = array_merge(array('url'), $header);
        $map['url'] = $url;
        $map['subject_tag'] = $this->_get_workflow_subject_tag();
        // FIXME
        // below is panasonic specific
        if( strcmp($this->pluginName, 'App2')==0 ){
            $case_inst = $this->pluginModel->findById($subject_id);
            $header_attr = array('Requested_by', 'Ship_to');
            foreach( $header_attr as $h ){
                $map[$h] = $case_inst[$this->pluginModelName][$h];
            }
            $header = array_merge($header, $header_attr);
        }

        return array('header'=>$header, 'map'=>$map);
    }

    private function _get_workflow_subject_tag()
    {
        $subject_column = $this->ConfigService->get_workflow_subject_tag_column($this->pluginName);
        $this->log('_get_workflow_subject_tag, col='.$subject_column, 'debug');
        if ($subject_column == null) return null; 

        // retrieve column value from database
        $subject_id = $this->_load_subject_id();
        $this->log('_get_workflow_subject_tag, wf_inst: ', 'debug');
        if (isset($this->data[$subject_column])) {
            $subject_tag = $this->data[$subject_column];
            $this->log('_get_workflow_subject_tag, tag from pre_create: '
                .$subject_tag, 'debug');
            return $subject_tag;
        }
        $this->log('_get_workflow_subject_tag, id='.$subject_id, 'debug');
        if (empty($subject_id))  return null;

        $case_inst = $this->pluginModel->findById($subject_id);
        //$this->log('_get_workflow_subject_tag, inst=', 'debug');
        //$this->log($case_inst, 'debug');

        $subject_tag = $case_inst[$this->pluginModelName][$subject_column];
        $this->log('_get_workflow_subject_tag, modelname,column,tag='
            .$this->pluginModelName.','.$subject_column.','.$subject_tag, 'debug');

        return $subject_tag;
    }

    private function _usernames_to_emails($usernames)
    {
        $emails = array();
        foreach ($usernames as $u) {
            $recipient_email = $this->User->findByUsername($u)['User']['mail'];
            if (!in_array($recipient_email, $emails)){
                array_push($emails, $recipient_email);
            }
        }
        return $emails;
    }

    // sendEmail must be controller action as it uses its view to create an email
    private function sendEmail($recipients, $template_name, $email_contents=NULL){
        $this->log('sendEmail, recipients=', 'debug');
        $this->log($recipients, 'debug');
        $this->log('template=', 'debug');
        $this->log($template_name, 'debug');
        if( !$this->ConfigService->is_workflow_email_enabled($this->pluginName) ){
            $this->log('sendEmail, email disabled', 'debug');
            return;
        }
        $recipient_emails = $this->_usernames_to_emails($recipients);

        $this->log('sendEmail, recp_email=', 'debug');
        $this->log($recipient_emails, 'debug');
        if( !empty($recipient_emails) ){
            $url_msg = '';
            $params = array(
                'recipient' => $recipients, 
                'contents'=>$email_contents, 
                'subject_tag'=>$this->_get_workflow_subject_tag()
            );
            if( empty($email_contents) ){
                $label_and_urls = $this->ConfigService->get_workflow_email_urls($this->pluginName);
                foreach( $label_and_urls as $label=>$url){
                    $url_msg .= '('.$label.')'.$url.' ';
                }
                $params['contents'] = $url_msg;
            }
            $this->log('url_msg='.$url_msg, 'debug');
            $this->AWSSES->to = $recipient_emails;
            if ($this->AWSSES->_aws_ses($template_name, $params)) {
                // succeed
                // TODO count failure and make registration complete
            }
        }
    }

    public function export_with_filter(){
        $this->set('action', 'post_export_with_filter');
    }

    private function _enum_to_val($enum_val, $enum_kind) {
        //$this->log('_enum_to_val, enum_val/kind='.$enum_val.','.$enum_kind, 'debug');
        $enum = $this->ConfigService->get_enum($enum_kind);
        if (empty($enum)) return array();
        $ret = array();
        if (!empty($enum_val) && isset($enum[$enum_val])) {
            #$this->log('_enum_to_val, sizeof enum_val='.sizeof($enum_val), 'debug');
            $ret = $enum[$enum_val];
            #$this->log('_enum_to_val, ret converted', 'debug');
        }
        return $ret;
    }

    private function _report_enum_to_val($data, $dbapp_fields, $col_enum_map)
    {
        if (sizeof($col_enum_map)==0) return $data;

        $data_new = array();
        foreach ($data as $numericKey => $row) {
            $f_index = 0;
            foreach ($dbapp_fields as $f) {
                if (in_array($f, array_keys($col_enum_map))) {
                    $modelName = $this->pluginModelName;
                    $colName = $f;
                    $colName_row = $f;
                    $modelAndCol = explode('.', $colName);
                    if (sizeof($modelAndCol)>1) {
                        $modelName = $modelAndCol[0];
                        $colName_row = $modelAndCol[1];
                        $this->log('_report_enum_to_val:update modelName to '.$modelName, 'debug');
                        $this->log('_report_enum_to_val:update colName to '.$colName_row, 'debug');
                    }
                    //$this->log('_report_enum_to_val:row(old)=', 'debug');
                    //$this->log($row, 'debug');
                    $col_new_val = $this->_enum_to_val(
                        $row[$modelName][$colName_row], 
                        $col_enum_map[$colName]
                    );
                    $this->log('_report_enum_to_val:col_new_val=', 'debug');
                    $this->log($col_new_val, 'debug');
                    $row[$modelName][$colName_row] = $col_new_val;
                    //$this->log('_report_enum_to_val:row(new)=', 'debug');
                    //$this->log($row, 'debug');
                }
            }
            array_push($data_new, $row);
        }
        return $data_new;
    }

    public function post_export_with_filter(){
        ini_set('max_execution_time', 300); // 5min

        $dbapp_fields = $this->ConfigService->get_filtered_report_columns(
            $this->pluginName);
        $dbapp_params = $this->ConfigService->get_filtered_report_params(
            $this->pluginName);
        $col_enum_map = $this->ConfigService->get_filtered_report_col_enum_map(
            $this->pluginName);

        //$this->log('post_export_with_filter, dbapp_params=', 'debug');
        //$this->log($dbapp_params, 'debug');

        $ts_from  = $dbapp_params['form_id_from'];
        $ts_to    = $dbapp_params['form_id_to'];
        $filt_col = $dbapp_params['filter_column'];

        $from = date('Y-m-d H:i:s', strtotime($this->get_field_data($ts_from)));
        $to   = date('Y-m-d H:i:s', strtotime($this->get_field_data($ts_to)));
        $this->log('post_export_with_filter, from/to='.$from.'/'.$to, 'debug');

        # apply filter against dbapp and add associated fields
        $filt_condition = array(
            // 'recursive' => -1, THIS WON'T WORK
            'fields' => $dbapp_fields,
            'conditions' => array(
                $filt_col." >= '" .$from ."'",
                $filt_col." <= '" .$to ."'"
            )
        );
        //$this->log('export filt_condition=', 'debug');
        //$this->log($filt_condition, 'debug');
        $data = $this->pluginModel->find('all', $filt_condition);
        $data = $this->_report_enum_to_val($data, $dbapp_fields, $col_enum_map);
        //$this->log('export:', 'debug');
        //$this->log($data, 'debug');
        $params = $this->Export->exportExcelInFile($this->pluginName, $data);
        //$this->log('export:fullpath='.$params['fullpath'], 'debug');
        //$this->log('export:filename='.$params['filename'], 'debug');
        $this->response->file($params['fullpath'],
            array('download'=>true, 'name'=>$params['filename']));

        return $this->response;
    }

    public function save_state() {
        //$this->log('save_state, post', 'debug');
        //$this->log($_POST, 'debug');
        //$this->log($_GET, 'debug');
        //$this->log($this->request, 'debug');

        // FIXME
        // Below is the way how cake form is used
        $subject_id = $_POST['data']['User']['state_dlg_subject_id'];
        $state      = $_POST['data']['User']['state_dlg_case_state'];
        $state_txt  = $_POST['data']['User']['state_dlg_case_state_text'];

        $this->log('save_state, subj_id,state,state_txt:'.$subject_id.','.$state.','.$state_txt, 'debug');

        $wf_inst = $this->workflowModel->findBySubjectId($subject_id)[$this->workflowModelName];
        $wf_inst['case_state'] = $state;
        $wf_inst['case_state_text'] = $state_txt;

        //$this->log('save_state, inst updated:', 'debug');
        //$this->log($wf_inst, 'debug');
        $this->workflowModel->save($wf_inst);

        $this->Session->setFlash('List updated', 'flash_error');
        //$this->log('save_state:next_url='.$this->request->here, 'debug');
        $this->redirect($this->referer());
    }
}
