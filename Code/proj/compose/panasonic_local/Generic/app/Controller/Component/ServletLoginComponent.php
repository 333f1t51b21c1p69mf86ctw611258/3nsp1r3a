<?php
App::uses('Component', 'Controller');

class ServletLoginComponent extends Component {

    public $components = array(
        'ConfigService'
    );

    // FIXME take following params from config files
    private $key ='test';
    private $tomcat_url = 'https://tomcat/';
    private $auth_servlet_name = '/smanage';
    private $timeout = 2000000;
    private $op_login = 'login';
    private $BriodeSession = NULL;

    public function __construct()
    {
        $this->BriodeSession = ClassRegistry::init('BriodeSession');
    }

    private function openSQLconnection($dbname='genericdata'){
        $host = 'mysql';
        $username = 'root';
        $password = '';
        $database = $dbname;

        $link=mysqli_connect($host, $username, $password);
        $ret = mysqli_select_db($link, $database);

        return $ret;
    }

    private function encrypt_data($message, $initialVector, $secretKey) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, md5($secretKey), $message, MCRYPT_MODE_CFB, $initialVector));
    }

    private function genIV(){
        return substr(md5(md5($this->key)), 0, 16);
    }

    private function genData($command, $sessId){
        $this->log('sessionId='.$sessId, 'debug');
        return  array(  'session_id'=> $sessId,
                        'op'        => $command,
                        'timeout'   => $this->timeout );
    }

    private function genHttpOptions($command, $sessId){
        return array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content'   => 'data='. urlencode($this->encrypt_data(
                                        http_build_query($this->genData($command, $sessId)), 
                                        $this->genIV($this->iv), 
                                        $this->key)),
                ),
            );
    }

    private function save_session($sess_id, $role, $username){
        $sess = array('BriodeSession'=>array('sessionid'=>$sess_id,
                                         'role'=>$role,
                                         'login'=>$username,
                                         'created_at'=>date('Y-m-d H:i:s'),
                                           )
        );
        $this->BriodeSession->save($sess);
        //setcookie("BriodeSession", $sess_id);
        return $sess_id;
    }

    public function cache_session_add($username, $role){
        $sess_id = $this->save_session(session_id(), $role, $username);
        /*
        // save csrf as well
        if( isset($_COOKIE["csrftoken"]) ){
            $sess_id = $this->save_session($_COOKIE["csrftoken"], $role, $username);
        }
        // in some cases token is formatted as JSESSIONID_[0-9a-z]{8}
        $pattern = '/^JSESSIONID_[0-9a-z]*$/';
        foreach( $_COOKIE as $key=>$value ){
            if( preg_match($pattern, $key, $matches) ){
                $sess_id = $this->save_session($value, $role, $username);
            }
        }
        */
        return $sess_id;
    }

    # sessions generated over 24hours ago is considered orphaned
    private function cache_remove_orphaned(){
        $sess = $this->BriodeSession->find('all', array('conditions' => array('BriodeSession.created_at < NOW() -INTERVAL 2 HOUR'), 'recursive' => -2));
        foreach( $sess as $s ){
            $this->log("deleting orphaned session ", 'debug');
            $this->log($s, 'debug');
            $this->BriodeSession->delete($s['BriodeSession']['id']);
        }
    }

    private function cache_session_remove($sessId){
        $this->log("finding cached session ".$sessId, 'debug');
        $sess = $this->BriodeSession->findAllBySessionid($sessId);
        $this->log("sessions found: ", 'debug');
        $this->log($sess, 'debug');
        foreach( $sess as $s ){
            $this->log("deleting cached session ", 'debug');
            $this->log($s, 'debug');
            $this->BriodeSession->delete($s['BriodeSession']['id']);
        }
    }

    public function get_birt_service($role){
        return ($role==4)?'birt':'birtmgr';
    }

    public function get_birt_url($role){
        return $this->tomcat_url.$this->get_birt_service($role);
    }

    // role: 1=users, otherwise managers
    private function get_auth_url($role){
        return $this->get_birt_url($role).$this->auth_servlet_name;
    }

    public function login($username, $role) 
    {
        $this->cache_remove_orphaned();
        $sessId = session_id();
        $ret = array_merge(
            $this->genHttpOptions('login', $sessId),
            array("ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ))
        ); 
        $this->log('ServletLogin:login()', 'debug');
        $this->log($ret, 'debug');
        $context = stream_context_create($ret);

        $result = @file_get_contents(
            $this->get_auth_url($role), false, $context);
        if ($result=== FALSE) {
            $result = 'No servlet';
        }
        $sessId = $this->cache_session_add($username, $role);
        $this->log('User '.$username.' from '.$_SERVER['REMOTE_ADDR'], 'debug');
        $this->log('Login: Servlet post request returned', 'debug');

        // FIXME: works only with servlet
        $this->log($result, 'debug');
        return $sessId;
    }

    public function after_login($username, $role){
        //$sessId = $this->cache_session_add($username, $role);
    }

    public function logout($sessId){
        $ret = $this->genHttpOptions('logout', $sessId);
        $this->log('ServletLogin:logout()', 'debug');
        $this->log($ret, 'debug');
        $this->cache_session_remove($sessId);
        $context = stream_context_create($ret);
        // FIXME: 
        // Just call manager/non-manager role each so a user will
        // get deregistered from both services.
        // FIXME: works only with servlet
        //$result = file_get_contents($this->get_auth_url(1), false, $context);
        //$result = file_get_contents($this->get_auth_url(2), false, $context);
        $this->log('Logout: Servlet post request returned', 'debug');

        // FIXME: works only with servlet
        //$this->log($result, 'debug');
    }
}

