<?php
App::uses('Component', 'Controller');

/**
  * Reads logs and XML configuration and returns corresponding 
  * parameters.
  */
class WorkflowComponent extends Component {
    public $components = array('ConfigService', 'DBAction', 'BriodeAcl');

    public function user_in_aro_level($pluginName, $user_aro, $level_name)
    {
        $users = $this->ConfigService->get_acl_layer_user($pluginName, $level_name);
        //$this->log('cond_approve check:, user_aro, val='.$user_aro, 'debug');
        //$this->log($users, 'debug');
        if (in_array($user_aro, $users)) {
            return true;
        }
        return false;
    }

    private function _get_post_action_label($pluginName, $owner_aro, $action){
        $action_label = $this->ConfigService->get_post_action_name($pluginName, $owner_aro, $action);
        if( empty($action_label) ){
            return array();
        }
        return array($action_label);
    }
    public function get_actions($pluginName, 
                                $action,
                                $pluginModelName, 
                                $owner_aro,
                                $form_id, 
                                $enableViewAttachment, 
                                $form_assignee,
                                $status, 
                                $usertype, 
                                $is_assignee, 
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
                                $cond_approve) {
    
        // FIXME
        // if action is defined in DBAction table, forward the request
        if( !$form_assignee && strcmp($action, 'update')== 0 ){
            $buttons = $this->DBAction->get_actions(
                                            $pluginName, 
                                            'update',
                                            $pluginModelName, 
                                            $form_id,
                                            $enableViewAttachment,
                                            false // exportToExcel
                                            );
            // FIXME
            // buttons are not maintained correctly in this view
            // append next_as for tickets in in_progress status
            // AND
            // workflow app at creation, after loading Excel the
            // window won't show next button. Enforce adding one.
            $btns_to_append = array(
                Configure::read('State.open')=>array('next'),
                Configure::read('State.in_progress')=>array(
                                        'next_as'),
                Configure::read('State.closed')=>array(),
            );

            $buttons[$status] = array_merge($buttons, $btns_to_append[$status]);
            return $buttons[$status];
        }
        
        $map = array(
            Configure::read('State.open')=>array(
                                'back'),
            Configure::read('State.in_progress')=>array(
                                'back'),
            Configure::read('State.closed')=>array(
                                'back'),
        );
        $map_nextprev = array(
            Configure::read('State.open')=>array(
                                'save',),
            Configure::read('State.in_progress')=>array(
                                'save',),
            Configure::read('State.closed')=>array(),
        );
        $map_single_excel_down_only = array(
            Configure::read('State.open')=>array(),
            Configure::read('State.in_progress')=>array(
                                'export_to_excel'),
            Configure::read('State.closed')=>array(
                                'export_to_excel'),
        );
        $map_single_excel_up_down = array(
            Configure::read('State.open')=>array(
                                'upload_single_data',),
            Configure::read('State.in_progress')=>array(
                                'export_to_excel'),
            Configure::read('State.closed')=>array(
                                'export_to_excel'),
        );
        $map_post_approve = array(
            Configure::read('State.open')=>array(),
            Configure::read('State.in_progress')=>array('update'),
            Configure::read('State.closed')=>
                $this->_get_post_action_label($pluginName, $owner_aro, 'approve'),
        );
        $map_undo = array(
            Configure::read('State.open')=>array(),
            Configure::read('State.in_progress')=>array(
                                'undo'),
            Configure::read('State.closed')=>array(),
        );
        $map_approval_fromUpper_op_as = array(
            Configure::read('State.open')=>array(),
            Configure::read('State.in_progress')=>$showApprovalOpAs,
                                //'prev_as', 'next_as', ), //'approve_as'),
            Configure::read('State.closed')=>array(),
        );
        $map_update_for_create_as = array(
            Configure::read('State.open')=>array(
                                'update'
            ),
            Configure::read('State.in_progress')=>array(
                                'update'
            ),
            Configure::read('State.closed')=>array(),
        );
/*
        $map_approval_fromLower_op_as = array(
            Configure::read('State.open')=>array(),
            Configure::read('State.in_progress')=>array(
                                'next_as_lower', 'approve_as_lower'),
            Configure::read('State.closed')=>array(),
        );
*/
        $map_admin = array(
            Configure::read('State.open')=>array(),
            Configure::read('State.in_progress')=>array(
                                'reject', 'approve'),
            Configure::read('State.closed')=>array('modify_after_close'),
        );

        $this->log('get_actions:status='.$status, 'debug');

        if( $enableSingleExcelUpDown ){
            $map[$status] = array_merge($map[$status], $map_single_excel_up_down[$status]);
        }else if( $enableSingleExcelDownOnly ){
            $map[$status] = array_merge($map[$status], $map_single_excel_down_only[$status]);
        } 
        
        // FIXME remove hardcoded values
        if( $showApprovalOpAs ){
            $map[$status] = array_merge($map[$status], $map_approval_fromUpper_op_as[$status]);
        }else if( $allowEditForCreateAsManager ){
            //$this->log('get_actions, allowEditForCreateAsManager', 'debug');
            $map[$status] = array_merge($map[$status], $map_update_for_create_as[$status]);
        }else if( $showNext || $showPrev){
            if( !$enablePostApprove ){
                //$this->log('get_actions, map=', 'debug');
                //$this->log($map, 'debug');
                //$this->log('get_actions, map_nextprev=', 'debug');
                //$this->log($map_nextprev, 'debug');
                $map[$status] = array_merge($map[$status], $map_nextprev[$status]);

                // FIXME: below only manipulates map of the current status
                // this order is critical as prev must be to the left of next
                if( $showPrev ) $map[$status] = array_merge($map[$status], array('prev'));
                if( $status!=Configure::read('State.closed') && $showNext ) $map[$status] = array_merge($map[$status], array('next'));
                if($allowApproveCancel){
                    $map[$status] = array_merge($map[$status], $map_admin[$status]);
                }
            }
        }else if( $is_assignee ){
            //$this->log('get_actions, is_assignee='.$is_assignee, 'debug');
            //$this->log('get_actions, enable_post_approve='.$enablePostApprove, 'debug');
            if( !$enablePostApprove ){
                $map[$status] = array_merge($map[$status], array('update'));
            }else{
                $map[$status] = array_merge($map[$status], $map_post_approve[$status]);
            }
        }

        if($enableViewAttachment){
            $map[$status] = array_merge($map[$status], array('view_attachment'));
        }
        if( !$is_assignee && $showUndo){
            $map[$status] = array_merge($map[$status], $map_undo[$status]);
        }

        // conditional approve and next to is exclusive, cond_approve takes precedence
        if ($cond_approve) {
            // add save
            $map[$status] = array_merge($map[$status], $map_nextprev[$status]);

            $found = array_search('next', $map[$status]);
            if ($found) {
                unset($map[$status][$found]);
                $this->log($map[$status], 'debug');
            }
            $found = array_search('update', $map[$status]);
            if ($found) {
                unset($map[$status][$found]);
                //$this->log($map[$status], 'debug');
            }
            if ($status==Configure::read('State.in_progress')) {
                $map[$status] = array_merge($map[$status], array('reject'));
            }
            $map[$status] = array_merge($map[$status], array('cond_approve'));
        }

        //$this->log('get_action, returning status='.$status, 'debug');
        //$map[$status] = array_merge($map[$status], array('update'));
        $this->log('get_action, flags=', 'debug');
        /*
        $this->log($pluginName
            .','
            .$action
            .','
            .$pluginModelName
            .','
            .$owner_aro
            .','
            .$form_id
            .','
            .$enableViewAttachment
            .','
            .$form_assignee
            .','
            .$status
            .','
            .$usertype
            .','
            .$is_assignee
            .','
            .$showNext
            .','
            .$showPrev
            .','
            .$allowApproveCancel
            .','
            .$enableViewAttachment
            .','
            .$enablePostApprove
            .','
            .$showApprovalOpAs
            .','
            .$enableSingleExcelUpDown
            .','
            .$enableSingleExcelDownOnly
            .','
            .$showUndo
            .','
            .$allowEditForCreateAsManager
            .','
            .$cond_approve
            , 'debug'
        );
        */
        $this->log('get_action, map=', 'debug');
        $this->log($map, 'debug');
        return $map[$status];
    }

