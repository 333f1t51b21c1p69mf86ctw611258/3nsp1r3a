<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Controller', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('PhpReader', 'Configure');
Configure::load('parameters');
Configure::load('config');

//PHPExcel
App::import('Vendor','PHPExcel',array('file' => 'excel/phpexcel.php'));
App::import('Vendor','IOFactory',array('file' => 'excel/PHPExcel/IOFactory.php'));
App::import('Vendor','PHPExcelWriter',array('file' => 'excel/PHPExcel/Reader/Excel5.php'));
App::import('Vendor','PHPExcelWriter',array('file' => 'excel/PHPExcel/Writer/Excel5.php'));

//Testing class and function loading
if (!class_exists('PHPExcel')) {
    throw new CakeException('Vendor class PHPExcel not found!');
}

if (!method_exists('PHPExcel', 'setActiveSheetIndex')) {
   throw new CakeException('Vendor function setActiveSheetIndex not found!');
}

//excel_reader2 to be included

App::import('Vendor','php_reader',array('file' => 'excel_reader2.php'));
//require_once 'phpreader/excel_reader2.php';
//error_reporting(E_ALL ^ E_NOTICE);

if (!class_exists('Spreadsheet_Excel_Reader')) {
    throw new CakeException('Vendor class Spreadsheet_Excel_Reader not found!');
}

if (!method_exists('Spreadsheet_Excel_Reader', 'dump')) {
   throw new CakeException('Vendor function dump not found!');
}



/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

//ini_set('max_execution_time', 60);

class AppController extends Controller {

    protected $appName = 'Generic';             // FIXME: default value
    protected $pluginName;
    protected $pluginModel;
    protected $pluginModelName;
    protected $pluginDBName;
    protected $pluginMainPage;
    protected $redirectUrlToMenu;

    protected $pluginAppRoot;
    protected $pluginScriptRoot;
    protected $pluginAttachmentRoot;

    protected $readProhibited;
    protected $uploadIgnored;

	public $helpers = array('NavBar', 'BriodeForm', 'ProjParam');
    public $uses = array('User', ); 
	public $components = array(
        'Auth'=>array('authorize' => 'Controller',
 //                     'loginRedirect' => array('controller'=>'App1', 'action'=>'main_menu'),
                      'logoutRedirect' => array('controller'=>'Users', 'action'=>'login')),
        'Session',
        'SQLConnection',
        'DOMEditor', 
        'ExcelLoader',
        'JSWriter', 
        'Paginator', 
        'Export',
        'ServletLogin',
        'Security',
        'Acl',
        'ConfigService',
        'DBMapping',
        'DBAction', 
        'AttrLog',
        'Import',
        'BriodeAcl',
        'AWSSES',
        'NotificationService'
    );
    public $operations;// = array('/app1/user'=>array('read'=>'#','Dummy'));
    protected $paginate = array('limit' => 25 );

    public function openSQLconnection($dbname='genericdata'){
        $host = 'mysql';
        $username = 'root';
        $password = '';
        $database = 'genericdata';

        $link = mysqli_connect($host, $username, $password);
        $ret = mysqli_select_db($link, $database);

        return $link;
    }

    public function beforeFilter() {
		Configure::load('parameters', true);
        $this->log('beforeFilter in', 'debug');
        if (Configure::read('App.maintenance')) {
            if ($this->request->clientIp() !== '38.115.160.94'){
                $message = __('We are currently working on maintenance, please check back later.', true);
                throw new ForbiddenException($message);
                exit();
            }
        }
        $this->log('current action='.$this->action, 'debug');
        $this->ExcelLoader->setParams($this->pluginName);
        $this->DBMapping->setParams($this->pluginName);
        $this->pluginAppRoot = $_SERVER['DOCUMENT_ROOT'].DS.$this->appName.DS.
                                    'app'.DS.'Plugin'.DS.$this->pluginName;
        $this->pluginScriptRoot = $this->pluginAppRoot.DS.'Vendor'.DS.'scripts'.DS;
        $this->pluginAttachmentRoot = $this->pluginAppRoot.DS.'attachments'.DS;
        $this->pluginJSRoot = $this->pluginAppRoot.DS.'webroot'.DS.'js'.DS;
        $this->Session->write('PluginName', $this->pluginName);
        $this->Session->write('PreviousURL', $this->redirectUrlToMenu);

        $this->setViewParams();
    }

    protected function setConfigFromDict($pluginName){
        //$this->log('setConfigFromDict', 'debug');
        $this->pluginName = $this->ConfigService->
                                    get_app_plugin_name($pluginName);
        $this->pluginModelName = $this->ConfigService->
                                    get_app_plugin_model_name($pluginName);
        $this->loadModel($this->pluginName.'.'.$this->pluginModelName);
        $this->pluginModel = eval('return $this->'.$this->pluginModelName.';');

        $this->pluginDBName = $this->ConfigService->
                                    get_app_plugin_db_name($pluginName);
        $this->pluginMainPage = $this->ConfigService->
                                    get_app_plugin_main_page($pluginName);
        $this->redirectUrlToMenu = '/'.$this->pluginName.'/'.$this->pluginMainPage;
        $this->log('redirectUrl='.$this->redirectUrlToMenu, 'debug');

        $this->set('attrs', $this->ConfigService->get_app_mainview_list(
                                    $this->pluginName));
        $this->readProhibited = $this->ConfigService->get_app_db_read_prohibited(
                                    $this->pluginName);
        $this->uploadIgnored = $this->ConfigService->get_upload_ignored_column(
                                    $this->pluginName);

        $this->operations = $this->ConfigService->get_app_operations(
                                    $this->appName, $this->pluginName);
    }

    public function isAuthorized($user=null) {
        $this->log('isAuthorized,user=', 'debug');
        $this->log($user, 'debug');

        $this->setViewParams();

        $userId = $this->Auth->user('id');
        $aro = array('model'=>'User', 'foreign_key' => $userId);
        $acl_op = $this->ConfigService->get_acl_operation($this->pluginName,
                                                             $this->action);
        $acl_aco = $this->ConfigService->get_acl_aco($this->plugin,
                                                 $this->action);

        // assess ACL if the user has privilege
        //$this->log('isAuthorized:aro=', 'debug');
        //$this->log($aro, 'debug');
        //$this->log('isAuthorized:aco='.$acl_aco, 'debug');
        //$this->log('isAuthorized:op='.$acl_op, 'debug');
        if( $this->Acl->check($aro, $acl_aco, $acl_op) ){
            if( $this->DBAction->check_briode_privilege(
                        $this->pluginName,
                        $userId,
                        $this->action,
                        $this->pluginModelName,
                        $this->get_field_data('id',NULL)
                                ) ) {
                return true;
            }
        }
        
        $this->log('isAuthorized, NOT AUTHORIZED', 'debug');
        //$_SERVER['HTTP_REFERER'] = '/'.$this->pluginName.'/main_menu';

        $this->Auth->redirectUrl($this->redirectUrlToMenu);
        $this->Session->setFlash('Not authorized', 'flash_error');

        return false;
    }
    protected function get_source_id(){
        $source_id = NULL;
        if( isset($this->data['form_id']) ){
            $source_id = $this->data['form_id'];
        }else if( isset($_GET['id']) ){
            $source_id = $_GET['id'];
        }else if( isset($_POST['id']) ){
            $source_id = $_POST['id'];
        }

        return $source_id;
    }

    protected function setViewParams(){
        if( empty($this->operations) ) return;
        //if( !array_key_exists('id', $this->Auth->user) ) return;

        $userId = $this->Auth->user('id');
        if( empty($userId) ) return;

        $aro = array('model'=>'User', 'foreign_key' => $userId);
        $operations = array();
        foreach( $this->operations as $aco=>$values ){
            foreach( $values as $acl_op=>$op_array ){
                if( $this->Acl->check($aro, $aco, $acl_op) ){
                    foreach( $op_array as $i => $value ){
                        array_push($operations, $value);
                    }
                }
            }
        }

        //$this->log('setViewParams,ope=', 'debug');
        //$this->log($operations, 'debug');
        $this->set('operations', $operations);

        $this->set('pluginName', $this->pluginName);
        $this->set('pluginModelName', $this->pluginModelName);

        $appName = $this->ConfigService->get_app_navname($this->pluginName);
        $this->set('appName', $appName);
        $this->set('title_for_layout', $appName);

        $this->set('urlAndLabel', 
            $this->ConfigService->get_app_selector_url_and_label());
        $this->set('enableReport', 
            $this->ConfigService->is_report_enabled($this->pluginName));
        $this->set('admin_menu', (
            $this->ConfigService->is_admin(
                $this->pluginName, $userId
            )
        ) ? 'enable' : 'disable');
        $this->set('source_id', $this->get_source_id());
        $this->set('birt_service', $this->get_birt_service($userId));
        $this->set('tomcatBaseUrl', $this->ConfigService->get_app_base_url());
        $this->set('proj_params', $this->ConfigService->get_proj_params());

        $this->set('sessionid', session_id());
    }

    public function get_birt_service($user_id){
        $user_inst = $this->User->findById($user_id);
        $role = $user_inst['User']['usertype'];
        return $this->ServletLogin->get_birt_service($role);
    }

    private function set_attr_logs($id){
        $this->set('logs', $this->AttrLog->get_attr_logs($this->pluginName, $id));
    }

    private function _dump_exec_error($retval){
        $outStr = __('Script caused an error.');

        //foreach($retval as $line){
        //    $outStr = $outStr ."<br>" .$line;
        //}
        //foreach($retval as $line){
		//    $outStr = $outStr ."<br>" .$line;
        //}

		$this->Session->setFlash($outStr, 'flash_error');
    }

