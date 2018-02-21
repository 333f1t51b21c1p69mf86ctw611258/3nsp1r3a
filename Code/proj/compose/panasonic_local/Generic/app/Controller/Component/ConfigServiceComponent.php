<?php
App::uses('Component', 'Controller');
require_once(APP . 'Vendor' . DS .'predis'. DS. 'predis'.DS. 'autoload.php');
class ConfigServiceComponent extends Component {

    private $redis;

    private function connect_dictionary(){
        if( $this->redis ) return;

        //require "Predis/Autoloader.php";
        Predis\Autoloader::register();
        try{ 
            $this->redis = new Predis\Client([
                'scheme' => 'tcp',
                'host' => 'redis',
                'port' => 6379 
            ]);
        }
        catch (Exception $e) {
            echo "Couldn't connected to Redis";
            echo $e->getMessage();
        }
    }
    public function getRedisInstance(){
        $this->connect_dictionary();
        return $this->redis;
    }

    // Global
    public function get_app_selector_url_and_label(){
        $ret = array();
        $app_num = $this->getRedisInstance()->llen('App_list_urls');
        for($i=0; $i<$app_num; ++$i){
            $url = $this->getRedisInstance()->lindex('App_list_urls', $i);
            $ret[$url] = $this->getRedisInstance()->hget('App_list_labels', $url);
        }
        return $ret;
    }

    public function get_app_navname($pluginName){
        return $this->getRedisInstance()->hget('App_navname_labels', $pluginName);
    }

    // DB common
    public function get_app_plugin_name($pluginName){
        return $this->getRedisInstance()->hget($pluginName, 'app_name');
    }

    public function get_app_plugin_model_name($pluginName){
        return $this->getRedisInstance()->hget($pluginName, 'model_name');
    }
    public function get_app_plugin_db_name($pluginName){
        return $this->getRedisInstance()->hget($pluginName, 'db_name');
    }
    public function get_app_plugin_main_page($pluginName){
        return $this->getRedisInstance()->hget($pluginName, 'main_page');
    }
    public function get_app_db_read_prohibited($pluginName){
        $key = $pluginName.'_db_read_prohibited';
        $ret = array();
        foreach($this->getRedisInstance()->hkeys($key) as $role){
            $subkey = $this->getRedisInstance()->hget($key, $role);
            $ret[$role] = $this->getRedisInstance()->smembers($subkey);
        }
        return $ret;
    }
    public function get_app_mainview_list($pluginName){
        $key = $pluginName.'_mainview_list';
       
        $menu = array();
        for( $i=0; $i<$this->getRedisInstance()->llen($key); ++$i ){ 
            array_push($menu, $this->redis->lindex($key, $i));
        }

        return $menu;
    }
    public function get_app_searchview_list($pluginName){
        $key = $pluginName.'_searchview_list';
       
        $menu = array();
        for( $i=0; $i<$this->getRedisInstance()->llen($key); ++$i ){ 
            array_push($menu, $this->redis->lindex($key, $i));
        }

        return $menu;
    }

    public function get_app_searchexclude_columns($pluginName)
    {
        $key = $pluginName.'_searchexclude_column';
       
        $menu = array();
        for ($i=0; $i<$this->getRedisInstance()->llen($key); ++$i) { 
            array_push($menu, $this->redis->lindex($key, $i));
        }

        return $menu;
    }

