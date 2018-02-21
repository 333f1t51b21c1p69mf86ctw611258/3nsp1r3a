<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
Configure::load('config');

class UsersController extends AppController {
	
    public $components = array(
        'SQLConnection', 
        'ExcelLoader', 
        'ServletLogin', 
        'Cookie', 
        'AWSSES', 
        'Session',
        'ConfigService'
    );
    public $helpers = array('UserList', 'UserSetting');
    public $uses = array('UserEventLog','Aro');

    private $loginRedirectUrl;

    /*
    function __construct(){
        $this->uses = array_merge($this->uses, array());
        $this->components = array_merge($this->components, 
            array('Auth' => array(
                'loginRedirect' => array(
                    'controller' => $this->pluginName,
                    'action' => $this->pluginName.'/main_menu'),
                'logoutRedirect' => array(
                    'controller' => 'Users',
                    'action' => 'login'),
            )));
        $this->helpers = array_merge($this->helpers, array());
        parent::__construct();
    }*/
  
    // FIXME: should specify correct Acl config
    public function isAuthorized($user=null){
        return true;
    }

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('add', 'login','logout', 'secure_login', 'upload_users', 'preview_users', 'userupload_confirmation');

        $this->setViewParams();
        $user_id = NULL;
        if( isset($this->data['User']['username']) ){
            $user = $this->User->findByUsername($this->data['User']['username']);
            if( $user ){
                $user_id = $user['User']['id'];
            }
        }