    private function _post_exec_vendorpath($retval){
        
        if(sizeof($retval)==0){
           return false;
        }

        // process python error starting CRITICAL
        $error_patterns = array(
                        "/^CRITICAL.+/"
                        );

        $errMsgs = array();
        foreach ($retval as $obj){
            foreach ($error_patterns as $pattern){
                if(preg_match($pattern, $obj)){
                    array_push($errMsgs, $obj);
                }
            }
        }

        if(sizeof($errMsgs)>0){
            $this->_dump_exec_error($errMsgs);
        }
    }

	protected function exec_in_vendorpath($command, $arg1='', $arg2='', $arg3='', $arg4=''){
		
		//print_r("**exec_in_vendorpath with ". $command );
		
		$working_dir = APP.DS.'Vendor'.DS.'scripts';
		
		$confScript = Configure::read('scripts');
        //$this->log("exec_in_vendorpath,confScript=",'debug');
        //$this->log($confScript, 'debug');
		$script_to_run = $confScript[$command];
		$script_to_run = $script_to_run. " ". $arg1. " ". $arg2. " ". $arg3. " ". $arg4;

		$this->log( $script_to_run, 'debug' );
		
		chdir( $working_dir );
		$ret = exec( $script_to_run, $retval );
		//$this->log( 'ret in exec_in_vendorpath=', 'debug' );
		//$this->log( $ret, 'debug' );

        $this->_post_exec_vendorpath($retval);
		
		return $ret;	
	}

    /////////////////////////////////////////////////
    // Excel upload
    /////////////////////////////////////////////////
    public function upload_layout(){
        // this action won't change across application
        $this->set('action', 'preview');
    }

    private function get_cols_of_table($tablename){
        $link = $this->openSQLconnection();

        $selectcols = "SELECT * FROM ".$tablename;
        $colsFromDB = mysqli_query($link, $selectcols) or die(mysqli_error($link));
        $numOfColumns = mysqli_num_fields($colsFromDB);
        $ret = array();
        while ($property = mysqli_fetch_field($colsFromDB)) {
            $colname = $property->name;
            array_push($ret, $colname);
        }
        return $ret;
    }

    private function alterAttributeTable($columns, $tables){
        $link = $this->openSQLconnection();
       
//        $existingCols = $this->get_cols_of_table($this->pluginDBName);
        $existingCols = array();

        $col_size = $this->ConfigService->get_db_col_size($this->pluginName);

        foreach($tables as $tableName => $columns){
            $colquery = '';

            if(mysqli_query($link, 'select 1 from `'.$tableName.'`')){
                //$tablename = $this->pluginDBName;
                $query = "ALTER TABLE ".$tableName.' ';
                $ADDorNULL = 'ADD';
                $bracketorNULL = '';
                $existingCols = $this->get_cols_of_table($tableName);
            }else{
                if($tableName == $this->pluginDBName){
                    //CREATE TABLE attrapp1s (id int(11) NOT NULL AUTO_INCREMENT, created_at datetime, creator_id varchar(255), updated_at datetime, updator_id varchar(255), mandatory_flag int(10), validation_flag int(10), PRIMARY KEY (  `id` ));
                    $query = "CREATE TABLE ".$tableName.' (id int(11) NOT NULL AUTO_INCREMENT, created_at datetime, creator_id varchar(255), updated_at datetime, updator_id varchar(255), mandatory_flag int(10), validation_flag int(10), ';
                }else{
                    $exploded = explode('s', $this->pluginDBName);
                    $query = "CREATE TABLE ".$tableName.' (id int(11) NOT NULL AUTO_INCREMENT, '.$exploded[0].'_id int(11), ';
                }
                $ADDorNULL = '';
                $bracketorNULL = ', PRIMARY KEY (  `id` ))';
            }
            foreach($columns as $colName => $colType){
                // if array exists, skip to next
                if( in_array($colName, $existingCols) ) continue;

                if( $colName == 'item_number'){
                    $colType = 'int';
                }
                if ($colType == "string") {
                    $colType = "varchar(".$col_size.")";
                }
                $decPattern = '/decimal/';
                if( preg_match($decPattern, $colType, $matches) ){
                    $colType = 'float';
                }
                if( strlen($colquery) != 0 ){
                    $colquery .= ', ';
                }
                $colquery .= $ADDorNULL.' '.$colName.' '.$colType;
                $checker = 1;
            }
            //$this->log('colquery: '.$colquery, 'debug');
            if(strlen($colquery) == 0){continue;}
            $query .= $colquery.$bracketorNULL;
            //$this->log('table query:'.$query, 'debug');
            if(! mysqli_query($link, $query) ){
                $this->Session->setFlash(
                        __('Table update failed, please review the table definition and retry.'),'flash_error');
                $this->redirect($this->redirectUrlToMenu);
                return;
            }
            if(strlen($ADDorNULL) == 0){
                if($tableName != $this->pluginModelName){
                    // baking attriapp__s should be the last one to set hasMany relationship
                    $this->writeModelFileBelongsTo($tableName);
                }
            }
        }
        //if all cells are included in table, attrapp__s table should have only `id` field
        $tableName = $this->pluginDBName;
        //$tableName = 'attri'.strtolower($this->pluginName.'s');
        if(!mysqli_query($link, 'select 1 from `'.$tableName.'`')){
            $query = "CREATE TABLE ".$tableName.' (id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (  `id` )) ';
            if(! mysqli_query($link, $query) ){
                $this->Session->setFlash(
                        __('Table update failed, please review the table definition and retry.'),'flash_error');
                $this->redirect($this->redirectUrlToMenu);
                return;
            }
        }
        $this->writeModelFile();
        $this->updateControllerFile();
    }

    /** 
      * add model definition in plugin controller file
      */
    private function updateControllerFile(){
        $ctrlrFileName = '../Plugin/'.$this->pluginName.'/Controller/'.$this->pluginModelName.'sController.php';
        $tableNameArray = $this->ConfigService->get_db_table($this->pluginName);
        $file_content = file_get_contents($ctrlrFileName);
       
        $updated = false; 
        foreach( $tableNameArray as $tableName ){
            $modelName = $this->getModelNameFromTableName($tableName);
            $pattern = $this->pluginName.'\.'.$modelName;
            if( preg_match("/$pattern/", $file_content) ) continue;

            // not found - add this after __ADD_MODELS_HERE__
            $pattern_next = '            \/\* __ADD_MODELS_HERE__ \*\/';
            $replace = '            \''.$this->pluginName.'.'.$modelName.'\',
'.'            /* __ADD_MODELS_HERE__ */';
            $file_content = preg_replace("/".$pattern_next."/", $replace , $file_content);
            $updated = true;
        }

        if( $updated ){
            $this->log('ctrlr_file_content='.$file_content, 'debug');
            file_put_contents($ctrlrFileName, $file_content);
        }
    }

    private function writeModelFileBelongsTo($tableName){
        //$this->log('command will be executed', 'debug');
        //$command = '../Vendor/scripts/bake.sh '.$this->pluginName.' '.$tableName.' 2>&1';
        //$this->log('command:'.$command, 'debug');
        //$result = shell_exec($command);
        //$this->log($result, 'debug');
        $modelName = $this->getModelNameFromTableName($tableName);
        $modelFileName = '../Plugin/'.$this->pluginName.'/Model/'.$modelName.'.php';
        $tableNameArray = $this->ConfigService->get_db_table($this->pluginName);
        //$this->log('tables', 'debug');
        //$this->log($tableNameArray, 'debug');
        $n_tables = count($tableNameArray) - 1;

        $tmpModelName = $this->pluginModelName;
        $tmpForeignKey = $this->getForeignKey();
        $fbody =<<<END
<?php
App::uses('DbAttrBase', 'Model');

class $modelName extends DbAttrBase{

    public \$actsAs = array('Search.Searchable');

    public \$belongsTo = array(
        '$tmpModelName' => array(
            'className' => '$tmpModelName',
            'foreignKey' => '$tmpForeignKey'
        )
    );

    public \$filterArgs = array(
        array(  'name' => 'keywords',
                'type' => 'query',
                'method' => 'filterKeyword'),
    );

    public function filterKeyword(\$data, \$field = null){
        if (empty(\$data['keywords'])) {
            return array();
        }
        return \$this->get_filter_condition(\$data, \$data['keywords']);
    }
}
END;
        //$this->log("modelName:".$modelFileName, 'debug');
        //$this->log("fbody:", 'debug');
        //$this->log($fbody, 'debug');
        file_put_contents($modelFileName, $fbody);
    }
    private function writeModelFile(){
        //$modelName = '../Plugin/'.$this->pluginName.'/Model/'.strtolower($this->pluginModelName).'.php';
        $modelFileName = '../Plugin/'.$this->pluginName.'/Model/'.$this->pluginModelName.'.php';
        $tableNameArray = $this->ConfigService->get_db_table($this->pluginName);
        //$this->log('tables', 'debug');
        //$this->log($tableNameArray, 'debug');
        $n_tables = count($tableNameArray) - 1;

        $i = 0;
        $hasMany = '    public $hasMany = array('."\n";
        foreach($tableNameArray as $index => $tableName){
            if($tableName == $this->pluginDBName){ continue; }
            // remove underscore in $tableName 
            // $tableName = Inflector::camelize($tableName);
            $modelName = $this->getModelNameFromTableName($tableName);
            $hasMany .= "        '".$modelName."' => array(\n";
            $hasMany .= "            'className' => '".$modelName."',\n";
            $hasMany .= "            'foreignKey' => '".$this->getForeignKey()."'";
            $hasMany .= "\n        )";
            if($i == ($n_tables - 1)){
                $hasMany .= "\n";
            }else{
                $hasMany .= ",\n";
            }
            $i++;
        }
        $hasMany .= "    );\n";

        //$forig = file_get_contents('../Plugin/'.$this->pluginName.'/Model/Attrapp3.php');
        $forig = file_get_contents($modelFileName);
        $exploded = explode("\n", $forig);
        $line = 1;
        $fbody = '';
        $checker = 0;
        foreach($exploded as $e){
            $this->log('line:'.$e, 'debug' );
            if (preg_match('/public \$hasMany/', $e)) {
                $this->log('hasMany included!'.$line, 'debug');
                $fbody .= $hasMany;
                $fbody .= "}";
                $checker = 1;
                break;
            } elseif (($line == (count($exploded) - 1)) && $checker == 0){
                $fbody .= $hasMany;
            }
            $fbody .= $e."\n";
            $line++;
        }

        /*$fbody = "<?php\nApp::uses('DbAttrBase', 'Model');\n\n";
        $fbody .= "class ".$this->pluginModelName." extends DbAttrBase{\n\n";
        $fbody .= $hasMany;
        $fbody .= "}";*/
        //$this->log("modelName:".$modelFileName, 'debug');
        //$this->log("fbody:", 'debug');
        //$this->log($fbody, 'debug');
        file_put_contents($modelFileName, $fbody);
    }