    public function get_upload_ignored_column($pluginName){
        $key = $pluginName.'_upload_ignored';

        return $this->getRedisInstance()->smembers($key); 
    }
    public function get_app_operations($appName, $pluginName){
        $key = $pluginName.'_op_label';
        $ops = array();
        foreach( $this->getRedisInstance()->hgetall($key) as $action=>$url_label){
            $url = '/'.$appName.'/'.$pluginName.'/'.$action;
            $url_map = array($url, $url_label);
            $action_acos_key = $pluginName.'_acl_action_acos';
            $action_ops_key = $pluginName.'_acl_action_ops';
            $aco = $this->getRedisInstance()->hget($action_acos_key, $action);
            $aco_op = $this->getRedisInstance()->hget($action_ops_key, $action);
            
            if( isset($ops[$aco][$aco_op]) ){
                array_push($ops[$aco][$aco_op], $url_map);
            }else{
                $ops[$aco][$aco_op] = array($url_map);
            }
        }

        $this->log('get_app_operations, ops=', 'debug');
        $this->log($ops, 'debug');

        return $ops;
    }
    public function get_db_col_size($pluginName){
        return $this->getRedisInstance()->hget($pluginName, 'db_col_size');
    }
    public function get_initial_plugin($user_id){
        $this->log('user_id='.$user_id, 'debug');
        $initial_plugin = $this->getRedisInstance()->hget('App_initial_plugin', 'default');
        if( !empty($user_id) ){
            $user_aco = 'User.'.$user_id;
            $loginapp_for_user = $this->getRedisInstance()->hget(
                                                'App_user_loginapp', $user_aco);
            if( $loginapp_for_user ){
               $initial_plugin = $loginapp_for_user;
            }
        }
        $this->log('get_initial_plugin,returning:'.$initial_plugin, 'debug');
        return $initial_plugin;
    }
    public function import_calculated_fields($pluginName){
        $import_all = $this->getRedisInstance()->hget($pluginName, 'import_calculated_columns');
        return ( strcmp($import_all, 'true')==0 );
    }

    // db update notification
    public function is_notification_email_enabled($pluginName){
        $key = $pluginName."_notification";
        return ( strcmp($this->getRedisInstance()->hget($key, 'email_mode'),'enable')==0 );
    }
    public function get_notification_email_urls($pluginName){
        return $this->getRedisInstance()->hgetall($pluginName.'_notification_email_urls');
    }
    public function get_notification_email_address($pluginName){
        $key = $pluginName."_notification";
        return $this->getRedisInstance()->hget($key, 'email_address');
    }

    // Workflow
    public function get_workflow_model_name($pluginName){
        return $this->getRedisInstance()->hget($pluginName.'_workflow', 'model_name');
    }
    public function get_workflow_db_name($pluginName){
        return $this->getRedisInstance()->hget($pluginName.'_workflow', 'db_name');
    }
    public function get_workflow_comment_model_name($pluginName){
        return $this->getRedisInstance()->hget($pluginName.'_workflow', 'comment_model_name');
    }
    public function get_workflow_comment_db_name($pluginName){
        return $this->getRedisInstance()->hget($pluginName.'_workflow', 'comment_db_name');
    }
    public function is_workflow_email_enabled($pluginName){
        return strcmp($this->getRedisInstance()->hget($pluginName.'_workflow', 'email_mode'), 'enable')==0;
    }
    public function get_workflow_email_urls($pluginName){
        return $this->getRedisInstance()->hgetall($pluginName.'_workflow_email_urls');
    }
    public function is_workflow_single_excel_enabled($pluginName){
        return strcmp($this->getRedisInstance()->hget($pluginName.'_workflow', 'single_excel_up_down'), 'enable')==0;
    }
    public function get_workflow_subject_tag_column($pluginName)
    {
        $key = $pluginName."_workflow_email";
        return $this->getRedisInstance()->hget($key, 'subject_tag_column');
    }
    public function get_workflow_assignee_at_approve($pluginName)
    {
        $key = $pluginName."_workflow";
        return $this->getRedisInstance()->hget($key, 'assignee_at_approve');
    }

    public function get_multi_options($option_name){
        $optionlist_name = $this->getRedisInstance()->hget('App_ui_options', $option_name);
        return $this->getRedisInstance()->lrange($optionlist_name, 0, -1);
    }

    public function get_enum($enum_name){
        $enum_key = $this->getRedisInstance()->hget('App_enums', $enum_name);
        return $this->getRedisInstance()->hgetall($enum_key);
    }