    public function get_next_status($status, $action, $this_action, $current_user, $assignee){
        $this->log('get_next_status,status/action/this->action='.$status.'/'.$action.'/'.$this_action, 'debug');
        // if current action is save, return current status
        if( strcmp($this_action, 'save')==0 ) return $status;

        // if current_user == assignee, return open
        if ($current_user == $assignee) return Configure::read('State.open');

        $map = array(
            Configure::read('State.open')=>array(
                                '*'=>'State.in_progress',),
            Configure::read('State.in_progress')=>array(
                                'approve'=>'State.closed',
                                'reject' =>'State.closed',),
            Configure::read('State.closed')=>array(
                                '*'=>'State.closed'),
        );
        $ret = NULL;
        if( array_key_exists($status, $map) ){
            $conditions = $map[$status];
            if( array_key_exists('*', $conditions) ){
                $ret = $conditions['*'];
            }else{
                foreach( $conditions as $a=>$nextstate ){
                    $this->log('a,nextstate='.$a.','.$nextstate,'debug');
                    if( strcmp($action,$a)==0 ){
                        $ret = $nextstate;
                        break;
                    }
                }
            }
        } 

        if( $ret != NULL ){
            $this->log("wf:get_next_status,st found=".$ret, 'debug');
            return Configure::read($ret);
        }

        // no condition found, returning current
        return $status;
    }