    private function convertFormatStringEditable($doc){
        $dom = new DOMDocument;
        $dom->loadHTML( $doc );

        $dom = $this->DOMEditor->prepareDbConfigurableTd($dom);

        return $dom->saveHTML();
    }

    private function removeNonAuthorizedFieldsInDbCache(&$dataFromDB){
        //$this->log('removeNonAuthorizedFieldsInDbCache', 'debug');
        //$this->log($dataFromDB, 'debug');
        if( !empty($this->readProhibited) ){
            foreach( $this->readProhibited as $aco => $columns ){
                // extract controller part only
                $model_name = split("/", $aco)[1];
                foreach( $columns as $column ){
                    //$this->log('unsetting '.$model_name.':'.$column, 'debug');
                    $dataFromDB[$model_name][$column] = "(hidden)";
                }
            }
        }
    }

    private function removeNonAuthorizedFields(&$initDataFromDB, $dom=NULL){
        if( !empty($this->readProhibited) ){
            $userId = $this->Auth->user('id');
            $aro = array('model'=>'User', 'foreign_key' => $userId);
            foreach( $this->readProhibited as $aco => $columns ){
                if( !empty($initDataFromDB) && !$this->Acl->check($aro, $aco, 'delete') ){
                    //$this->Security->hideColumns($initDataFromDB, $columns);
                    if( !empty($dom) ){
                        $dom = $this->DOMEditor->maskNonAuthorized($dom, $columns);
                    }
                }
            }
        }
        return $dom;
    }
    
    private function replaceFormatString($doc,$initDataFromDB=NULL,$readonlyColumns=array('*')){
        $dom = new DOMDocument;
        $dom->loadHTML($doc);
       
        $dom = $this->removeNonAuthorizedFields($initDataFromDB, $dom); 
        
        $dom = $this->DOMEditor->processDate($initDataFromDB, $dom, $readonlyColumns, $this->uploadIgnored);
        //$this->log('after processDate');

        $dom = $this->DOMEditor->processLabel($dom);
        //$this->log('after processPulldown');

        $dom = $this->DOMEditor->processInputStrings($initDataFromDB, $dom, $readonlyColumns, $this->uploadIgnored);
        //$this->log('after processInputString');

        $dom = $this->DOMEditor->processImage($dom);
        //$this->log('after processInputString');

        $this->log('replaceFormatString, calling check/radio','debug');
        $dom = $this->DOMEditor->processCheckbox($this->pluginName, $initDataFromDB, $dom, $readonlyColumns);
        $dom = $this->DOMEditor->processRadioButton($this->pluginName, $initDataFromDB, $dom, $readonlyColumns);
        $dom = $this->DOMEditor->processCombobox($this->pluginName, $initDataFromDB, $dom, $readonlyColumns);
        $dom = $this->DOMEditor->processRichText($initDataFromDB, $dom, $readonlyColumns);
        $dom = $this->DOMEditor->processRTImage($initDataFromDB, $dom, $readonlyColumns);
        
        return $dom->saveHTML();
    }

    public function cancel_preview(){
        $this->upload_layout();
    }

    private function validate_excel_upload($redirect_action){
        $redirect_to = '/'.$this->pluginName.'/'.$redirect_action;
        if( !($_POST["submit"] == "Upload"  && $_FILES['file']['name'][0] )) {
            $this->Session->setFlash(
                    __('Please choose a file to upload'),'flash_error');
            $this->redirect($redirect_to);
            return;
        }

        $info = pathinfo($_FILES['file']['name'][0]);
        //$this->log('pathinfo', 'debug');
        //$this->log($info, 'debug');
        $ext = $info['extension'];
        if (! preg_match("/xls/",$ext)) {
            $this->Session->setFlash(
                    __('Please upload an Excel file for layout.'),'flash_error');
            $this->redirect($redirect_to);
            return;
        }
    }
    public function checkFormula($redirect_action){
        $redirect_to = '/'.$this->pluginName.'/'.$redirect_action;
        $formulaMap = $this->DBMapping->getCellIdToFormulaMap();
        $colNameMap = $this->DBMapping->getCellIdToColNameMap();
        foreach($formulaMap as $cellId => $formula){
            if( isset($formulaMap[$cellId]) ){
                if( !isset($colNameMap[$cellId]) ){
                    $this->Session->setFlash(
                            __('Please set column name if you want to input formula in cell id '.$cellId.'.'),'flash_error');
                    $this->redirect($redirect_to);
                    return;
                }
            }
        }
    }


    public function preview($reedit=false) {
        set_time_limit(600);

        $this->log('preview', 'debug');
        $this->openSQLconnection();
        $attrs = $this->pluginModel->find('first');
        //$this->log('find all', 'debug');
        //$this->log($attrs, 'debug');
        if (sizeof($attrs)>0){
            $this->Session->setFlash(__('Database will be uploaded with the new Excel format.'), 'flash_warning');
        }

        if ( !$reedit ){
            $this->validate_excel_upload('upload_layout');

            $this->log("calling excel->preview()", "debug");
            $this->ExcelLoader->preview();
        
            // generate columnName to cellId mapping in redis
            $this->DBMapping->generateColNameToCellIdMap($this->pluginName);

            if(isset($_FILES['xlfile']['tmp_name'][0])){
                if(file_exists($_FILES['xlfile']['tmp_name'][0])){
                    $this->log('pathinfo formula', 'debug');
                    $info = pathinfo($_FILES['xlfile']['tmp_name'][0]);
                    $xlDataFilename = $_FILES['xlfile']['tmp_name'][0];
                }
            }
            $this->log("calling excel->preview()", "debug");
            // move uploaded file to UploadXlsFullpath
            $this->ExcelLoader->preview();
            
            // generate columnName to cellId mapping in redis
            $this->DBMapping->generateColNameToCellIdMap($this->pluginName);

            if(isset($xlDataFilename)){
                // load formula from excel file
                $this->DBMapping->importFormulaMap($this->pluginName, $xlDataFilename);
                $this->log('DBMapping->get colName to formula map', 'debug');
                $formulaMap = $this->DBMapping->getColNameToFormulaMap();
                $colNameMap = $this->DBMapping->getCellIdToColNameMap();
                // check if cellId which has formula also has colName 
                $this->checkFormula('upload_layout');
                // generate javascript
                $dirpath = $this->pluginJSRoot;
                $filename = 'FormulaFromExcel_Generated.js';
                $this->JSWriter->generateJSFormula($dirpath, $filename, $formulaMap);
            }
        }

        $doc = file_get_contents($this->ExcelLoader->getUploadPHPFullpath());
        $docAfterFormatting = $this->convertFormatStringEditable($doc);

        //$this->log('preview:ExcelLoader, setting doc');
        $this->set('doc',$docAfterFormatting);
        $enableViewAttachment = false;
        $this->set('actions', $this->DBAction->get_actions(
                $this->pluginName, 
                $this->action,
                $this->pluginModelName, 
                $this->get_field_data('id', NULL),
                $enableViewAttachment));
    }

    public function reedit_layout(){
        $this->preview($reedit=true);
    }

    public function preview_layout(){
        //$this->log('preview:ExcelLoader');

        // generate tdToExcelId map 
        list ($cellIdToValueMap, $formulaMap) = $this->DOMEditor->getCellIdToValueMap(
                                $this->ExcelLoader->getUploadPHPMapperFullpath(),
                                $this->ExcelLoader->getUploadPHPFullpath());

        // generate javascript
        $dirpath = $this->pluginJSRoot;
        $filename = 'GenericHandler_generated.js';
        $this->JSWriter->generateJavaScript($dirpath, $filename, $formulaMap);
        // get diff array and also update upload.xls
        $diff = $this->ExcelLoader->preview_layout($cellIdToValueMap);
        $this->log('preview_layout, diff=', 'debug');
        $this->log($diff, 'debug');
        if( !empty($diff) ){
            $this->set('diff1',$diff['diff1']);
            $this->set('diff2',$diff['diff2']);
        }
        // DB Mapping
        $type = 'formula';
        $this->DBMapping->generateDBMapping($this->pluginName, $this->type, $formulaMap); 

        $doc = $this->php_to_html( $this->ExcelLoader->getUploadPHPFullpath(),
                                   NULL );

        //$this->log('preview:ExcelLoader, setting doc');
        $enableViewAttachment = false;
        $this->set('doc',$doc);
        $this->set('actions', $this->DBAction->get_actions(
                $this->pluginName, 
                $this->action,
                $this->pluginModelName,
                $this->get_field_data('id', NULL),
                $enableViewAttachment));
    }

    public function upload_confirmation() {
        list($columns, $tables) = $this->ExcelLoader->addNewColumns($this->pluginName);
        $this->log('upload_confirmation', 'debug');
        $this->log($columns, 'debug');
        $this->log('tables array', 'debug');
        $this->log($tables, 'debug');
//        $this->createAttributeTable($columns);
        $this->alterAttributeTable($columns, $tables);

        $this->Session->setFlash(__('Upload new layout successfully!'), 'flash_success');
        $this->redirect($this->redirectUrlToMenu);
    }