    public function set_db_schema($pluginName, $colname, $type){
        $this->getRedisInstance()->hset($pluginName.'_db_schema', $colname, $type);
    }
    public function set_excel_schema($pluginName, $colname, $uitype){
        $this->getRedisInstance()->hset($pluginName.'_excel_schema', $colname, $uitype);
    }
    public function set_db_table($pluginName, $tableArray){
        $len = $this->getRedisInstance()->llen($pluginName.'_db_table');
        $tableArray_old = array();
        for($i = 0; $i < $len; $i++){
            $tableName = $this->getRedisInstance()->lindex($pluginName.'_db_table', $i);
            array_push($tableArray_old, $tableName);
        }
        foreach($tableArray as $tableName => $Name){
            if(!in_array($tableName, $tableArray_old)){
                $this->getRedisInstance()->lpush($pluginName.'_db_table', $tableName);
            }
        }
    }
    public function set_db_item_number($pluginName, $item_number_array){
        foreach($item_number_array as $tableName => $item_number){
            if(strlen($item_number) != 0){
                $this->getRedisInstance()->hset($pluginName.'_db_item_number', $tableName, $item_number);
            }
        }
    }
    public function set_db_col_schema($pluginName, $columnNames){
        foreach($columnNames as $colName => $tableName){
            $this->getRedisInstance()->hset($pluginName.'_db_col_schema', $colName, $tableName);
        }
    }
    public function get_excel_type($pluginName, $colname){
        return $this->getRedisInstance()->hget($pluginName.'_excel_schema', $colname);
    }
    public function get_db_type($pluginName, $colname){
        return $this->getRedisInstance()->hget($pluginName.'_db_schema', $colname);
    }
    public function get_db_table($pluginName){
        $len = $this->getRedisInstance()->llen($pluginName.'_db_table');
        $tableArray = array();
        for($i = 0; $i < $len; $i++){
            $tableName = $this->getRedisInstance()->lindex($pluginName.'_db_table', $i);
            array_push($tableArray, $tableName);
        }
        return $tableArray;
    }
    public function get_db_n_table($pluginName){
        $len = $this->getRedisInstance()->llen($pluginName.'_db_table');
        return $len;
    }
    public function get_db_item_number($pluginName){
        $tableArray = $this->get_db_table($pluginName);
        $itemNumberArray = array();
        foreach($tableArray as $index => $tableName){
            $item_number = $this->getRedisInstance()->hget($pluginName.'_db_item_number', $tableName);
            if(strlen($item_number) != 0){
                $itemNumberArray[$tableName] = $item_number;
            }
        }
        return $itemNumberArray;
    }
    public function get_db_col_schema($pluginName){
        $columnNames_redis = $this->getRedisInstance()->hgetall($pluginName.'_db_col_schema');
        $columnNames = array();
        foreach($columnNames_redis as $colName => $tableName){
            if(!array_key_exists($tableName, $columnNames)){
                $columnNames[$tableName] = array($colName);
            }else{
                array_push($columnNames[$tableName], $colName);
            }
        }
        return $columnNames;
    }

    // ACL 
    public function get_acl_operation($pluginName, $action){
        $ops_key = $pluginName.'_acl_action_ops';
        // use default config if undefined
        if( !$this->getRedisInstance()->hexists($ops_key, $action) ){
            $h_key = $pluginName.'_acl';
            $h_op_key = 'default_acl_op';
            return $this->getRedisInstance()->hget($h_key, $h_op_key);
        }
        return $this->getRedisInstance()->hget($ops_key, $action);
    }

    public function get_acl_aco($pluginName, $action){
        $acos_key = $pluginName.'_acl_action_acos';
        if( !$this->getRedisInstance()->hexists($acos_key, $action) ){
            $h_key = $pluginName.'_acl';
            $h_aco_key = 'default_acl_aco';
            // return array to match lrange below
            return $this->getRedisInstance()->hget($h_key, $h_aco_key);
        }
        return $this->getRedisInstance()->hget($acos_key, $action);
    }

    public function get_acl_layer_user($pluginName, $layer_name){
        $key = $pluginName.'_acl_'.$layer_name;
        
        return $this->getRedisInstance()->lrange($key, 0, -1);
    }

    public function in_manager_groups($user_aro, $groups){
        if( empty($groups) ) return false;

        foreach( $groups as $g ){
            if( !$this->in_manager_group($user_aro, $g) ){
                return false;
            }
        }
        return true;
    }

    public function in_manager_group($user_aro, $group){
        $this->log('in_manager_group, user_aro=', 'debug');
        $this->log($user_aro, 'debug');
        $this->log('group='.$group, 'debug');

        $group_manager_key = 'App_acl_group'.$group.'_manager';
        return  in_array($user_aro, $this->getRedisInstance()->
                    lrange($group_manager_key,0,-1));
    }

    private function in_group($user_aro, $group){
        $group_manager_key = 'App_acl_group'.$group.'_manager';
        $group_member_key = 'App_acl_group'.$group.'_member';

        $this->log('in_group:gid,user_aro='.$group.','.$user_aro, 'debug');
        return ( in_array($user_aro, $this->getRedisInstance()->
                    lrange($group_manager_key,0,-1)) ||
                 in_array($user_aro, $this->getRedisInstance()->
                    lrange($group_member_key,0,-1)) );
    } 