    public function get_next_workflow_step($last_workflow_step, $action){
        if( strcmp($action,'next')==0 
                    || strcmp($action,'next_as')==0 ){  # FIXME: next_as has no chance to get called 
            return $last_workflow_step  + 1;
        }else if ( strcmp($action,'prev')==0 
                   || strcmp($action,'prev_as')==0 ){
            return $last_workflow_step - 1;
        }
        return $last_workflow_step;
    }

    private function get_all_approvers($p_username){
        $arr = array();
        if( empty($p_username) ) return $arr;
        $userModel = ClassRegistry::init('User');
        $user = $userModel->findByUsername($p_username);
        if( !isset($user['User']) || empty($user['User']['manager']) ){
            return array($p_username);
        }
        $mgr_inst = $userModel->findByName($user['User']['manager']);
        return array_merge($this->get_all_approvers($mgr_inst['User']['username']), array($p_username));
    }

    private function get_supervisor($userId){
        $userModel = ClassRegistry::init('User');
        $user = $userModel->findById($userId);
        $manager_name = $user['User']['manager'];
        if( empty($manager_name) ) return NULL;
        $manager = $userModel->findByName($manager_name);
        if( empty($manager) ) return NULL;

        return $manager['User']['username'];
    }

    public function get_forward($pluginName, $id, $loginuser, $userId){
        $ret = array();
        
        // add the group manager where the person belongs to
        $group_managers = $this->ConfigService->get_group_managers_for_user($pluginName, $loginuser);
        if( !empty($group_managers) ){
            array_push($ret, $this->ConfigService->get_group_managers_for_user($pluginName, $loginuser));
        }
        //  add the person's immediate boss
        $userModel = ClassRegistry::init('User');
        $supervisor = $this->get_supervisor($userId);
        if( !empty($supervisor) ){
            array_push($ret, $supervisor);
        }

        // if approver listing is requested and the login user is in approval_op_as group
        // show it all
        $user_aro = 'User.'.$userId;
        if( $this->ConfigService->is_approval_list_approvers_from_lower($pluginName) ){
            // FIXME there is one approval_op instance
            $grp_ids = $this->ConfigService->get_approval_op_allowed_group($pluginName);
            $grp_members_aros = $this->ConfigService->get_group_members(
                                                        $pluginName, $grp_ids );
            if( in_array( $user_aro, $grp_members_aros ) ){
                $approvers = $this->get_all_approvers($supervisor);
                foreach( $approvers as $a ){
                    if( !in_array( $a, $ret ) ){
                        array_push( $ret, $a );
                    }
                }
            }
        }

        // Panasonic V2
        // show immediate boss only
        // show all approvers for create_as user
        /*
        $aro = array('model'=>'User', 'foreign_key' => $userId);
        #print_r( $this->BriodeAcl->get_create_as_members_for_login_user($pluginName) );
        if( in_array($loginuser, $this->BriodeAcl->get_create_as_members_for_login_user($pluginName)) ){
            $layer_name = 'approver';
            $approvers = $this->BriodeAcl->get_users_in_layer($pluginName, $layer_name);
            foreach( $approvers as $a ){
                if( !in_array( $a, $ret ) ){
                    array_push( $ret, $a );
                }
            }
        }
        */
        return $ret;
    }