    /////////////////////////////////////////////////
    // CRUD operations
    /////////////////////////////////////////////////
    private function setInitialRecordData($username) {
        $dataFromDB = NULL;

        $dataFromDB['creator_id'] = $username;
        $dataFromDB['created_at'] = date('Y-m-d H:i:s');

        return $dataFromDB;
    }

    private function load_xlfile($xlDataFilename){
        list ($cellIdToValueMap, $formulaMap) = $this->DOMEditor->getCellIdToValueMap(
                                        $this->ExcelLoader->getActivePHPMapperFullpath(),
                                        $this->ExcelLoader->getActivePHPFullpath()
                                    );

        $dataFromExcel = $this->ExcelLoader->getDataFromExcel($xlDataFilename, $cellIdToValueMap);
        //$this->log('data from excel:', 'debug');
        //$this->log($dataFromExcel, 'debug');

        $import_all = $this->ConfigService->import_calculated_fields($this->pluginName);
        if( !$import_all && $this->uploadIgnored ){
            foreach( $this->uploadIgnored as $c ){
                $this->log('create,unsetting column $c', 'debug');
                unset($dataFromExcel[$c]);
            }
        }
        //$this->log('create:xlfile data read=', 'debug');
        //$this->log($dataFromExcel, 'debug');

        return $dataFromExcel;
    }

    private function read_uploaded_sheet(){
        $dataFromExcel = array();

        if( array_key_exists('xlfile', $_FILES) && $_FILES['xlfile']['name'][0]!=NULL ){
            //$this->log('xlfile from user=', 'debug');
            //$this->log($_FILES['xlfile']['name'], 'debug');
            $info = pathinfo($_FILES['xlfile']['name'][0]);
            $ext = $info['extension'];
            if (! preg_match("/xls/",$ext)) {
                $this->Session->setFlash(__('Please upload an Excel file.'),'flash_error');
            }else{
                // now read the file
                $this->log("create_check: xls uploaded:". $info['basename'],'debug');

                $xlDataFilename = $_FILES['xlfile']['tmp_name'][0];
                $dataFromExcel = $this->load_xlfile( $xlDataFilename );
            }
        }

        return $dataFromExcel;
    }

    private function php_to_html($php_filename, $data_to_overwrite, $readonly_columns=NULL){
        $doc = file_get_contents($php_filename);
        return $this->replaceFormatString($doc, $data_to_overwrite, $readonly_columns);
    }

    public function create(){
        $this->log('apply', 'debug');
        $tableName = 'App1_Budget';

        //$this->log('create', 'debug');
        //$this->log($_POST, 'debug');
        //$this->log($this->data, 'debug');

        $dataFromExcel = $this->read_uploaded_sheet();

        // below is overwritten
        $username = $this->Auth->user('username');
        $dataFromDB = $this->setInitialRecordData($username);
        //$dataFromSource = array_merge($dataFromDB, $dataFromExcel);
        $dataFromSource = array_merge($dataFromDB, $dataFromExcel);
        //$this->log('dataFromSource', 'debug');
        //$this->log($dataFromSource, 'debug');

        $connection=$this->openSQLconnection();
        //$this->log('apply(), username='.$username, 'debug');
        //$this->log('apply(), workflow=', 'debug');
        //$this->log($workflow, 'debug');

        $doc = $this->php_to_html(  $this->ExcelLoader->getActivePHPFullpath(),
                                    $dataFromSource );

        // Set UserName
        //$dom = new DOMDocument;
        //$dom->loadHTML($doc);
        //$dom->getElementById("applicantid")->nodeValue = $username;
        //$doc = $dom->saveHTML();

        $this->set('doc', $doc);
        // enable choose_your_attachment
        $dataFromDB = array_merge($dataFromDB, array('choose_your_attachment' => true));

        $this->set('options', $dataFromDB);
        $enableViewAttachment = false;
        $this->set('actions', $this->DBAction->get_actions(
                $this->pluginName,
                $this->action,
                $this->pluginModelName,
                $this->get_field_data('id', NULL),
                $enableViewAttachment));
        $this->set_attr_logs( $this->get_field_data('id', NULL) );
        $this->Session->write('PreviousURL', $this->redirectUrlToMenu);
        //$this->set('workflow',$workflow);
    }

    public function upload_single_data(){
        // retain Excel input file to pass update()
        // otherwise the file will be deleted at create()
        $xl_new = '';
        if( isset($_FILES['xlfile']['tmp_name'][0]) &&
            file_exists($_FILES['xlfile']['tmp_name'][0]) ){
            $xl_tmp = $_FILES['xlfile']['tmp_name'][0];
            $xl_new = sys_get_temp_dir(). DS. uniqid('upload',true);
            copy($xl_tmp, $xl_new);
        }

        $subject_id = $this->save_at_create();
        $this->create();
        $this->forward_to_edit($subject_id, $xl_new);
        //return $subject_id;
    }

    protected function forward_to_menu(){
        $successMsg = "Action completed";
        $this->Session->setFlash(__($successMsg), 'flash_success');
        $this->redirect($this->redirectUrlToMenu);
    }

    protected function forward_to_edit($attr_id, $php_file){
        $this->log('forward_to_edit, id/xlsfile='.$attr_id.'/'.$php_file, 'debug');
        $redirectUrlToEdit = '/'.$this->pluginName.'/update?'
                                .'id='.$attr_id
                                .'&xlsfile='.urlencode($php_file);
        $this->redirect($redirectUrlToEdit);
    }

    private function get_plugin_model_id(){
        if (isset($this->data['id']) && !empty($this->data['id'])) {
            return $this->data['id'];
        }

        //get last id from db
        $link = $this->openSQLconnection();
        $select_id = "SELECT id FROM ".$this->pluginDBName;
        $resourceid = mysqli_query($link, $select_id) or die(mysqli_error($link));
        $this->log('resourceid:', 'debug');
        $this->log($resourceid, 'debug');
        while($attr_id_array = mysqli_fetch_assoc($resourceid)){
            $attr_id = $attr_id_array['id'];
        }
        if(!isset($attr_id)){
            $attr_id = 0;
        }
        $attr_id++;
        $this->log('attr_id: '.$attr_id, 'debug');

        return $attr_id;
    }

    private function save_form_data($attr_id,$override=NULL){
        $this->setAttributes($attrArray);

        $tableNameArray = $this->ConfigService->get_db_table($this->pluginName);
        $itemNumberArray = $this->ConfigService->get_db_item_number($this->pluginName);
        $foreignKey = $this->getForeignKey();

        foreach($tableNameArray as $tableName){
            $this->log('tableName: '.$tableName, 'debug');
            if(array_key_exists($tableName, $attrArray)){
                $tableArray = $attrArray[$tableName];
                //$this->log('tableArray', 'debug');
                //$this->log($tableArray, 'debug');

                if( !empty($tableArray) ){
                    $model = $this->prepareModel($tableName);
                    if(array_key_exists($tableName, $itemNumberArray)){
                        $item_number_max = $itemNumberArray[$tableName];
                        $this->log($tableName.' has items!', 'debug');
                        // get the largest id for the table
                        $selectids = "SELECT id FROM ".$tableName;
                        $mysqli_ids= mysqli_query($link, $selectids) or die(mysqli_error($link));
                        while($ids = mysqli_fetch_array($link, $mysqli_ids, MYSQL_NUM)){
                            $this->log('id:'.$ids[0], 'debug');
                            $lastId = $ids[0];
                        }
                        if(!isset($lastId)){
                            $lastId = 0;
                        }

                        for($item_number = 1; $item_number <= $item_number_max; $item_number++){ 
                            $tableArray_item[$item_number]['item_number'] = $item_number;
                            foreach($tableArray as $colName => $value){
                                $exploded = explode('__', $colName);
                                if(!isset($exploded[1])){ continue; }
                                $colName_db = $exploded[0].'__'.$exploded[1];
                                if($exploded[2] == $item_number){
                                    $this->log('item number:'.$item_number.' colName_db:'.$colName_db, 'debug');
                                    $tableArray_item[$item_number][$colName_db] = $value;
                                }
                            }
                            if($tableName != $this->pluginDBName){
                                if(isset($this->data['id'])){
                                    $this->log('set id for '.$tableName, 'debug');
                                    $tableArray_item[$item_number][$foreignKey] = $this->data['id'];
                                    // set id manually otherwise items will not be saved except the last item
                                    $tableArray_item[$item_number]['id'] = $this->getIdWhereForeignKeyMatches($foreignKey, $tableName) + $item_number - 1;
                                }else{
                                    $tableArray_item[$item_number][$foreignKey] = $attr_id;
                                    // set id manually otherwise items will not be saved except the last item
                                    $tableArray_item[$item_number]['id'] = $lastId + $item_number;
                                }
                            }
                            //$this->log('next: saving attributes', 'debug');
                            //$this->log("tableArray_item:", 'debug');
                            //$this->log($tableArray_item[$item_number], 'debug');
                            $model->save($tableArray_item[$item_number]);
                        }
                    }else{ // pluginModel Table
                        if(isset($this->data['id'])){
                            if($tableName == $this->pluginDBName){
                                $model->id = $this->data['id'];
                            }else{
                                $tableArray[$foreignKey] = $this->data['id'];
                                $tableArray['id'] = $this->getIdWhereForeignKeyMatches($foreignKey, $tableName);
                            }
                        }else{
                            $tableArray[$foreignKey] = $attr_id;
                        }
                        $this->setOverride( $tableArray, $override );
                        //$this->log('next: saving attributes', 'debug');
                        //$this->log($tableArray, 'debug');
                        $this->removeNonAuthorizedFields($tableArray);
                        $model->save($tableArray);
                    }
                }
            }
        }
    }