    // FIXME all methods that depend on this should be replaced with find_groups
    private function find_group($user_aro){
        $this->log('find_group:'.$user_aro, 'debug');
        $group_key = 'App_acl_groups';
        foreach( $this->getRedisInstance()->hkeys($group_key) as $group ){
            if( $this->in_group($user_aro, $group) ){
                return $group;
            }
        }
        return NULL; 
    }
    private  function find_groups($user_aro){
        $this->log('find_groups:'.$user_aro, 'debug');
        $group_key = 'App_acl_groups';
        $groups = array();
        foreach( $this->getRedisInstance()->hkeys($group_key) as $group ){
            if( $this->in_group($user_aro, $group) ){
                array_push($groups, $group);
            }
        }
        return $groups;
    }

    public function is_group_manager($creator_userid, $login_id){
        $creator_aro = 'User.'.$creator_userid;
        $login_aro   = 'User.'.$login_id;
        
        // find the group of creator
        $group_found = $this->find_group($creator_aro);
        if( empty($group_found) ) return false;
   
        return $this->in_manager_group($login_aro, $group_found);
    }

    public function is_admin($pluginName, $login_id){
        $login_aro = 'User.'.$login_id;
        #$this->log('is_admin, aro='.$login_aro, 'debug');
        $admin_key = $pluginName."_acl_admin";
        #$this->log('is_admin, admin_key='.$admin_key, 'debug');

        return in_array($login_aro, $this->getRedisInstance()->
                    lrange($admin_key, 0, -1));
    }

    public function get_managers_in_groups($groups_found){
        $managers = array();
        foreach( $groups_found as $g ){
            #if( sizeof($g) > 1 ){
            #    $e = new Exception();
            #    $this->log($e->getTraceAsString(), 'debug');
            #}
            $group_key = 'App_acl_group'.$g.'_manager';
            $managers = array_merge($managers, $this->getRedisInstance()->lrange($group_key, 0, -1));
        }
        #$this->log('get_managers_in_groups,$managers=', 'debug');
        #$this->log($managers, 'debug');
        return $managers;
    }

    public function get_group_managers_for_user($pluginName, $login_id){
        $user_aro = 'User.'.$login_id;
        $groups_found = $this->find_groups($user_aro);
        $this->log('groups_found=', 'debug');
        $this->log($groups_found, 'debug');
        if( empty($groups_found) ) return NULL;
        
        return $this->get_managers_in_groups($groups_found);
    }

    public function get_group_members($pluginName, $group_ids){
        $this->log('get_group_members', 'debug');
        #$this->log('size of group_name='.sizeof($group_name), 'debug');
        if( !is_array($group_ids) ){
            $group_ids = array($group_ids);
            #$e = new Exception();
            #$this->log($e->getTraceAsString(), 'debug');
        }
        $res = array(); 
        foreach ($group_ids as $id) {
            $group_manager_key = 'App_acl_group'.$id.'_manager';
            $group_member_key = 'App_acl_group'.$id.'_member';
            $res = array_merge(
                $res,
                $this->getRedisInstance()->lrange($group_manager_key, 0, -1),
                $this->getRedisInstance()->lrange($group_member_key, 0, -1)
            );
        }
        return $res;
    }

    private function get_group_ids_by_name($group_name){
        $retarr = array();
        foreach( $this->getRedisInstance()->hkeys('App_acl_groups') as $group_id ){
            $cur_name = $this->getRedisInstance()->hget('App_acl_groups', $group_id);
            if( strcmp($cur_name, $group_name)==0 ){
                array_push($retarr, $group_id);
            }
        }
        return $retarr;
    }

    public function is_readable_by_all($pluginName){
        return strcmp($this->getRedisInstance()->hget($pluginName, 'db_visibility'), 'readable_by_all')==0;
    }

    public function is_report_enabled($pluginName){
        return strcmp($this->getRedisInstance()->hget($pluginName, 'report_mode'), 'enable')==0;
    }