    // returns previous approver at a particular step
    public function get_past_user_at($id, $user_at, $loginuser){
        $this->log('get_past_user_at, id/approver_at/loginuser='.$id.'/'.$user_at.'/'.$loginuser, 'debug');
        if( $user_at < 1 ){
            return NULL;
        }
        $workflowEventLog = ClassRegistry::init('WorkflowEventLog');
        $log = $workflowEventLog->find('first', array(
                'conditions' => array(
                        'subject_id'=>$id,
                        'nth'=>$user_at,
                ),
                'order' => array('updated_at'=>'ASC')));

        if( empty($log) ){
            return NULL;
        }

        return $log['WorkflowEventLog']['prev_assignee'];
    }

    public function get_backward_all($id, $loginuser){
        // FIXME generate data from real configuration
        $ret = array('b1','b2','b3');
        
        // list of previous approvers

        return $ret;
    }

    public function get_assignable($id, $loginuser){
        // FIXME generate data from real configuration
        $ret = array('a1','a2','a3','a4');
        
        // list of all managers

        return $ret;
    }

    public function get_origin($id, $loginuser){
        // FIXME generate data from real configuration
        $ret = 'test1';
        
        // owner (created_by)

        return $ret;
    }

    public function get_comments($id, $commentModelName){
        if( !isset($id) ){
            return array();
        }
        $model = ClassRegistry::init($commentModelName);
        $result = $model->findAllBySubjectId($id);
        return $result;
    }

    private function is_closed($workflowModel, $workflowModelName, $form_id){
        $case = $workflowModel->findBySubjectId($form_id);
        if( !empty($case) &&
            strcmp($case[$workflowModelName]['state'], Configure::read('State.closed'))==0 ){
            return true;
        }
        return false;
    }

    public function check_briode_privilege($pluginName, $workflowModel, $userId, $current_action, $pluginModelName, $workflowModelName, $form_id, $owner_aro){

        // XXXX when the case status is closed XXX
        // XXXX allow peole in the post_approve group to view the file XXXX
        // Requirement above was replacedwith the folliwing:
        //   all customer service (who has read permission only) can read the case
        //if( $this->is_closed($workflowModel, $workflowModelName, $form_id) ){
        $user_aro = 'User.'.$userId;

        // Panasonic V2
        // allow edit for owner
        if ($owner_aro == $user_aro) return true;

        // Panasonic V2
        // allow customer care access to sales details
        if ($this->user_in_aro_level($pluginName, $user_aro, 'user')) return true;

        $grp_ids = $this->ConfigService->get_post_action_allowed_group($pluginName, $owner_aro, 'approve');
        // FIXME assume there is only one instance
        if( !empty($grp_ids) && sizeof($grp_ids)>0 && in_array( $user_aro, $this->ConfigService->get_group_members($pluginName, $grp_ids) ) ){
            return true;
        }

        // if action is one of approval-ops (namely next_as, approve_as)
        // and the user is in approval-op-as group, allow the person 
        // to perform the requested op
        $approval_ops = array('next_as', 'approve_as', 'next_as_lower', 'approve_as_lower');
        if( in_array($current_action, $approval_ops) ){
            $user_aro = 'User.'.$userId;
            // FIXME: multi approval_op_as is allowed here
            $grp_ids = $this->ConfigService->get_approval_op_allowed_group($pluginName);
            if( in_array($user_aro, $this->ConfigService->get_group_members($pluginName, $grp_ids) ) ){
                return true;
            }
        }

        // if login user is a manager of create_as for the owner of the ticket
        $create_as_ops = array('read', 'update', 'next_as');
        if( in_array($current_action, $create_as_ops) ){
            $user_aro = 'User.'.$userId;
            // FIXME: multi-create as is allowed here
            $grp_ids = $this->ConfigService->get_create_as_allowed_group($pluginName);
            if( in_array($user_aro, $this->ConfigService->get_managers_in_groups($grp_ids) ) ){
                return true;
            }
        }

        return false;
    }
}