    private function save_at_create($override=NULL){
        $attr_id = $this->get_plugin_model_id();

        $this->save_form_data($attr_id, $override);

        $this->delete_attachment($attr_id);
        $this->save_attachment($attr_id);

        return $attr_id;
    }

    // create or update, depending on the existence of id
    public function create_check($forward=true,$override=NULL){
        $attr_id = $this->save_at_create($override);
        // attachment not saved
        if($forward){
            $this->forward_to_menu();
        }
        return $attr_id;
    }

    public function delete(){
        // FIXME delete allows POST
        $id = $this->get_field_data('id', NULL);
        $this->log('delete,id='.$id,'debug');
        if( $id ){
            $this->pluginModel->delete($id);
            $next_id_inst = $this->pluginModel->find(
                'first', array(
                    'fields' => array('MAX(id) AS next'),
                )
            );
            //$this->log($next_id_inst, 'debug');
            $next_id = intval($next_id_inst[0]['next']);
            $next_id += 1;
            $alter_sql = 'alter table '.$this->pluginDBName .' AUTO_INCREMENT = '.$next_id;
            //$this->log('alter auto_increment query:'. $alter_sql, 'debug');
            $link = $this->openSQLconnection();
            $query_ret = mysqli_query($link, $alter_sql);
            //$this->log($query_ret, 'debug');
            if (!$query_ret) {
                $this->log('alter table failed for '.$this->pluginDBName, 'error');
                // SHOULD NOT HAPPEN!
                // do nothing, just continue 
            }
            $this->AttrLog->delete_attr_logs($this->pluginName, $id);
            $successMsg = "Delete completed";
            $this->Session->setFlash(__($successMsg), 'flash_success');
        }
        $this->redirect($this->redirectUrlToMenu);
    }

    public function read(){
        $this->log('read in AppController', 'debug');

        $id = $this->get_field_data('id', NULL);

        $username = $this->Auth->user('username');
        $dataFromDB = $this->getDataFromDB($username, $id);

        //$this->log('read, dataFromDB=', 'debug');
        //$this->log($dataFromDB, 'debug');
        $connection=$this->openSQLconnection($this->genericDBname);

        $doc = $this->php_to_html( $this->ExcelLoader->getActivePHPFullpath(),
                                   $dataFromDB );

        // FIXME actions hardcoded
        $actions = $this->DBAction->get_actions(
                $this->pluginName,
                $this->action,
                $this->pluginModelName,
                $this->get_field_data('id', NULL),
                $this->is_exist_attachment($id));
        $this->set('actions', $actions);
        $this->Session->write('PreviousURL', $this->redirectUrlToMenu);
        $options = array('id'=>$id);
        // FIXME options control should be centralized
        if( strcmp($this->action,'create')!=0 ){
            if( $this->is_exist_attachment($id) ||
                in_array('export_to_excel', $actions) ){
                $options['formid_for_download'] = $id;
                $options['attachments'] = $this->get_attachment($id);
            }
        }
        $this->set('options', $options);

        $this->set_attr_logs( $this->get_field_data('id', NULL) );
        $this->set('doc',$doc);
    }

    // override gives following params
    //  array( 'action' => value );
    public function update($override=NULL){
        $this->log('update', 'debug');
        $action = $this->action;
        if( isset($override['action']) ){
            $action = $override['action'];
        }
        $id = $this->get_field_data('id', NULL);
        $xls_file = urldecode($this->get_field_data('xlsfile', NULL));
        
        /*
        if( empty($id) ){
            $this->Session->setFlash(__('ID for update not specified'), 'flash_error');
            $this->redirect($this->redirectUrlToMenu);
        }
        */

        $username = $this->Auth->user('username');
        $dataFromDB = $this->getDataFromDB($username, $id);

        $dataFromExcel = array();
        //$this->log('update, excel filename='.$xls_file, 'debug');
        if( !empty($xls_file) ){
            $this->log('update, xlfile is found, name='.$xls_file, 'debug');
            $dataFromExcel = $this->load_xlfile( $xls_file );
        }
        $dataFromSource = array_merge($dataFromDB, $dataFromExcel);

        //$this->log('update, dataFromSource=', 'debug');
        //$this->log($dataFromSource, 'debug');
        $connection=$this->openSQLconnection($this->genericDBname);

        // replace with excel file
        $doc = $this->php_to_html( $this->ExcelLoader->getActivePHPFullpath(),
                            $dataFromSource,
                            array($this->pluginDBName.'.id') );

        // FIXME actions hardcoded
        $enableViewAttachment = false;
        $actions = $this->DBAction->get_actions(
                $this->pluginName,
                $action,
                $this->pluginModelName,
                $this->get_field_data('id', NULL), 
                $enableViewAttachment);
        $this->set('actions', $actions);
        $this->Session->write('PreviousURL', $this->redirectUrlToMenu);
        $options = array('id'=>$id);
        $options = array_merge($options, array('choose_your_attachment' => true));
        // FIXME options control should be centralized
        if( $this->is_exist_attachment($id) ||
            in_array('export_to_excel', $actions) ){
            $options['formid_for_download'] = $id;
            $options['attachments'] = $this->get_attachment($id);
        }
        $this->set('options', $options);
        $this->set_attr_logs( $this->get_field_data('id', NULL) );
        $this->set('doc',$doc);
    }

    public function save(){
        $this->create_check();
    }

    public function back(){
        $this->redirect($this->Session->read('PreviousURL'));
    }
    
    /////////////////////////////////////////////////
    // Data massaging/manipulation
    /////////////////////////////////////////////////
    private function setAttributes(&$tableArray){
        //$this->log('setAttributes(), showing data', 'debug');
        //$this->log($this->data, 'debug');

        $tables = $this->ConfigService->get_db_table($this->pluginName);
        //$this->log('setAttributes tables array', 'debug');
        //$this->log($tables, 'debug');
        $itemNumberArray = $this->ConfigService->get_db_item_number($this->pluginName);
        //$this->log('setAttributes itemNumber array', 'debug');
        //$this->log($itemNumberArray, 'debug');
        foreach($tables as $tableName){
            $link = $this->openSQLconnection();
            $selectcols = "SELECT * FROM ".$tableName;
            $colsFromDB = mysqli_query($link, $selectcols) or die(mysqli_error($link));
            $numOfColumns = mysqli_num_fields($colsFromDB);
            //$this->log('setAttributes, tableName:'.$tableName.' numOfColumns:'.$numOfColumns, 'debug');
            //$this->log($colsFromDB, 'debug');

            // time and update
            if($tableName == $this->pluginDBName){
                if( array_key_exists('creator_id', $_POST) ){
                    $tableArray[$tableName]['creator_id'] = $this->get_field_data('creator_id', NULL);
                    $tableArray[$tableName]['creator_id'] = $this->get_field_data('creator_id', NULL);
                }
                if( array_key_exists('creator_at', $_POST) ){
                    $tableArray[$tableName]['created_at'] = $this->get_field_data('created_at', NULL);
                }

                $tableArray[$tableName]['updator_id'] = $this->Auth->user('username');
                $tableArray[$tableName]['updated_at'] = date('Y-m-d H:i:s');
            }
            $colNameArray = array();
            //$this->log('tableName: '.$tableName.' itemNumberArray ->', 'debug');
            //$this->log($itemNumberArray, 'debug');
            if(array_key_exists($tableName, $itemNumberArray)){
                $this->log('checker0', 'debug');
                $item_number_max = $itemNumberArray[$tableName];
                $this->log('item_number_max:'.$item_number_max, 'debug');
                for($item_number = 1; $item_number <= $item_number_max; $item_number++){
                    while ($property = mysqli_fetch_field($colsFromDB)) {
                        $colName_db = $property->name;
                        if($colName_db != 'id'
                                && $colName_db != 'item_number'
                                && $colName_db != 'attri'.strtolower($this->pluginName).'_id'){ 
                            $colName = $colName_db.'__'.$item_number;
                        }else{
                            $colName = $colName_db;
                        }
                        $this->log('mysqli_fetch_field:'.$colName, 'debug');
                        if( isset($this->data[$colName]) ){
                            $value = $this->massageDataForDB($colName, $this->data[$colName]);
                            $tableArray[$tableName][$colName] = $value;
                            $this->log('tableName:'.$tableName.' value:'.$value, 'debug');
                        }
                    }
                }
            }else{
                while ($property = mysqli_fetch_field($colsFromDB)) {
                    $colName = $property->name;
                    $this->log('mysqli_fetch_field:'.$colName, 'debug');

                    if( isset($this->data[$colName]) ){
                        $value = $this->massageDataForDB($colName, $this->data[$colName]);
                        $tableArray[$tableName][$colName] = $value;
                        $this->log('tableName:'.$tableName.' value:'.$value, 'debug');
                    }
                }
            }
        }
    }

    // override_plugin_table = array(colname=>val)  #no table info
    private function setOverride(&$pluginTableArray, $override_plugin_table){
        if( empty($override_plugin_table) ) return;
        $this->log( 'setOverride, override array=', 'debug' );
        $this->log( $override_plugin_table, 'debug' );
        if( isset($pluginTableArray['creator_id']) && 
            array_key_exists('creator_id', $override_plugin_table ) ){
            $pluginTableArray['creator_id'] = $override_plugin_table['creator_id'];
        }
    }