    // EXCELDATA 
    public function get_cellid_to_colname_map($pluginName){
        return $this->getRedisInstance()->hgetall('EXCELDATA_'.$pluginName.'_colName');

    }
    private function strcond_to_sign($strcond){
        if( empty($strcond) ) return NULL;
        $ret = '';
        if( strpos($strcond, 'lower')!==false ) $ret .= '<';
        if( strpos($strcond, 'higher')!==false ) $ret .= '>';
        if( strpos($strcond, 'equal')!==false ) $ret .= '=';
        return $ret;
    }
    private function get_range_validation_inequality($key, $validation_name){
        $kind = $this->getRedisInstance()->hget($key, 'kind');

        $ret = array($validation_name);
        switch( $kind ){
            case 'between':
                $high_kind_sign = $this->strcond_to_sign(
                    $this->getRedisInstance()->hget($key, 'high_kind'));
                $high_value = $this->getRedisInstance()->hget($key, 'high_value');
                $low_kind_sign = $this->strcond_to_sign(
                    $this->getRedisInstance()->hget($key, 'low_kind'));
                $low_value = $this->getRedisInstance()->hget($key, 'low_value');

                $ret = array_merge($ret, array(
                                'x', $low_kind_sign, $low_value,
                                'x', $high_kind_sign, $high_value));
                break;
            case 'lower_or_equal':
            case 'lower':
            case 'upper_or_equal':
            case 'upper':
                $kind_sign = $this->strcond_to_sign($kind);
                $value = $this->getRedisInstance()->hget($key, 'value');
                $ret = array_merge($ret, array(
                                'x', $kind_sign, $value));
                break;
            default:
                // unknown patten, ignoring
                continue;
                break;
        }
        return $ret;
    }

    // key => (lower bound, lower cond, upper bound, upper cond)
    public function get_app_range_validation_map($pluginName){
        $pattern_prefix = $pluginName."_validate_cssclass_val";
        $keys = $this->getRedisInstance()->keys($pattern_prefix.'*');
        $ret = array();
        foreach( $keys as $key ){
            $validation_key = substr($key, strlen($pattern_prefix));
            array_push($ret, $this->get_range_validation_inequality($key, $validation_key));
        }
        return $ret;
    }

    public function get_app_mainview_sort_condition($pluginName){
        $key_base = $pluginName."_mainview_sort";
        $sortby_colnames = $this->getRedisInstance()->lrange($key_base."_colname", 0, -1);
        $sortby_orders = $this->getRedisInstance()->lrange($key_base."_order", 0, -1);

        if( empty($sortby_colnames) ) return NULL;

        return array( 'colnames' => $sortby_colnames,
                      'orders' => $sortby_orders );
    }

    //////////////////////////////////////
    //  Reports
    public function get_app_base_url(){
        $key = 'App_base_url';
        return $this->getRedisInstance()->hgetall($key);
    }

    public function get_report_list(){
        $key = 'Report_list';
        return $this->getRedisInstance()->hgetall($key);
    }

    public function get_plugin_name($app_name){
        $key = 'App_name_to_plugin';
        return $this->getRedisInstance()->hget($key, $app_name);
    }

    public function get_plugin_model_name($app_name){
        return $this->getRedisInstance()->hget($this->get_plugin_name($app_name), 'model_name');
    }

    //////////////////////////////////////
    //  Reports (filter by date)
    private function _get_cols_enum_map($cols_raw)
    {
        $cols = array();
        $col_enum_map = array();
        foreach ($cols_raw as $c) {
            $c_val = explode(':', $c);
            array_push($cols, $c_val[0]);
            if (sizeof($c_val)>1) {
                $enum_kind = explode('enum_', $c_val[1])[1];
                $col_enum_map[$c_val[0]] = $enum_kind;
            }
        }
        return array(
            'cols' => $cols,
            'col_enum_map' => $col_enum_map
        );
    }
 
    public function get_filtered_report_columns($app_name) {
        $key = $app_name.'_workflow_report_columns';
        $cols_raw = $this->getRedisInstance()->lrange($key, 0, -1);
        $cols_enum_map = $this->_get_cols_enum_map($cols_raw);
        
        return $cols_enum_map['cols'];
    }

    public function get_filtered_report_col_enum_map($app_name) {
        $key = $app_name.'_workflow_report_columns';
        $cols_raw = $this->getRedisInstance()->lrange($key, 0, -1);
        $cols_enum_map = $this->_get_cols_enum_map($cols_raw);
        
        return $cols_enum_map['col_enum_map'];
    }

