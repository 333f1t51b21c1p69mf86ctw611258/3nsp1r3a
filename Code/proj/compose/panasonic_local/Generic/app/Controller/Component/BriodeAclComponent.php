<?php
App::uses('Component', 'Controller');

class BriodeAclComponent extends Component {
    public $components = array('Acl', 'Auth', 'ConfigService');

    public function is_readonly_user($aro, $pluginName){
        // readonly user is given read/view permission but create
        if( !$this->Acl->check($aro,
             $this->ConfigService->get_acl_aco($pluginName, 'create'),
             $this->ConfigService->get_acl_operation($pluginName, 'create')) ){
            return true;
        }
        return false;
    }

    public function get_users_in_layer($pluginName, $layer_name){
        $usernames = array();
        foreach($this->ConfigService->get_acl_layer_user($pluginName, $layer_name) as $user_aro){
            $userModel = ClassRegistry::init('User');
            $user_explode = explode(".", $user_aro);
            $username = $userModel->findById($user_explode[1])['User']['username'];
            array_push($usernames, $username);
        }
        return $usernames;
    }

    // return list of usernames
    // if list_if_manager=true, list only if the current user is the manager
    public function get_create_as_members_for_login_user($pluginName, $list_if_manager=false){
        $userId = $this->Auth->user('id');
        $user_aro = 'User.'.$userId;
        $ret = array();
        foreach( $this->ConfigService->get_create_as_allowed_group($pluginName) as $g_id ){
            if( (!$list_if_manager &&
                    in_array($user_aro, $this->ConfigService->get_group_members($pluginName, $g_id)))
                || in_array($user_aro, $this->ConfigService->get_managers_in_groups(array($g_id))) ){
                foreach( $this->ConfigService->get_group_members($pluginName, $g_id) as $u_aro ){
                    $member_explode = explode(".", $u_aro);
                    $userModel = ClassRegistry::init('User');
                    $member_username = $userModel->findById($member_explode[1])['User']['username'];
                    array_push($ret, $member_username);
                }
                break;
            }
        }
        return $ret;
    }
}