    private function prepareModel($tableName){
        $modelName = $this->getModelNameFromTableName($tableName);
        $this->loadModel($this->pluginName.'.'.$modelName);
        $model = $this->load_model($tableName);
        $model->useTable = $tableName;
        //$this->log('prepareModel, model=', 'debug');
        //$this->log($model, 'debug');
        //$this->log('prepareModel, pluginModel=', 'debug');
        //$this->log($this->pluginModel, 'debug');
        return $model;
    }
    private function load_model($tableName){
        $this->log('load_model', 'debug');
        $modelName = $this->getModelNameFromTableName($tableName);
        //$this->loadModel($this->pluginName.'.'.$modelName);
        return eval('return $this->'.$modelName.';');
    }
    // TODO 
    // for multitable support, make sure table name ends with 's'
    private function getModelNameFromTableName($tableName){
        $tableName = ucfirst($tableName);
        $exploded = explode('_', $tableName);
        $modelName = $tableName;
        if(isset($exploded[1])){
            $modelName = '';
            foreach($exploded as $term){
                $modelName .= ucfirst($term);
            }
        }
        // take off ending 's'
        // FIXME: this may remove 's' from a word with ending s
        if( strcmp(substr($modelName, -1),'s') == 0 ){
            $modelName = substr($modelName, 0, -1);
        }
        //$this->log('getModelNameFromTableName, modelName='.$modelName, 'debug');
        return $modelName;
    }
    private function getTableNameFromModelName($modelName){
        if($modelName == $this->pluginModelName){
            return $this->pluginDBName;
        }else{
            $tableName = Inflector::pluralize(Inflector::underscore($modelName));
            $exploded = explode('_', $tableName);
            return $tableName;
        }
    }
    private function getForeignKey(){
        $exploded = explode('s', $this->pluginDBName); //$exploded[0] = attrapp1 
        return $exploded[0]."_id";
    }
    private function getIdWhereForeignKeyMatches($foreignKey, $tableName){
        $link = $this->openSQLconnection();
        $this->log('getIdWhereForeignKeyMatches', 'debug');
        $select = "SELECT id, ".$foreignKey." FROM ".$tableName." where ".$foreignKey." = ".$this->data['id'].";";
        $mysqli_id_a= mysqli_query($link, $select) or die(mysqli_error($link));
        while($id_a = mysqli_fetch_array($link, $mysqli_id_a, MYSQL_NUM)){
            //$this->log('the first one id:'.$id_a[0].' attrapp1_id:'.$id_a[1], 'debug');
            $id = $id_a[0];
            break;
        }
        return $id;
    }

    private function convertToYMD($value){
        $date_regex = '/(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)\d\d/';
        if(preg_match($date_regex, $value)) {
            //$this->log("convertToYMD matched", 'debug');
            $newval = date("Y-m-d", strtotime($value));
            //$this->log("convertToYMD old,new=".$value.",".$newval, 'debug');
            return $newval;
        }
        return $value;
    }

    private function convertToMDY($value){
        $date_regex = '/(19|20)\d\d[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])/';
        if(preg_match($date_regex, $value)) {
            //$this->log("convertToYMD matched", 'debug');
            $newval = date("m/d/Y", strtotime($value));
            //$this->log("convertToMDY old,new=".$value.",".$newval, 'debug');
            return $newval;
        }
        return $value;
    }

    // primarily for date conversion
    private function massageDataForDB($column, $orig){
        // checkbox
        $this->log('messageDataForDB, col,orig='.$column.'/'.$orig, 'debug');
        $typesMultiOptions = array('checkbox');
        $excelType = $this->ConfigService->get_excel_type($this->pluginName, $column);
        $this->log('messageDataForDB, excelType='.$excelType, 'debug');
        if( !empty($orig) && 
            in_array($excelType, $typesMultiOptions) ){
            return implode(',', $orig);
        }

        // date
        $new = $this->convertToYMD($orig);

        return $new;
    }
    private function massageDataForUI($column, $orig){
        // checkbox
        $typesMultiOptions = array('checkbox');
        $excelType = $this->ConfigService->get_excel_type($this->pluginName, $column);
        if( !empty($orig) && 
            in_array($excelType, $typesMultiOptions) ){
            return explode(',', $orig);
        }

        // date
        $new = $this->convertToMDY($orig);

        return htmlentities($new);
    }

    private function _startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }

    private function getAttributesFromDB($id){
        $this->log('getAttributesFromDB', 'debug');
        $tableNameArray = $this->ConfigService->get_db_table($this->pluginName);
        $dataFromDBRawAttributes = $this->pluginModel->findById($id);
        //$this->log($dataFromDBRawAttributes, 'debug');
        $itemNumberArray = $this->ConfigService->get_db_item_number($this->pluginName);

        // process date conversion
        // data structure before conversion
        // [Attrapp1] => Array(     <- array $data
        //     [$key] => $value
        // )
        // [$tableName] => Array(   <- array $data
        //     [0 ($item_number) ] => Array(
        //         [id] => 1
        //         [attrapp1_id] => 17
        //         [item_number] => 1
        //         [$tablename] => Array(
        //             [0] => Array(    <- array $d[0]
        //                 [$key] => $value
        //             )
        //         )
        //     )
        //     [1 ($item_number) ] => Array(
        //         ...
        //     )
        // )
        foreach($dataFromDBRawAttributes as $modelName => $data){
            if(empty($data)){ continue; }
            $tableName = $this->getTableNameFromModelName($modelName);
            //$this->log('tableName: '.$tableName.' data->', 'debug');
            //$this->log($data, 'debug');
            //$this->log('getAttributesFromDB itemNumberArray ->', 'debug');
            //$this->log($itemNumberArray, 'debug');
            if($modelName == $this->pluginModelName){
                foreach($data as $key => $value){
                    $dataFromDBRawAttributes[$key] = $this->massageDataForUI($key, $value);
                }
            }elseif(array_key_exists($tableName, $itemNumberArray) ){
                $item_number = $itemNumberArray[$tableName];
                $this->log('tableName:'.$tableName.' item_number:'.$item_number, 'debug');
                for($i = 0; $i < $item_number; $i++){
                    $d = $data[$i][$modelName];
                    foreach($d[0] as $key => $value){
                        $dataFromDBRawAttributes[$key.'__'.($i + 1)] = $this->massageDataForUI($key, $value);
                    }
                }
            // FIXME following condition knows Workflow impl -> should be avoided
            } elseif ($this->_startsWith($modelName, 'Wf')) {
                continue;
            } else {
                $d = $data[0][$modelName];
                foreach($d[0] as $key => $value){
                    $dataFromDBRawAttributes[$key] = $this->massageDataForUI($key, $value);
                }
            }
        }
        //$this->log('after data converstion', 'debug');
        //$this->log($dataFromDBRawAttributes, 'debug');
        return $dataFromDBRawAttributes;
     }

    private function getDataFromDB($username, $id) {
        $dataFromDB = $this->getAttributesFromDB($id);

        return $dataFromDB;
    }