        // check user has access privilege to any app
        $user_aro_inst = $this->Aro->findByForeignKey($user_id);
        if (empty($user_aro_inst)) {
            $this->Session->setFlash(
                __('User has no access privilege'), 'flash_error');
            $this->redirect('/Generic/Users/logout');
            return;
        }
        $this->loginRedirectUrl = '/' . $this->ConfigService->get_initial_plugin($user_id) .'/main_menu';
        $this->log('redirectUrl(no access priv):'.$this->loginRedirectUrl, 'debug');
    }

    // FIXME
    // call base class impl to remove code duplicate
    protected function setViewParams(){
        $this->log('setViewParams,ope=', 'debug');

        $this->set('operations', array()); 

        $this->set('pluginName', 'Users');
        $this->set('pluginModelName', 'User');

        $this->set('appName', 'User Profile');
        $this->set('urlAndLabel', $this->ConfigService->get_app_selector_url_and_label());
        $this->set('enableReport', false);

        // set dummy data
        $this->set('birt_service', NULL);
        $this->set('tomcatBaseUrl', NULL);

        $this->set('enableProfilePicture', $this->ConfigService->is_profile_picture_enabled());
        
        $this->set('admin_menu', 'disable');
        $this->set('proj_params', $this->ConfigService->get_proj_params());

        $this->set('sessionid', session_id());
	}

    private $usertypeMap = array(
        1 => 'Admin',
        2 => 'Senior Manager',
        3 => 'Manager',
        4 => 'Employee',
    );

    /////////////////////////////////////////////
    // upload procedure
    public function upload_users(){} 

    public function preview_users() {
        $this->log('preview', 'debug');
		$link = $this->SQLConnection->openSQLconnection();

        if( !($_POST["submit"] == "Upload"  && $_FILES['file']['name'] )) {
            $this->Session->setFlash(__('Please choose a file to upload'),'flash_error');
            $this->redirect('/upload_users');
            return;
        }

        $info = pathinfo($_FILES['file']['name']);
        $this->log('user preview_users, info:', 'debug');
        $this->log($info, 'debug');
        $ext = $info['extension'];
        if (! preg_match("/xls/",$ext)) {
            $this->Session->setFlash(__('Please upload an Excel file.'),'flash_error');
            $this->redirect('/upload_users');
            return;
        }

        // copy the excel file from tmp to uploads
        $userListExcelFile = $this->appConfig['userListExcelFileFullpath'];
        move_uploaded_file( $_FILES["file"]["tmp_name"], $userListExcelFile);

        $this->set('users', $this->getNewUsersInXls());
    }

    private function getNewUsersInXls(){
        $this->log("getNewUsersInXls(), generating list of new users", "debug");

        // create a list of users in DB
        $usersInstInDB = $this->User->find('all');
        $usersInDb = array();
        foreach( $usersInstInDB as $idx=>$tblAndAttrs ){
            $this->log('preview_users, attrs=', 'debug');
            $this->log($tblAndAttrs, 'debug');
            array_push($usersInDb, $tblAndAttrs['User']['username']);
        }
        
        // create a list of users in Excel
        $userListExcelFile = $this->appConfig['userListExcelFileFullpath'];
        $usersInExcel = $this->ExcelLoader->getUserList($userListExcelFile);
        
        // schedule to add new users only
        $usersToAdd = array();
        foreach($usersInExcel as $idx=>$userInExcelInst ){
            
            $usernameInExcel = $userInExcelInst['User']['username'];
            if( !in_array($usernameInExcel, $usersInDb) ){
                array_push($usersToAdd, $userInExcelInst);
            }
        } 
        return $usersToAdd;
    }

    public function userupload_confirmation() {
        $userListExcelFile = $this->appConfig['userListExcelFileFullpath'];
        $users = $this->getNewUsersInXls();

        $this->log("userupload_confirmation() users=", "debug");
        $this->log($users, 'debug');
        $this->User->saveAll($users);
		$retval = $this->exec_in_vendorpath('SyncLdapWithDB');

        $this->Session->setFlash(__('Upload users successfully!'), 'flash_success');

        $this->redirect($this->loginRedirectUrl);
    }

	//not used anymore
	public function secure_login() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				// save username entered in the login form
				$username = $this->Auth->user('username');
				//$this->redirect(array('controller' => 'Ringi', 'action' => 'main_menu'));
				return $this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Session->setFlash(__('Invalid username or password, try again'),'flash_error');
			}
		}
	}

    private function save_user_activity($action){
        if( empty($this->Auth->user('username')) ) return;

        $userevent = array('UserEventLog'=>array('creator_id'=>$this->Auth->user('username'),
                                         'created_at'=>date('Y-m-d H:i:s'),
                                         'action'=>$action,
                                         )
        );
        $this->UserEventLog->save($userevent);
    }

	public function login() {
        #$this->log('Session=', 'debug');
        #$this->log($_SESSION, 'debug');
        #$this->log($this->Session, 'debug');
        $nextUrl = $this->loginRedirectUrl;
        if( $this->Session->read('redirect_after_login') ){
            // FIXME automatic removal of controller name may not be safe
            $redirect = $this->Session->read('redirect_after_login');
            $this->log('login(), redirect_after_login(1)='.$redirect, 'debug');
            $redir_exp = explode( "/", $redirect );
            if (sizeof($redir_exp)>2) {
                array_shift( $redir_exp );
                array_shift( $redir_exp );
            }
            #$this->log('redirect_after_login=', 'debug');
            #$this->log($redir_shift, 'debug');
            $nextUrl = implode( "/", $redir_exp ); 
            if( sizeof($redir_exp)>1 ){
                $nextUrl = '/'.$nextUrl;
            }
            $this->log('login(), nextUrl(1)='.$nextUrl, 'debug');
            $this->Session->delete('redirect_after_login');
        }
        if( !empty($this->referer()) && $this->request->is('get') &&
            isset($_SESSION['Auth']['redirect']) ){
            $this->log('login(), Session.Auth.redirect=', 'debug');
            $this->log($_SESSION['Auth']['redirect'], 'debug');
            $this->Session->write('redirect_after_login', $_SESSION['Auth']['redirect']);
            //$this->log('setting redirect_after_login='. $this->Auth->redirectUrl(), 'debug');
        }
        if( $this->Auth->user() ){
            $ext = pathinfo($nextUrl, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), array('css', 'js', 'jpeg', 'jpg'))) return;
            $this->log('login: auth->user exists and now redirects to '.$nextUrl, 'debug');
            return $this->redirect($nextUrl);
        }
		include("authenticate.ctp");
		if ($this->request->is('post')) {
			$usr = $this->data['User']['username'];
			$pass = $this->data['User']['password'];

			// run information through authenticator
			if(authenticate($usr,$pass))
			{
                // FIXME role should be set from DB
                $user_instance = $this->User->findByUsername($usr);
                $user_role = $user_instance['User']['usertype'];
                
                $this->Session->write('servletLoginSessionId', $this->ServletLogin->login($usr, $user_role));
                $this->log($_COOKIE, 'debug');
				// authentication passed
				// Connect to MySQL
				$link = $this->SQLConnection->openSQLconnection();
				$salted_pass = $this->Auth->password($pass);			//put salt on password
				$querynewpass = "UPDATE users SET `password`='".$salted_pass."' WHERE username='".$usr."'"; 
				mysqli_query($link, $querynewpass) or die(mysqli_error($link));	//overwrite password

				if ($this->Auth->login()) {
                    $this->save_user_activity('login');
					$this->Session->setFlash(__('Logged in Successfully!'), 'flash_success');
					//$this->Session->setFlash('Something good.', 'flash_success');
					//return $this->redirect($this->Auth->redirectUrl());

                    $this->sendWatchdogEmail($usr);

                    // TODO: investigate following situation
                    // session id has changed by now! save agaion
                    $this->ServletLogin->after_login($usr, $user_role);
                    return $this->redirect($nextUrl);
				}
			}	
			else {
				$this->Session->setFlash(__('Invalid username or password.'),'flash_login');
			}
		}
	}

	public function logout() {
        $this->log('logout:', 'debug');
        $this->log($_COOKIE, 'debug');

        $this->save_user_activity('logout');
        $this->ServletLogin->logout($this->Session->read('servletLoginSessionId'));
		if ($this->Auth->logout()) {
			$this->redirect($this->Auth->logout());
		} else {
			$this->Session->setFlash(__('Expired your session'),'flash_error');
		}
	}

    public function main_menu() {
        $this->user_setting();
    }

	public function index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

    public function events(){
        $this->paginate = array(
            'order' => array('created_at' => 'DESC') );
        $this->Paginator->settings = $this->paginate;
        $list = $this->Paginator->paginate('UserEventLog');
        $this->set('pluginName', 'UserEventLog');
        $this->set('pluginModelName', 'UserEventLog');
        $this->set('attrs', array('creator_id', 'created_at', 'action'));
        $this->set('list', $list);
    }

	public function view($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->set('user', $this->User->read(null, $id));
	}
	
	private function get_user_DN($username, $department){
		$ldapConf = Configure::read('ldap');
		$userDN = 	'uid='. $username. 
					','.
					'ou='. 	$department. 
					','.
					$ldapConf['DefaultDC'];
		return $userDN;
	}
	
	private function get_manager_DN($managerid){
        $link = $this->SQLConnection->openSQLconnection();
		$sql = "SELECT DN FROM users WHERE username='".$managerid."'";
		$query = mysqli_query($link, $sql);
		$manager = mysqli_fetch_assoc($query);
		if( $manager == NULL ){
			// this should not happen
			return 'DN_NOT_FOUND_FOR_THIS_MANAGER';
		}
		return $manager['DN'];
	}
	
	
	
	public function add() {
		$link = $this->SQLConnection->openSQLconnection();
		$username = $this->Auth->user('username');
		if (file_exists("img/".$username.".jpg")){
			$this->set('picture', $username);
		}
		else{
			$this->set('picture', "default");
		}
		if ($this->request->is('post')) {
			$this->User->create();

			//$sql = "SELECT username FROM users WHERE username='".$newusername."'";
			//$query=mysqli_query($sql);
			//$existing = mysqli_fetch_assoc($query);
			//if ($existing==NULL) {
			//	
			$user = $this->data['username'];
			$usertype = $this->data['usertypes'];
			$fullname = $this->data['name'];
			$email = $this->data['email'];
			$department = $this->data['department'];
			$title = $this->data['title'];
			$manager = $this->get_manager_DN($this->data['manager']);
			$activeflag = isset($this->data['activeFlag'])?1:0;
			$userdn = $this->get_user_DN($user, $department);			
			
			$sql = "INSERT INTO users (DN, usertype, name, username, mail, department, title, manager, activeflag, creator_id, created_at)
				VALUES ('$userdn','$usertype','$fullname','$user','$email', '$department', '$title', '$manager', '$activeflag', '$username', now());";	

			$query = mysqli_query($link, $sql);
			if ($query != NULL)
			{
				// synchronize user table with LDAP, usertable.csv
				$ldapConfig = Configure::read('ldap');
				$ldapHost = $ldapConfig['Hostname'];		
				$retval = $this->exec_in_vendorpath('SynchronizeUser', $ldapHost);
				$this->log('synchronizeUser retval='.$retval, 'debug');
				
				$newPassword = $_POST['newPassword'];
				$salted_pass = $this->Auth->password($newPassword);			//put salt on password
				$querynewpass = "UPDATE users SET password='$salted_pass' WHERE username='$user'";
				//mysqli_query($querynewpass) or die(mysqli_error());	//overwrite password
				
				$ldapConfig = Configure::read('ldap');
				$ldapHost = $ldapConfig['Hostname'];
				$this->exec_in_vendorpath('ResetPassword', $ldapHost, '" '. $userdn. '"', $newPassword);
				
				$this->Session->setFlash(__("The user was created successfully"),'flash_success');
			}
			else{
				$this->Session->setFlash(__("Oops! Create Failed!"),'flash_error');
			}
		}
		
		//Name, title, departement
		$sql="SELECT usertype, name, department, title,  manager, mail, activeflag FROM users WHERE username = '$username'";
		//$sql="SELECT usertype, username, name, department, title, manager, email, activeflag,
		//	FROM users
		//	where username= admin direct username from pulldown";
		$query = mysqli_query($link, "$sql");
		$array = mysqli_fetch_assoc($query);
		$this->set('usertype', $array['usertype']);
		$this->set('username', $username);
		$this->set('email', $array['mail']);
		$this->set('name', $array['name']);
		$this->set('department', $array['department']);
		$this->set('title', $array['title']);
		$this->set('manager', $array['manager']);
		$this->set('activeflag', $array['activeflag']);
		
		$usertype_count = 0;
        foreach($this->usertypeMap as $typeOption => $typeMeaning){
            $usertypeOption[$usertype_count] = $typeOption;
            $usertypeMeaning[$usertype_count] = $typeMeaning;
            $usertype_count++;
        }
        $this->set('usertype_count', $usertype_count);
        $this->set('usertypeMeaning', $usertypeMeaning);
        $this->set('usertypeOption', $usertypeOption);
		
		$sql="SELECT usertype, username, name, department, title, manager, mail, activeflag From users ORDER BY username";
		$query = mysqli_query($link, "$sql");
		if ($query != NULL){
			$userCount = mysqli_num_rows($query);
		}
		$this->set('userCount', $userCount);
		if ($userCount > 0){
			for ($i = 0 ; $i < $userCount; $i++){
				$array = mysqli_fetch_assoc($query);
				$allUsertype[$i] = $array['usertype'];
				$allUserame[$i] = $array['username'];
				$allName[$i] = $array['name'];
				$allDepartment[$i] = $array['department'];
				$allTitle[$i] = $array['title'];
				$allManager[$i] = $array['manager'];
				$allEmail[$i] = $array['mail'];
				$allActiveFlag[$i] = $array['activeflag'];
			}
			$this->set('allUsertype', $allUsertype);
			$this->set('allUsername', $allUserame);
			$this->set('allName', $allName);
			$this->set('allDepartment', $allDepartment);
			$this->set('allTitle', $allTitle);
			$this->set('allManager', $allManager);
			$this->set('allEmail', $allEmail);
			$this->set('allActiveFlag', $allActiveFlag);
		}
		
	}

	public function edit($id = null) {
		/*
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->User->read(null, $id);
			unset($this->request->data['User']['password']);
		}
		*/
	}

	public function password_change() {
		include("authenticate.ctp");
		$link = $this->SQLConnection->openSQLconnection();
		$username = $this->Auth->user('username');
		if (file_exists("img/".$username.".jpg")){
			$this->set('picture', $username);
		}
		else{
			$this->set('picture', "default");
		}
		if ($this->request->is('post')) {
			$resourceflag = $this->data['resourceflag'];
			if($resourceflag == 'user'){
				// run information through authenticator
				if(authenticate($username,$_POST["currentPassword"]))
				{
					$newPassword = $_POST['newPassword'];
					$salted_pass = $this->Auth->password($newPassword);			//put salt on password
					$querynewpass = "UPDATE users SET password='$salted_pass' WHERE username='$username'"; 
					//mysqli_query($querynewpass) or die(mysqli_error());	//overwrite password
						
					$sql="SELECT DN FROM users WHERE username='$username'";
					$query = mysqli_query($link, $sql);
					$user = mysqli_fetch_assoc($query);
					$userdn = $user['DN'];
					$ldapConfig = Configure::read('ldap');
					$ldapHost = $ldapConfig['Hostname'];
					$this->exec_in_vendorpath('ResetPassword', $ldapHost, '" '. $userdn. '"', $newPassword);
					
				    $this->Session->setFlash(__("Your password was updated successfully"),'flash_success');
					//$this->redirect(array('controller' => 'Users', 'action' => 'password_change'));	
				}	
				else {
					
					$this->Session->setFlash(__('Invalid password, try again. If you forget your password, please ask administrator to reset!'),'flash_error');
				}
			}
			elseif ($resourceflag == 'admin'){
				$user = $_POST['users'];
				$newPassword = $_POST['newPassword'];
				$salted_pass = $this->Auth->password($newPassword);			//put salt on password
				$querynewpass = "UPDATE users SET password='$salted_pass' WHERE username='$user'";
				//mysqli_query($querynewpass) or die(mysqli_error());	//overwrite password
				
				$sql="SELECT DN FROM users WHERE username='$user'";
				$query = mysqli_query($link, $sql);
				$user = mysqli_fetch_assoc($query);
				$userdn = $user['DN'];
				$ldapConfig = Configure::read('ldap');
				$ldapHost = $ldapConfig['Hostname'];
				
				$return = $this->exec_in_vendorpath('ResetPassword', 
													'"'. $ldapHost. '"', 
													'"'. $userdn. '"', 
													'"'. $newPassword. '"');
				$this->log('ResetPassword retval='.$return, 'debug');
				$this->Session->setFlash(__("Your password was updated successfully"),'flash_success');
			}
		}
		
		//Name, title, departement
		$sql="SELECT usertype, name, department, title, name FROM users WHERE username = '$username'";
		//$sql="SELECT usertype, username, name, department, title, manager, email, activeflag,
		//	FROM users
		//	where username= admin direct username from pulldown";
		$query = mysqli_query($link, "$sql");
		$array = mysqli_fetch_assoc($query);
		$this->set('usertype', $array['usertype']);
		$this->set('username', $username);
		$this->set('name', $array['name']);
		$this->set('department', $array['department']);
		$this->set('title', $array['title']);
		
		
        $sql="SELECT username From users where username not like '\_%' ORDER BY username";
		$query = mysqli_query($link, "$sql");
		if ($query != NULL){
			$userCount = mysqli_num_rows($query);
		}
		$this->set('userCount', $userCount);
		if ($userCount > 0){
			for ($i = 0 ; $i < $userCount; $i++){
				$array = mysqli_fetch_assoc($query);
				$allUserame[$i] = $array['username'];
			}
			$this->set('allUsername', $allUserame);
		}
		
		
		
	}
	
	public function password_reset() {
		$link = $this->SQLConnection->openSQLconnection();
		if ($this->request->is('post')) {	
			if ($_POST["newpass"]!=="" && $_POST["selection"]!=="") {
				$user = $_POST["selection"];
				$sql="SELECT DN FROM users WHERE username='".$user."'";
				$query = mysqli_query($link, $sql);
				$userDN = mysqli_fetch_assoc($query);
				
				$newPassword=$_POST["newpass"];
				$userdn = $userDN['DN'];
				$ldapConfig = Configure::read('ldap');
				$ldapHost = $ldapConfig['Hostname'];
				$this->exec_in_vendorpath('ResetPassword', $ldapHost, '" '. $userdn . '"', $newPassword);				
				$this->Session->setFlash(__("The password of ".$_POST["selection"]." was updated successfully"));
                return $this->redirect($this->loginRedirectUrl);
				
			}
			else {
				$this->Session->setFlash(__('Please select an appropriate password!'),'flash_error');
			}
			
		}
	
		$sql = "SELECT username FROM users";
		$query = mysqli_query($link, $sql);
		
		$allusers = array();
		$num = mysqli_num_rows($query);
		for($j=0; $j<$num; $j++){
			$row = mysqli_fetch_assoc($query);
			$allusers[$j] = $row['username'];
			//print_r($row);
		}

		//print_r($allusers);
		
		$this->set('allusers',$allusers);
		
	}
	
	public function user_setting(){
		$link = $this->SQLConnection->openSQLconnection();
		$username = $this->Auth->user('username');
		if (file_exists("img/".$username.".jpg")){
			$this->set('picture', $username);
		}
		else{
			$this->set('picture', "default");
		}
		if ($this->request->is('post')) {
			$resourceflag = $this->data['resourceflag'];
			if ($resourceflag =="user"){
				$name = $this->data['userFullName'];
				$email = $this->data['userEmail'];
				$sql = "UPDATE users Set name = '$name', mail = '$email', updated_at = now(), updator_id  = '$username' WHERE username= '$username'";
				$query = mysqli_query($link, $sql);
			}
			elseif ($resourceflag == "admin"){
				$user = $this->data['users'];
				$usertype = $this->data['usertypes'];
				$fullname = $this->data['name'];
				$email = $this->data['email'];
				$department = $this->data['department'];
				$title = $this->data['title'];
				$manager = $this->data['manager'];
				$activeflag = isset($this->data['activeFlag'])?1:0;
				
				
				$sql="UPDATE users SET  usertype = '$usertype',
							name = '$fullname',
							mail = '$email',
							department = '$department',
							title = '$title',
							manager = '$manager',
							activeflag = '$activeflag',
							updated_at = now(),
							updator_id  = '$username'
					WHERE username= '$user'";
				$query = mysqli_query($link, $sql);
			}
			$ldapConfig = Configure::read('ldap');
			$ldapHost = $ldapConfig['Hostname'];
			$retval = $this->exec_in_vendorpath('SynchronizeUser', $ldapHost);
			$this->Session->setFlash(__("Your profile was updated successfully"),'flash_success');
		}
		//Name, title, departement
		$sql="SELECT usertype, name, department, title,  manager, mail, activeflag FROM users WHERE username = '$username'";
		//$sql="SELECT usertype, username, name, department, title, manager, email, activeflag,
		//	FROM users
		//	where username= admin direct username from pulldown";
		$query = mysqli_query($link, "$sql");
		$array = mysqli_fetch_assoc($query);
		$this->set('usertype', $array['usertype']);
		$this->set('username', $username);
		$this->set('email', $array['mail']);
		$this->set('name', $array['name']);
		$this->set('department', $array['department']);
		$this->set('title', $array['title']);
		$this->set('manager', $array['manager']);
		$this->set('activeflag', $array['activeflag']);
		
		$usertype_count = 0;
        foreach($this->usertypeMap as $typeOption => $typeMeaning){
            $usertypeOption[$usertype_count] = $typeOption;
            $usertypeMeaning[$usertype_count] = $typeMeaning;
            $usertype_count++;
        };
        $this->set('usertype_count', $usertype_count);
        $this->set('usertypeMeaning', $usertypeMeaning);
        $this->set('usertypeOption', $usertypeOption);
        //print_r($usertypeMeaning);
		
		$sql="SELECT usertype, username, name, department, title, manager, mail, activeflag From users ORDER BY username";
		$query = mysqli_query($link, "$sql");
		if ($query != NULL){
			$userCount = mysqli_num_rows($query);
		}
		$this->set('userCount', $userCount);
		if ($userCount > 0){
			for ($i = 0 ; $i < $userCount; $i++){
				$array = mysqli_fetch_assoc($query);
				$allUsertype[$i] = $array['usertype'];
				$allUserame[$i] = $array['username'];
				$allName[$i] = $array['name'];
				$allDepartment[$i] = $array['department'];
				$allTitle[$i] = $array['title'];
				$allManager[$i] = $array['manager'];
				$allEmail[$i] = $array['mail'];
				$allActiveFlag[$i] = $array['activeflag'];
			}
			$this->set('allUsertype', $allUsertype);
			$this->set('allUsername', $allUserame);
			$this->set('allName', $allName);
			$this->set('allDepartment', $allDepartment);
			$this->set('allTitle', $allTitle);
			$this->set('allManager', $allManager);
			$this->set('allEmail', $allEmail);
			$this->set('allActiveFlag', $allActiveFlag);
		}
	}
	
	
	public function profile_upload(){
		$link = $this->SQLConnection->openSQLconnection();
		$username = $this->Auth->user('username');
		if (file_exists("img/".$username.".jpg")){
			$this->set('picture', $username);
		}
		else{
			$this->set('picture', "default");
		}
		//Name, title, departement
		$sql="SELECT usertype, name, department, title,  manager, mail, activeflag FROM users WHERE username = '$username'";
		//$sql="SELECT usertype, username, name, department, title, manager, email, activeflag,
		//	FROM users
		//	where username= admin direct username from pulldown";
		$query = mysqli_query($link, "$sql");
		$array = mysqli_fetch_assoc($query);
		$this->set('usertype', $array['usertype']);
		$this->set('username', $username);
		$this->set('email', $array['mail']);
		$this->set('name', $array['name']);
		$this->set('department', $array['department']);
		$this->set('title', $array['title']);
		$this->set('manager', $array['manager']);
		$this->set('activeflag', $array['activeflag']);

		if ($this->request->is('post')) {
			//set where you want to store files
			//in this example we keep file in folder upload 
			//$HTTP_POST_FILES['ufile']['name']; = upload file name
			//for example upload file name cartoon.gif . $path will be upload/cartoon.gif

			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$temp = explode(".", $_FILES["ufile"]["name"]);
			$extension = end($temp);
			$path= "img/".$username."."."jpg";
			
			if ((($_FILES["ufile"]["type"] == "image/gif")
			|| ($_FILES["ufile"]["type"] == "image/jpeg")
			|| ($_FILES["ufile"]["type"] == "image/jpg")
			|| ($_FILES["ufile"]["type"] == "image/pjpeg")
			|| ($_FILES["ufile"]["type"] == "image/x-png")
			|| ($_FILES["ufile"]["type"] == "image/png"))
			&& ($_FILES["ufile"]["size"] < 200000)
				&& in_array($extension, $allowedExts))
			{
				if ($_FILES["ufile"]["error"] > 0)
				{
					$this->log( "Return Code: " . $_FILES["ufile"]["error"] . "<br>", 'debug');
				}
				else
				{
					if (file_exists($path))
					{
						unlink($path);
					}
					$result = copy($_FILES['ufile']['tmp_name'], $path);
					if($result)
					{
						$this->set('path', $path);
						$this->Session->setFlash(__('Profile uploaded successfully'),'flash_success');
						//$this->isUpdate++;
						//echo $this->isUpdate;
						//$this->set('isUpdate', $this->isUpdate);
					}
					else
					{
						$this->Session->setFlash(__('Fail in Copy file'),'flash_error');
					}
				}
			}
			else {
				$this->Session->setFlash(__('The type of file is not correct or the size of file is too big, please choose the correct file within 2MB. '),'flash_error');
			}

		}


	}
	public function rmdir_recursive($dir){
		foreach(scandir($dir) as $file){
			if ('.' === $file || '..' === $file) continue;
			if (is_dir("$dir/$file")) $this->rmdir_recursive("$dir/$file");
			else unlink("$dir/$file");
		}
		rmdir($dir);
		return;
	}
	
	public function department(){
        $link = $this->SQLConnection->openSQLconnection();
        $username = $_POST['username'];
        $sql = "select department from users where username='$username'";
        $query = mysqli_query($link, $sql);
        $array = mysqli_fetch_assoc($query);
        $department = $array['department'];

        $data = array(
                    'department'=>$department,
        );
        $this->set('data', $data);

        // FIXME somehow department.json does not return json struct
        return new CakeResponse(array('type'=>'json','body'=>json_encode($data)));
    }

	public function deptmembers(){
        $link = $this->SQLConnection->openSQLconnection();
        $dept = $_POST['department'];

        //$dept = 'Chicago';
        $sql = "select username from users where department='$dept'";
        $query = mysqli_query($link, $sql);
        $members = array();
        while( $tmp = mysqli_fetch_assoc($query) ){
            array_push($members, $tmp['username']);
        }

        $data = array( 'members'=>$members );

        $this->set('data', $data);

        // FIXME somehow department.json does not return json struct
        return new CakeResponse(array('type'=>'json','body'=>json_encode($data)));
    }

    // FIXME this is ITA specific
    // sendEmail must be controller action as it uses its view to create an email
    private function sendWatchdogEmail($login_username){
        //if( strcmp($login_username,'shink')!=0 ) return;
        if( strcmp($login_username,'shink')!=0 ) return;

        $this->AWSSES->to = 'taira@enspirea.com';
        $params = array();
        if ($this->AWSSES->_aws_ses('itaLoggedIn', $params)) {
            // succeed
            // TODO count failure and make registration complete
        }
    }
}