    public function get_filtered_report_params($app_name) {
        $key = $app_name.'_workflow_report_params';
        return $this->getRedisInstance()->hgetall($key);
    }


    //////////////////////////////////////
    //  Redirect from user
    public function get_login_redirect($user_role){
        $key = 'App_login_redirect_by_usertype';
        return $this->getRedisInstance()->hget($key, $user_role);
    }

    public function get_impexp_keys($app_name){
        $key = $app_name.'_impexp_keys';
        return $this->getRedisInstance()->lrange($key, 0, -1);
    }

    private function _startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }


    private function _get_post_action_group_key($app_name, $user_aro, $action)
    {
        $this->log('_get_post_action_group_key', 'debug');
        $group_ids_found = $this->find_groups($user_aro);
        $this->log('_get_post_action_group_key, groups found:', 'debug');
        $this->log($group_ids_found, 'debug');
        $g_id_found = null;
        foreach ($group_ids_found as $g_id) {
            // FIXME
            // code depends on create_as structure, otherwise post_action 
            // hierarchy cannot be constructed
            // this code should be consolidated into one place
            $grp_list_key = 'App_acl_groups';
            $g_found = $this->getRedisInstance()->hget($grp_list_key, $g_id);
            $key_ptn = $app_name.'_workflow_create_as';
            if ($this->_startsWith($g_found, $key_ptn)) {
                $g_id_found = $g_id;
                break;
            }
        }
        $this->log('_get_post_action_group_key, group found=', 'debug');
        $this->log($g_id_found, 'debug');

        if (empty($g_id_found)) return null;

        // find the manager of create_as and locate corresponding 
        // AppX_workflow_post_approve_regionX group
        $g_manager_aros = $this->get_managers_in_groups(array($g_id_found));

        $this->log('_get_post_action_group_key, aros=', 'debug');
        $this->log($g_manager_aros, 'debug');

        // only one manager must be found
        $group_ids_found = $this->find_groups($g_manager_aros[0]);
        $this->log('_get_post_action_group_key, groups_found=', 'debug');
        $this->log($group_ids_found, 'debug');
        $workflow_post_action_key_base = $app_name.'_workflow_post_'.$action.'_region';
        $workflow_post_action_key_ptn = '/'.$workflow_post_action_key_base.'(\d*)_(.*)/';
        $group_found = null;
        foreach ($group_ids_found as $g_id) {
            $grp_list_key = 'App_acl_groups';
            $g_found = $this->getRedisInstance()->hget($grp_list_key, $g_id);
            $this->log('_get_post_action_group_key, g_found='.$g_found, 'debug');
            $this->log('_get_post_action_group_key, ptn='.$workflow_post_action_key_ptn, 'debug');
            preg_match($workflow_post_action_key_ptn, $g_found, $matched);
            if (!empty($matched)) {
                $group_found = $workflow_post_action_key_base. $matched[1];
                break;
            }
        }
        $this->log('_get_post_action_group_key, group_found=', 'debug');
        $this->log($group_found, 'debug');
       
        if (empty($group_found)) {
            return null;
        }

        return $group_found;
    }

    //////////////////////////////////////
    // Post-approval action
    /* 
     * return post action name where the given user belongs to. 
     *   search path:
     *     find if user_aro(owner of issue) is a manager/members of create_as
     *        Iterate thru App_acl_groups
     *
     *     if found, locate the key with correct region
     *        AppX_workflow_post_approve_regionX
     *        AppX_workflow_post_approve_regionX_notification_groups
     */
    public function get_post_action_name($app_name, $user_aro, $action) 
    {
        $post_action_group_key = $this->_get_post_action_group_key($app_name, $user_aro, $action);
        return $this->getRedisInstance()->hget($post_action_group_key, 'action');
    }

    /* 
     * return post action name where the given user belongs to
     */
    public function get_post_action_allowed_group($app_name, $user_aro, $action){
        $post_action_group_key = $this->_get_post_action_group_key($app_name, $user_aro, $action);
        return $this->get_group_ids_by_name( $this->getRedisInstance()->hget($post_action_group_key, 'groupAllowedTo') );
    }