/*    public function get_number_of_db_table(){
        $this->log('check_number_of_table', 'debug');
        $layoutMultiTableRandomFilename = APP . DS . 'Plugin/'.$this->pluginName.'/Test/Case/Controller/'.$this->pluginName.'MultiTable.xls';
        if(file_exists($layoutMultiTableRandomFilename)){
            $this->openSQLconnection();

            $mysqlCommand = "show tables like '".strtolower($this->pluginName)."_%';";
            $this->log($mysqlCommand, 'debug');
            $result = mysqli_query($mysqlCommand) or die(mysqli_error());
            // number of table >= 1 including attrapp1s
            $n_table = 1;
            while($table = mysqli_fetch_array($result, MYSQL_ASSOC)){
                $this->log($table, 'debug');
                $n_table++;
            }
        }
        return $n_table;
    }*/

    protected function get_field_data($field, $default=NULL){
        //$this->log('get_field_data', 'debug');
        //$this->log($_POST, 'debug');

        if( array_key_exists($field, $_POST) ){
            return $_POST[$field];
        }
        //$this->log('GET======', 'debug');
        //$this->log($_GET, 'debug');
        //$e = new Exception();
        //$this->log($e->getTraceAsString(), 'debug');
        if( array_key_exists($field, $_GET) ){
            return $_GET[$field];
        }
        return $default;
    }


    /////////////////////////////////////////////////
    // Utility functions
    /////////////////////////////////////////////////
    public function paypal_ack(){
        $doc = '';
        foreach($_POST as $key=>$value ){
            $this->log($key.'/'.$value, 'debug');
            $doc .= $key. '/'. $value. ';';
        }

        $this->set('doc',$doc);
    }

    public function export(){
        //$this->log('export called', 'debug');
        ini_set('max_execution_time', 300); // 5min
        $data = $this->pluginModel->find('all', array(
            'recursive' => -1,
            'limit' => 100,
            'order' => array($this->pluginModelName.'.created_at' => 'desc')
        ));
        //$this->log('export:', 'debug');
        //$this->log($data, 'debug');
        $params = $this->Export->exportExcelInFile($this->pluginName, $data);
        //$this->log('export:fullpath='.$params['fullpath'], 'debug');
        //$this->log('export:filename='.$params['filename'], 'debug');
        $this->response->file($params['fullpath'],
            array('download'=>true, 'name'=>$params['filename']));

        return $this->response;
    }

    public function import_excel(){
        // this action won't change across application
        $this->set('action', 'do_import');
    }

    public function do_import() {
        $this->validate_excel_upload('import_excel');

        $rows = $this->Import->get_rows( $_FILES['file']['tmp_name'][0], $this->pluginModel, $this->pluginModelName );
        $this->pluginModel->saveAll( $rows );

        $this->Session->setFlash(__('Import spreadsheet successfully!'), 'flash_success');
        $this->redirect($this->redirectUrlToMenu);
    }

    // FIXME this is App25 specific
    // zero padding should be implemented as postprocessing for import
    public function import_target(){
        // this action won't change across application
        $this->set('action', 'do_import_target');
    }

    public function do_import_target() {
        $this->validate_excel_upload('import_excel');

        $rows = $this->Import->get_rows( $_FILES['file']['tmp_name'][0], $this->pluginModel, $this->pluginModelName );
        $this->pluginModel->saveAll( $rows );

        $this->exec_in_vendorpath('LoadPadding', NULL);

        $this->Session->setFlash(__('Import target successfully!'), 'flash_success');
        $this->redirect($this->redirectUrlToMenu);
    }


    /////////////////////////////////////////////////
    // AJAX call from js
    public function saveUpdate() {
        $tdId = $_POST['id'];
        $dbConfig = $_POST['config'];
        $formula = $_POST['formula'];
        $this->log('saveUpdate, id/config='.$tdId.'/'.$dbConfig, 'debug');
        
        // Update data in redis
        $this->DBMapping->saveUpdate($this->pluginName, $dbConfig, $formula);

        $phpUploadFilename = $this->ExcelLoader->getUploadPHPFullpath();
        // read currently saved php
        $doc = file_get_contents($phpUploadFilename);

        // locate specific ID from DOM tree, set and save
        $dom = new DOMDocument;
        $dom->loadHTML( $doc );
        $dom = $this->DOMEditor->updateHtmlForTd($dom, $tdId, $dbConfig, $formula);

        // update php file
        file_put_contents( $phpUploadFilename, $dom->saveHTML() );
        chmod( $phpUploadFilename, "a+w" );

    }

    /////////////////////////////////////////////////
    // List View 
    private function setUIProfiles(){
        $username = $this->Auth->user('username');
        $this->log('setUIProfiles, username='.$username, 'debug');
        $user = $this->User->findByUsername($username);
        //$this->log('setUIProfiles, user=', 'debug');
        //$this->log($user['User'], 'debug');

        $this->set('userInst', $user['User'] );
    }

    protected function get_order($modelName){
        $sortCondition = $this->ConfigService->get_app_mainview_sort_condition(
                                                    $this->pluginName);
        $sortNew = array();
        if( !empty($sortCondition) ){
            $ith = 0;
            foreach( $sortCondition['colnames'] as $colname ){
                $order = $sortCondition['orders'][$ith++];

                // NULL FIRST DESC requires two entries for the same column
                // Add space for one end of the colname to avoid duplicate
                $pattern = "is null";
                $matched_pos = strpos(strtolower($order), $pattern);
                if( $matched_pos!==false ){
                    $sortNew[ $this->pluginModelName.'.'.$colname.' ' ] = $order;
                }else{
                    $sortNew[ $this->pluginModelName.'.'.$colname ] = $order;
                }
            }
        }
            
        if( empty($sortNew) ){
            $sortNew = array($modelName.'.id'=>'desc');
        }

        return $sortNew;
    }
    protected function get_condition($actionName){
        return array(
            'conditions' => NULL,
            'order' => $this->get_order($this->pluginModelName),
        );
    }
    protected function setList(){
        $this->paginate = $this->get_condition($this->action);
        $this->Paginator->settings = $this->paginate;
        $attrs = $this->Paginator->paginate($this->pluginModel);

        $this->set('usertype', $this->Auth->user('usertype'));
        $this->set('attrDataArray', $attrs);
        $this->set('pluginName', $this->pluginName);
        $this->set('listModelName', $this->pluginModelName);
        $this->set('detail_id', 'id');
    }

    /////////////////////////////////////////////////
    // REST 
    public function index(){
       // TODO define func 
    }

    ///////////////////////////////////////////////////
    // Navbar items
    protected function save_sessionid(){
        $sess_afterlogin = session_id();
        $this->log('sessionid='.$sess_afterlogin, 'debug');

		$username = $this->Auth->user('username');
        if( empty($username) ) return false;

        $user_instance = $this->User->findByUsername($username);
        $user_role = $user_instance['User']['usertype'];

        $this->ServletLogin->cache_session_add($username, $user_role);
        return true;
    }

    private function redirect_to_birt(){
		$username = $this->Auth->user('username');
        $user_instance = $this->User->findByUsername($username);
        if( !array_key_exists('User', $user_instance) ) return;

        $user_role = $user_instance['User']['usertype'];
        $redirect_url = $this->ConfigService->get_login_redirect($user_role);

        if( $redirect_url ){
            $this->redirect($redirect_url);
        }
    }

    public function main_menu(){
        if( !$this->save_sessionid() ){
            $this->redirect('/Generic/Users/logout');
        }

        $this->setUIProfiles();

        $this->setList();

        $this->redirect_to_birt();
    }

    public function administration(){
        $this->log('administration', 'debug');
    }

    public function report_menu(){
        $this->set('pluginName', $this->pluginName);
        $this->set('birtBaseUrl', $this->ConfigService->get_app_base_url());
        $this->set('list_of_reports', $this->ConfigService->get_report_list());
    }

    ///////////////////////////////////////////////////
    // Export a single Excel SS
    public function export_to_excel(){
        $userId = $this->Auth->user('id');
        $aro = array('model'=>'User', 'foreign_key' => $userId);
        $form_id = $this->data['form_id'];

        $params = $this->export_to_excel_ex(
            $form_id,
            $this->BriodeAcl->is_readonly_user($aro, $this->pluginName)
        );

        $this->response->file($params['fullpath'],
                              array('download'=>true, 'name'=>$params['filename']));

        return $this->response;
    }

    protected function export_to_excel_ex($form_id, $readonly){
        $data = $this->pluginModel->findById($form_id);
        if( $readonly ){
            $this->removeNonAuthorizedFieldsInDbCache($data);
        }
        return $this->Export->export_to_excel(
            $this->pluginName, $data[$this->pluginModelName], $form_id);
    }


    // FIXME
    // workaround : View attachment always calls action: 'undefined'
    public function undefined(){
        $this->log('undefined','debug');
        $this->view_attachment();
    }


    ///////////////////////////////////////////////////
    // Viewer
    public function file_viewer(){
        $this->layout = false;
        if( !isset($_GET['fn']) || 
            !isset($_GET['id']) ){
            $this->Session->setFlash('File not found', 'flash_error');
            $this->redirect($this->redirectUrlToMenu);
        }
        $id = $this->get_field_data('id', NULL);
        $in_filename = APP. 'Plugin'. DS. $this->pluginName. DS.
                        'attachments'. DS. $id. DS. $_GET['fn'];
        //var_dump($in_filename);
        $im = new imagick();
        $im->setResolution(100,100);
        $pdf_obj = new imagick($in_filename);
        for( $i=0,$total_num=$pdf_obj->getNumberImages(); $i<$total_num; $i++ ){
            $im->readImage($in_filename.'['.$i.']');
        }
        $im->resetIterator();
        $combined = $im->appendImages(true);
        $combined->setImageFormat('jpg');

        $out_dir = APP. 'webroot'. DS. 'img'. DS. 'tmp';
        $out_fullpath = tempnam( $out_dir, 'fv_');
        $out_exploded = explode( DS, $out_fullpath );
        $combined->writeImage($out_fullpath);
        //var_dump($out_dir);
        //var_dump($out_fullpath);
        
        //header('Content-Type: image/jpeg');
        $this->set('outfile', end($out_exploded));
    }
    
    ///////////////////////////////////////////////////
    // Attachment
    public function view_attachment(){
        $this->log('view_attachment', 'debug');
        
        $form_id = $this->data['form_id'];
        $dir = $this->pluginAttachmentRoot.DS.$form_id.DS;
        $this->log('view_attachment,dir='.$dir, 'debug');

        $this->exec_in_vendorpath('CreateZip', $dir);
        $this->viewClass = 'Media';
        
        $params = array(
                        'id'    => 'attachment.zip',
                        'name'  => 'Id_'.$form_id.'_attachments',
                        'download'  => true,
                        'extension' => 'zip',
                        'path'  => 'Plugin'.DS.$this->pluginName.DS.'attachments'.DS.$form_id.DS
                    );
        $this->set($params);
    }
    private function delete_attachment($form_id){
        foreach($this->data as $key=>$file_to_del){
            $this->log('del_att,key/val='.$key.'/'.$file_to_del,'debug');
            if("FileToDelete_" == substr($key, 0, strlen("FileToDelete_"))){
                $this->log("deleting attachment at: ".
                        $this->pluginAttachmentRoot.DS.$form_id.DS.$file_to_del, 'debug');
                unlink($this->pluginAttachmentRoot.DS.$form_id.DS.$file_to_del);
            }
        } 
    }

    private function save_attachment($form_id){
        if ( isset($_FILES['fileToAdd']) &&
             $_FILES['fileToAdd']['error'][0]==0) { // 0 means uploaded and no error
            $count=0;
            // FIXME: permission is so permissive
            if( !file_exists($this->pluginAttachmentRoot.DS.$form_id) ){
                mkdir($this->pluginAttachmentRoot.DS.$form_id, 0777, true);
            }
            foreach ($_FILES['fileToAdd']['name'] as $filename) {
                move_uploaded_file($_FILES['fileToAdd']['tmp_name'][$count],
                    $this->pluginAttachmentRoot.DS.$form_id.DS.
                                        $_FILES['fileToAdd']['name'][$count]);
                $count++;
            }
        }
    }

    protected function get_attachment($form_id){
        if( file_exists( $this->pluginAttachmentRoot.DS.$form_id ) ){
            return scandir( $this->pluginAttachmentRoot.DS.$form_id );
        }else{
            return NULL;
        }
    }
    protected function is_exist_attachment($form_id){
        //print_r($this->pluginAttachmentRoot.DS.$form_id );
        return file_exists( $this->pluginAttachmentRoot.DS.$form_id );
    }

    // FIXME this is for Meiji project only
    // This shall be replaced with generic bulk import. 
    public function import_timesheet(){
        // this action won't change across application
        $this->set('action', 'do_import_timesheet');
    }

    private function get_year_month_from_ts($filename){
        $pattern = '/\w_([0-9]*)_([0-9]*)/';
        if( preg_match($pattern, $filename, $matched) ){
            return array( 'year'=>$matched[1],
                          'month'=>$matched[2] );
        }
        return NULL;
    }

    private function read_timesheet($org_filename, $tmp_filename, $redirect_action){
        $info = pathinfo($org_filename);
        $year_month = $this->get_year_month_from_ts( $info['filename'] );
        if( empty($year_month) ){
            $redirect_to = '/'.$this->pluginName.'/'.$redirect_action;
            $this->Session->setFlash(
                    __('Please specify year/month in filename'),'flash_error');
            $this->redirect($redirect_to);
            return;
        }
        $tsSummaryPluginName = $this->ConfigService->get_plugin_name('Timesheet Summary');
        $tsSummaryPluginModelName = $this->ConfigService->get_plugin_model_name('Timesheet Summary');
        $this->loadModel($tsSummaryPluginName.'.'.$tsSummaryPluginModelName);
        $tsSummaryPluginModel = eval('return $this->'.$tsSummaryPluginModelName.';');
        $holidayPluginName = $this->ConfigService->get_plugin_name('Holiday Setting');
        $holidayPluginModelName = $this->ConfigService->get_plugin_model_name('Holiday Setting');
        $this->loadModel($holidayPluginName.'.'.$holidayPluginModelName);
        $holidayPluginModel = eval('return $this->'.$holidayPluginModelName.';');
        $handlerPluginName = $this->ConfigService->get_plugin_name('Handler Setting');
        $handlerPluginModelName = $this->ConfigService->get_plugin_model_name('Handler Setting');
        $this->loadModel($handlerPluginName.'.'.$handlerPluginModelName);
        $handlerPluginModel = eval('return $this->'.$handlerPluginModelName.';');

        return $this->Import->get_timesheet_rows( 
                        $this->pluginModel,
                        $this->pluginModelName,
                        $tsSummaryPluginModel,
                        $tsSummaryPluginModelName,
                        $holidayPluginModel,
                        $holidayPluginModelName,
                        $handlerPluginModel,
                        $handlerPluginModelName,
                        $tmp_filename,
                        $year_month );
    }

    public function do_import_timesheet() {
        $redirect_action = 'import_timesheet';
        $this->validate_excel_upload($redirect_action);

        // by now we know correct excel file was loaded
        $org_filename = $_FILES['file']['name'][0];
        $tmp_filename = $_FILES['file']['tmp_name'][0];
        $days_months_rows = $this->read_timesheet($org_filename, $tmp_filename, $redirect_action);
        $this->pluginModel->saveAll( $days_months_rows['days'] );

        $tsSummaryPluginName = $this->ConfigService->get_plugin_name('Timesheet Summary');
        $tsSummaryPluginModelName = $this->ConfigService->get_plugin_model_name('Timesheet Summary');
        $this->loadModel($tsSummaryPluginName.'.'.$tsSummaryPluginModelName);
        $tsSummaryPluginModel = eval('return $this->'.$tsSummaryPluginModelName.';');
        // generate Monthly report based on daily timesheet
        $tsSummaryPluginModel->saveAll( $days_months_rows['months'] );
        
        $this->Session->setFlash(__('Import timesheet successfully!'), 'flash_success');
        $this->redirect($this->redirectUrlToMenu);
    }

    public function init_timesheet_failed(){
        $this->Session->setFlash(__('Import timesheet failed!'), 'flash_error');
        $this->redirect($this->redirectUrlToMenu);
    }

    public function init_timesheet(){
        set_time_limit(600);

        $tsPluginName = $this->ConfigService->get_plugin_name('Timesheet');
        $tsPluginModelName = $this->ConfigService->get_plugin_model_name('Timesheet');

        // get list of timesheets in APP
		$working_dir = APP.DS.'Vendor'.DS.'projects'.DS.'data';
        $ts_files = scandir($working_dir);

        $tsSummaryPluginName = $this->ConfigService->get_plugin_name('Timesheet Summary');
        $tsSummaryPluginModelName = $this->ConfigService->get_plugin_model_name('Timesheet Summary');
        $this->loadModel($tsSummaryPluginName.'.'.$tsSummaryPluginModelName);
        $tsSummaryPluginModel = eval('return $this->'.$tsSummaryPluginModelName.';');

        $ts_pattern = '/'.$tsPluginName.'_ts_[\d]*_[\d]*\.xlsx/';
        $redirect_action = 'init_timesheet_failed';
        foreach( $ts_files as $f ){
            if( preg_match($ts_pattern, $f) ){
                $f_fullpath = $working_dir.DS.$f;
                $days_months_rows = $this->read_timesheet($f_fullpath, $f_fullpath, $redirect_action);
                $this->pluginModel->saveAll( $days_months_rows['days'] );
                $tsSummaryPluginModel->saveAll( $days_months_rows['months'] );
            }
        }
 
        $this->Session->setFlash(__('Import timesheet successfully!'), 'flash_success');
        $this->redirect($this->redirectUrlToMenu);
    }

    /*
     * input: POST request with following params
     *    table: name of the table
     *    column: name of the column
     *    constraint: parameter given to 'colname like XXX'
     * output: JSON
     *    table:
     *    column:
     *    data:
     */
    public function fetch_column_val_list(){
        $link = $this->SQLConnection->openSQLconnection();
        //$this->log('fetch_column_val_list in:', 'debug');
        //$this->log('fetch_column_val_list post:', 'debug');
        //$this->log($_POST, 'debug');
        $ret_data = array();
        
        // return NULL when no parameter is given
        if( !isset($_POST['table']) || !isset($_POST['column']) ){
            return new CakeResponse(array('type'=>'json',
                                          'body'=>json_encode($ret_data)));
        }
        $table_name = $_POST['table'];
        $column_name = $_POST['column'];
        $constraint = '1=1';
        if( isset($_POST['constraint']) && !empty($_POST['constraint'])){
            $constraint = $_POST['constraint'];
            $constraint = $column_name. " LIKE '". $constraint. "'";
        }
        $sql_query = 'select '.$column_name. ' from '.$table_name. ' where '.$constraint;
        //$this->log('fetch_table query:'.$sql_query, 'debug');
        $result = mysqli_query($link, $sql_query) or die(mysqli_error($link));
        $row_data = array();
        while($row = mysqli_fetch_array($result, MYSQLI_NUM)){
            array_push($row_data, $row[0]);
        }
        $ret_data['table'] = $table_name;
        $ret_data['column'] = $column_name;
        $ret_data['data'] = $row_data;

        //$this->log( json_encode($ret_data), 'debug' );
        //$this->log('fetch_table data:', 'debug');
        //$this->log($ret_data, 'debug');
        return new CakeResponse(array('type'=>'json',
                                      'body'=>json_encode($ret_data)));
    
    }
    /*
     * input: POST request with following params
     *    table: name of the table
     *    keyColumn: name of the key column
     *    keyConstraint: parameter given to 'keyColumn is XXX'
     *    sourceCssid: cssid that triggers this request
     * output: JSON
     *    table:
     *    column:
     *    data:
     */
    public function fetch_one_record(){
        $link = $this->SQLConnection->openSQLconnection();
        $this->log('fetch_one_record in:', 'debug');
        $ret_data = array();
        
        // return NULL when no parameter is given
        if( !isset($_POST['table']) || !isset($_POST['keyColumn']) || !isset($_POST['keyConstraint']) ){
            return new CakeResponse(array('type'=>'json',
                                          'body'=>json_encode($ret_data)));
        }
        $source_cssid = $_POST['sourceCssid'];
        $table_name = $_POST['table'];
        $key_column_name = $_POST['keyColumn'];
        $key_column_value = $_POST['keyConstraint'];
        $constraint = $key_column_name. "='". $key_column_value. "'";
        $sql_query = 'select * from '.$table_name. ' where '.$constraint;
        //$this->log('fetch_table query:'.$sql_query, 'debug');
        $result = mysqli_query($link, $sql_query) or die(mysqli_error($link));
        $row_data = array();
        while($row_data = mysqli_fetch_array($result, MYSQLI_ASSOC)){
            // only one row is required
            break;
        }
        $ret_data['source_cssid'] = $source_cssid;
        $ret_data['key_col_name'] = $key_column_name;
        $ret_data['key_col_value'] = $key_column_value;
        $ret_data['data'] = $row_data;

        //$this->log( json_encode($ret_data), 'debug' );
        //$this->log('fetch_one_record data:', 'debug');
        //$this->log($ret_data, 'debug');
        return new CakeResponse(array('type'=>'json',
                                      'body'=>json_encode($ret_data)));
    } 

    /////////////////////////////////////////////////
    // Notification Service Callback
    /////////////////////////////////////////////////
    private function setNotificationEmail($updateEvent){
        if( !$this->ConfigService->is_notification_email_enabled($this->pluginName) ){
            return;
        }
        // FIXME
        // only external url is meainingful
        // FIXME
        // workflow used in App - should be avoided (BriodeCore uses workflow)
        //$label_and_urls = $this->ConfigService->get_workflow_email_urls(
        $label_and_urls = $this->ConfigService->get_notification_email_urls(
                                                        $this->pluginName);
        $subject_id = $updateEvent['id'];
        $url = $label_and_urls['external_baseurl']
                            .'/'
                            .$this->pluginName
                            .'/read?id='.$subject_id;
        // FIXME list log event's attrs manually
        $header = array('updated_at', 'updator_id', 'additional_text');
        $header = array_merge(array('url'), $header);
        $updateEvent['url'] = $url;

        return array('header'=>$header, 'map'=>$updateEvent);
    }

    public function sendUpdateNotification($data){
        $this->log('sendUpdateNotification', 'debug');
        $email_content = $this->setNotificationEmail($data);

        // FIXME read this from parameter
        $recipient_email = $this->ConfigService->get_notification_email_address($this->pluginName);
        $template_name = 'notifyUpdate';
        if( !empty($recipient_email) ){
            $url_msg = '';
            $params = array();
            $params = array('recipient' => $recipient_email, 'contents'=>$email_content);
            $this->log('sendUpdateNotification, sending email to '.$recipient_email, 'debug');
            $this->AWSSES->to = $recipient_email;
            if ($this->AWSSES->_aws_ses($template_name, $params)) {
                // succeed
                // TODO count failure and make registration complete
            }
        }
    }
}