/*
    public function is_post_action_script_enabled($app_name, $action){
        $key = $app_name.'_workflow_post_'.$action;
        return (strcmp($this->getRedisInstance()->hget($key, 'enablePostScript'), 'enable')==0);
    }
*/

    /* 
     * return post action name where the given user belongs to
     */
    public function is_post_action_group_notification_enabled($app_name, $user_aro, $action){
        $post_action_group_key = $this->_get_post_action_group_key($app_name, $user_aro, $action);
        return (strcmp($this->getRedisInstance()->hget($post_action_group_key, 'enableGroupNotification'), 'enable')==0);
    }

    /* 
     * return post action name where the given user belongs to
     */
    // Panasonic V2 : data held as notification_key does not hold anything meaningful
    public function get_post_action_notification_group_ids($app_name, $user_aro, $action){
        $post_action_group_key = $this->_get_post_action_group_key($app_name, $user_aro, $action);
        $notification_key = $post_action_group_key. '_notification_groups';
        $grp_ids = array();
        foreach( $this->getRedisInstance()->lrange($notification_key, 0, -1) as $grp_name ){
            $grp_ids = array_merge( $grp_ids, $this->get_group_ids_by_name( $grp_name ) );
        }
        return $grp_ids;
    }

    public function get_post_action_group_ids($app_name, $user_aro, $action){
        $post_action_group_key_ptn = $app_name. '_workflow_post_'.$action.'_region*';

        $grp_ids = array();
        foreach ($this->getRedisInstance()->keys($post_action_group_key_ptn) as $key_base) {
            $group_name = $key_base. '_allowed_users';
            $this->log('get_post_action_group_ids, group_name='.$group_name, 'debug');
            $g_id = $this->get_group_ids_by_name($group_name);
            $this->log('get_post_action_group_ids,i g_id found=', 'debug');
            $this->log($g_id, 'debug');
            if (sizeof($g_id)>0) {
                $g_id = $g_id[0];
                if ($this->in_group($user_aro, $g_id)) {
                    array_push($grp_ids, $g_id);
                }
            }
        }
        return $grp_ids;
    }

    public function get_post_action_default_notifier($app_name, $action) {
        $notifier_key = $app_name. '_workflow_notifier_at_'.$action;

        $emails = array();
        foreach ($this->getRedisInstance()->lrange($notifier_key, 0, -1) as $notifier_email) {
            $this->log('get_post_action_default_notifier, email:'
                .$notifier_email, 'debug');
            array_push($emails, $notifier_email);
        }
        return $emails;
    }

    //////////////////////////////////////
    // create_as control
    public function get_create_as_allowed_group($app_name){
        $key = $app_name.'_workflow_create_as';
        return $this->get_group_ids_by_name( $this->getRedisInstance()->hget($key, 'groupAllowedTo') );
    }
    public function is_create_as_enabled($app_name){
        $key = $app_name.'_workflow_create_as';
        $subkey = 'mode';
        return ( strcmp( $this->getRedisInstance()->hget($key, $subkey), 
                         'enable' )==0 );
    }

    //////////////////////////////////////
    // approval operation control (next, approve)
    public function get_approval_op_allowed_group($app_name){
        $key = $app_name.'_workflow_approval_op_as';
        return $this->get_group_ids_by_name( $this->getRedisInstance()->hget($key, 'groupAllowedTo') );
    }
    // direction should be fromUpper or fromLower
    public function is_approval_op_enabled($app_name, $direction){
        $key = $app_name.'_workflow_approval_op_as';
        $subkey = $direction;
        return ( strcmp( $this->getRedisInstance()->hget($key, $subkey), 
                         'enable' )==0 );
    }
    public function is_approval_list_approvers_from_lower($app_name){
        $key = $app_name.'_workflow_approval_op_as';
        $subkey = 'listApproversFromLower';
        return ( strcmp( $this->getRedisInstance()->hget($key, $subkey), 
                         'enable' )==0 );
    }
    public function get_approval_op_allowed_group_manager($app_name){
        $group_ids =  $this->get_approval_op_allowed_group($app_name);
        #print_r($group_ids);
        return $this->get_managers_in_groups( $group_ids );
    }

    // profile picture
    public function is_profile_picture_enabled(){
        $key = 'App_user_profile';
        $subkey = 'picture';
        return ( strcmp( $this->getRedisInstance()->hget($key, $subkey), 
                         'enable' )==0 );
    }

    ////////////////////////////////////////////////
    // project parameters
    public function get_proj_params()
    {
        $project_param_key = 'App_project_params';
        $kv_map = array();
        return $this->getRedisInstance()->hgetall($project_param_key);
    }
}
