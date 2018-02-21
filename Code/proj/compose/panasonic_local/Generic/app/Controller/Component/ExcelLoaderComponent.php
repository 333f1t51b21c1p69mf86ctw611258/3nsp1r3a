<?php
App::uses('Component', 'Controller');
App::uses('PhpReader', 'Configure');

class ExcelLoaderComponent extends Component {

    // line below causes an error in AWS
    // public function startup($controller){ $this->controller = $controller;}

    public $components = array('ConfigService');

    private $pluginName;
    private $myconfig;
    private $supportedFormats;
    private $activeSheet = null;
 
    public function setParams($pluginName){
        $appName = "Generic";
        $this->pluginName = $pluginName;
        $this->supportedFormats = array('input', 'date', 'pulldown', 'radio', 'checkbox', 'combobox', 'richtext', 'rtimage');
        $this->myconfig = NULL;

        $uploadRoot = $_SERVER['DOCUMENT_ROOT'].DS.$appName.DS.'app';
        if( $pluginName != NULL ){
            $uploadRoot .= DS.'Plugin'.DS. $pluginName;
        }
        $uploadRoot .= DS. 'uploads';

        $uploadFilebody = 'upload';
        $activeFilebody = 'active';
        $this->myconfig = array(
            'uploadpath' => $uploadRoot,

            'uploadFilenameBody' => $uploadFilebody,
            'uploadXlsFullpath' => $uploadRoot.DS.$uploadFilebody.'.xls',
            'uploadPHPFullpath' => $uploadRoot.DS.$uploadFilebody.'.php',
            'uploadPHPMapperFullpath' => $uploadRoot.DS.$uploadFilebody.'Mapper.php',

            'activeFilenameBody' => $activeFilebody,
            'activeXlsFullpath' => $uploadRoot.DS.$activeFilebody.'.xls',
            'activePHPFullpath' => $uploadRoot.DS.$activeFilebody.'.php',
            'activePHPMapperFullpath' => $uploadRoot.DS.$activeFilebody.'Mapper.php',

            'userListExcelFileFullpath' => $uploadRoot.DS.$uploadFilebody.'Userlist.xlsx',
        );
    }

    public function getUploadPHPFullpath(){ return $this->myconfig['uploadPHPFullpath'];}
    public function getUploadPHPMapperFullpath(){ return $this->myconfig['uploadPHPMapperFullpath'];}
    public function getUploadRoot(){ return $this->myconfig['uploadpath'];}
    public function getUploadXlsFullpath(){ return $this->myconfig['uploadXlsFullpath'];}
    public function getActivePHPFullpath(){ return $this->myconfig['activePHPFullpath'];}
    public function getActivePHPMapperFullpath(){ return $this->myconfig['activePHPMapperFullpath'];}
    public function getActiveXlsFullpath(){ return $this->myconfig['activeXlsFullpath'];}
    public function getPluginName(){ return $this->pluginName; }
    private function nextColumn($col){ $tmp=$col; $tmp++; return $tmp; }

    private function addImage($activeSheet){
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Company logo');
        $objDrawing->setDescription('Company logo');
        $objDrawing->setPath('../../img/meiji_logo.png',false);
        $objDrawing->setHeight(36);
        $objDrawing->setCoordinates('A1'); 
        $objDrawing->setOffsetX(10); 
        $objDrawing->setWorksheet($activeSheet);
    }

    private function getActiveSheet($objPHPExcel)
    {
        if (empty($this->activeSheet)) {
            $this->activeSheet = $objPHPExcel->getActiveSheet();
        }
        return $this->activeSheet;
    }

    private function getColumnNamesAndType($objPHPExcel)
    {
        $activeSheet = $this->getActiveSheet($objPHPExcel);

        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $columns = array();
        //$columnNames = array();
        //$columnTypes = array();

        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                //getting as an excel with all the formatting and colors
                $cellVal = $activeSheet->getCell("$j$i");
                $cellVal = PHPExcel_Shared_String::SanitizeUTF8($cellVal);
                $exploded = explode(':',$cellVal);
                if ( in_array( $exploded[0], $this->supportedFormats ) && !empty($exploded[1])){
                    //$this->log('excel read:$exploded', 'debug');
                    //$this->log($exploded, 'debug');
                    $colNameRaw = $exploded[3];
                    $colTypeRaw = $exploded[0];

                    // for pulldown, set type as string
                    if( strcmp($colTypeRaw, 'pulldown')==0 ||
                        strcmp($colTypeRaw, 'radio')==0 ||
                        strcmp($colTypeRaw, 'checkbox')==0 ||
                        strcmp($colTypeRaw, 'combobox')==0 ||
                        strcmp($colTypeRaw, 'richtext')==0 ||
                        strcmp($colTypeRaw, 'rtimage')==0 ){ 
                        $colType = 'string';
                    }
                    else{
                        $colType = $exploded[2];
                        if( $colType == 'currency' ){
                            $colType = 'double';
                        }
                    }

                    // remove trailing (\d+)
                    //  this is going to be the source of alter table
                    preg_match('/\(\d+\)/', $colNameRaw, $match);
                    if( $match ){
                        $colName = str_replace($match[0], '', $colNameRaw);
                    }else{
                        $colName = $colNameRaw;
                    }
                    $columns[$colName] = $colType;

                    $this->ConfigService->set_db_schema($this->pluginName, $colName, $colType);
                    $this->ConfigService->set_excel_schema($this->pluginName, $colNameRaw, $colTypeRaw);
                }
            }
        }
        return $columns;
    } 
   
    private function getColumnNamesAndTypeForFile($excelfile){
        $objPHPExcel = $this->getPHPExcelInstance($excelfile);
 
        $columns = $this->getColumnNamesAndType($objPHPExcel);
        $this->log('columns to create:', 'debug');
        $this->log($columns,'debug');

        return $columns;
    }

    private function getTable($objPHPExcel, $pluginName)
    {
        $this->log('getTabel', 'debug');
        $activeSheet = $this->getActiveSheet($objPHPExcel);
        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $tables = array();
        $pluginModelName = $this->ConfigService->get_app_plugin_db_name($pluginName); 
        $tableNames = array($pluginModelName => $pluginModelName);
        $item_number_max = array();
        //$columnTypes = array();

        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                //getting as an excel with all the formatting and colors
                $cellVal = $activeSheet->getCell("$j$i");
                $cellVal = PHPExcel_Shared_String::SanitizeUTF8($cellVal);
                $exploded = explode(':',$cellVal);
                if ( in_array( $exploded[0], $this->supportedFormats ) && !empty($exploded[1])){
                    //$this->log('excel read:$exploded', 'debug');
                    $this->log($exploded, 'debug');
                    $NameElementRaw = $exploded[1];
                    $colTypeRaw = $exploded[0];
                    $exploded__ = explode('__',$exploded[1]);
                    list($tableNameRaw, $colNameRaw, $tableName, $colName, $item_number) = $this->getTableAndColName($pluginName, $exploded[1]);
                    $columnNames[$colName] = $tableName;
                    $this->log('item_number:'.$item_number[$tableName].' '.$tableName, 'debug');
                    if(array_key_exists($tableName, $item_number)){
                        $this->log('item_number exists:'.$item_number[$tableName].' '.$tableName);
                        if(array_key_exists($tableName, $item_number_max)){
                            if($item_number_max[$tableName] < $item_number[$tableName]){
                                $item_number_max[$tableName] = $item_number[$tableName]; 
                                $this->log('item_number_max:'.$item_number_max[$tableName].' '.$tableName);
                            }
                        }else{
                            $item_number_max[$tableName] = $item_number[$tableName];
                        }
                    }
                    // for pulldown, set type as string
                    if( strcmp($colTypeRaw, 'pulldown')==0 ||
                        strcmp($colTypeRaw, 'radio')==0 ||
                        strcmp($colTypeRaw, 'checkbox')==0 ||
                        strcmp($colTypeRaw, 'combobox')==0 ||
                        strcmp($colTypeRaw, 'richtext')==0 ||
                        strcmp($colTypeRaw, 'rtimage')==0 ){ 
                        $colType = 'string';
                    }
                    else{
                        $colType = $exploded[2];
                        if( $colType == 'currency' ){
                            $colType = 'double';
                        }
                    }

                    if(!array_key_exists($tableName, $tables)){
                        $tables[$tableName] = array($colName => $colType);
                    }else{
                        $tables[$tableName][$colName] = $colType;
                    }

                    foreach($tableNames as $Name){
                        //$this->log('Name:'.$Name.' tableName:',$tableName, 'debug');
                        if($Name != $tableName){
                            $tableNames[$tableName] = $tableName;
                        }
                    }
                }
            }
        }
        foreach($tableNames as $tableName){
            $this->log('get item number tableName:'.$tableName, 'debug');
            if( isset($item_number_max[$tableName]) && strlen($item_number_max[$tableName]) != 0){
                $this->log('item_number_max exists: '.$item_number_max[$tableName], 'debug');
                $tables[$tableName]['item_number'] = $item_number_max[$tableName];
            }
        }
        $this->log('getTable tableNames array', 'debug');
        $this->log($tableNames, 'debug');
        $this->ConfigService->set_db_table($this->pluginName, $tableNames);
        $this->log('getTable tables array', 'debug');
        $this->log($tables, 'debug');
        $this->log('getTable item_number_max array', 'debug');
        $this->log($item_number_max, 'debug');
        $this->ConfigService->set_db_item_number($this->pluginName, $item_number_max);
        $this->log('getTable db_colName array', 'debug');
        if( !empty($colunmNames) ){
            $this->log($columnNames, 'debug');
            $this->ConfigService->set_db_col_schema($this->pluginName, $columnNames);
        }
        return $tables;
    }

    public function getTableAndColName($pluginName, $nameElement){
        $this->log('getTableAndColName', 'debug');
        $exploded__ = explode('__',$nameElement);
        $item_number = array();
        if(!empty($exploded__[2]) && is_numeric($exploded__[2])){
            // case1: $NameElementRaw = tableName__colName__itemNumber
            // e.g. Budget__1_Rep__1
            $colNameRaw = $exploded__[1];
            $tableNameRaw = $exploded__[0];
            $tableName = $this->remove_trailing($tableNameRaw);
            $colName = $tableName.'__'.$this->remove_trailing($colNameRaw);
            $tableName = $this->addPluginName($tableName, $pluginName);
            $item_number[$tableName] = $exploded__[2];
        }elseif(!empty($exploded__[1])){
            // case2: $NameElementRaw = tableName__colName
            // e.g. Budget__Rep --> tableName = Budget, colName = Rep;
            $colNameRaw = $exploded__[1];
            $tableNameRaw = $exploded__[0];
            $tableName = $this->remove_trailing($tableNameRaw);
            $colName = $tableName.'__'.$this->remove_trailing($colNameRaw);
            $tableName = $this->addPluginName($tableName, $pluginName);
            $item_number[$tableName] = '';
        }else{
            // case3: $NameElementRaw = colName
            // e.g. Month --> tableName = attrapp1s;
            $colNameRaw = $nameElement;
            $tableNameRaw = $this->ConfigService->get_app_plugin_db_name($pluginName);
            //$tableNameRaw = 'attri'.strtolower($pluginName).'s';
            $tableName = $this->remove_trailing($tableNameRaw);
            $colName = $this->remove_trailing($colNameRaw);
            $item_number[$tableName] = '';
        }
        $this->log('columnNames array:'.$colName.' => '.$tableName, 'debug');
        return array($tableNameRaw, $colNameRaw, $tableName, $colName, $item_number);
    }

    private function remove_trailing($value){
        // remove trailing (\d+)
        preg_match('/\(\d+\)/', $value, $match);
        if( $match ){
            $value = str_replace($match[0], '', $value);
        }
        return $value;
    }

    public function addPluginName($tableName, $pluginName){
        if($tableName != $this->ConfigService->get_app_plugin_db_name($pluginName)){
            //$tableName = $pluginName.'_'.$tableName;
            $tableName = strtolower($pluginName).'_'.$tableName;
        }
        return $tableName;
    }

    private function adjustColWidth(){
        $doc = file_get_contents($this->myconfig['uploadPHPFullpath']);
        $pattern = "|<col style=\"width:([0-9\.]*)pt\">|U";
        $convMap = array();
        $numMatched = preg_match_all($pattern, $doc, $matches, PREG_SET_ORDER);
        for( $i=0;$i<$numMatched;$i++ ){
            // FIXME 1.2 is hardcoded
            $key = $matches[$i][0];
            $newWidth = floatval($matches[$i][1])*3.0;
            // echo 'key/orig:'.$key.'/'.$matches[$i][1].'='.$newWidth.', ';
            $convMap[$key] = '<col style="width:'.$newWidth.'pt">';
        }
        //$this->log('adjustColWidth, convMap=', 'debug');
        //$this->log($convMap, 'debug');
        foreach( $convMap as $old=>$new ){
            $doc = str_replace($old,$new,$doc);
        }
        file_put_contents($this->myconfig['uploadPHPFullpath'], $doc);
    }

    private function adjustColHeight(){
        $doc = file_get_contents($this->myconfig['uploadPHPFullpath']);
        $pattern = "| width:([0-9\.]*)pt; height:[0-9\.]*pt\">|U";
        $convMap = array();
        $numMatched = preg_match_all($pattern, $doc, $matches, PREG_SET_ORDER);
        for( $i=0;$i<$numMatched;$i++ ){
            // FIXME 3.0 is hardcoded
            $key = $matches[$i][0];
            $newWidth = floatval($matches[$i][1])*3.0;
            // echo 'key/orig:'.$key.'/'.$matches[$i][1].'='.$newWidth.', ';
            $convMap[$key] = '| width:'.$matches[$i][1].'pt;">';
        }
        //$this->log('adjustColWidth, convMap=', 'debug');
        //$this->log($convMap, 'debug');
        foreach( $convMap as $old=>$new ){
            $doc = str_replace($old,$new,$doc);
        }
        file_put_contents($this->myconfig['uploadPHPFullpath'], $doc);
    }

    private function convertXLStoPHP($excelfile) {
        $objPHPExcel = $this->getPHPExcelInstance($excelfile);
        PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
        $objWriter->setUseInlineCSS(true);
        $objWriter->save($this->myconfig['uploadPHPFullpath']);
        $this->adjustColWidth();
        $this->adjustColHeight();

        $objPHPExcelMapper = $this->saveHtmlWithCellId($excelfile);
        $objWriterMapper = PHPExcel_IOFactory::createWriter($objPHPExcelMapper, 'HTML');
        $objWriterMapper->setUseInlineCSS(true);
        $objWriterMapper->save($this->myconfig['uploadPHPMapperFullpath']);
    }

    private function getPHPExcelInstance($XLfile) 
    {
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $objPHPExcel = PHPExcel_IOFactory::load($XLfile);
        $activeSheet = $this->getActiveSheet($objPHPExcel);

        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $activeSheet->getStyle("A1:$highestColumn$highestRow")
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //showing some possible processes that can be made
        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                //eliminating spaces before and after 'input:'
                $curCell = $activeSheet->getCell("$j$i");
                if ($curCell == 'input: ' or $curCell == ' input') {
                    $activeSheet->SetCellValue("$j$i", 'input:');
                }
            }
        }
        //$this->addImage($objPHPExcel->getActiveSheet());
        return $objPHPExcel;
    }

    private function saveHtmlWithCellId($XLfile) 
    {
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $objPHPExcel = PHPExcel_IOFactory::load($XLfile);
        $activeSheet = $this->getActiveSheet($objPHPExcel);

        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $activeSheet->getStyle("A1:$highestColumn$highestRow")
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //showing some possible processes that can be made
        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; ++$i) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                $activeSheet->SetCellValue("$j$i", "$j$i");
            }
        }
        //$this->addImage($objPHPExcel->getActiveSheet());
        return $objPHPExcel;
    }

    public function addNewColumns($pluginName){
        $path = $this->myconfig['uploadpath'];
        $d = dir($path);
        while ($entry = $d->read()) {
            $matchStr = "/".$this->myconfig['uploadFilenameBody'].".xls\b/";
            if (preg_match($matchStr,$entry)) {
                $uploadxls = $entry;
                $uploadfilepath = "{$path}".'/'."{$entry}";
            }
        }
        $columns = $this->getColumnNamesAndTypeForFile($uploadfilepath);
        $objPHPExcel = $this->getPHPExcelInstance($uploadfilepath);
        $tables = $this->getTable($objPHPExcel, $pluginName);
        $this->from_upload_layout();
        return array($columns, $tables);
    }

    private function from_upload_layout(){
        $beforeAfterMap = array(
            $this->myconfig['uploadXlsFullpath']=>$this->myconfig['activeXlsFullpath'],
            $this->myconfig['uploadPHPFullpath']=>$this->myconfig['activePHPFullpath'],
            $this->myconfig['uploadPHPMapperFullpath']=>$this->myconfig['activePHPMapperFullpath'],
        );

        foreach( $beforeAfterMap as $before=>$after){
            if ( file_exists($before)) {
                rename($before, $after);
            }
        }
    }

    private function getColumnDiff(){
        $path = $this->myconfig['uploadpath'];
        $this->log('uploadpath='.$path, 'debug');
        $d = dir($path);
        while ($entry = $d->read()) {
            $uploadxls = $entry;
            //$uploadfilepath = "{$path}{$entry}";
            if (preg_match("/".$this->myconfig['activeFilenameBody'].".xls\b/",$entry)) {
                $activexls = $entry;
                //$activefilepath = "{$path}{$entry}";
            }
        }

        if (isset($activexls)) {
            //Returns the column names of OLD sheet.
            // Also saves upload.php. This file will later be overwritten.
            $oldColumns = $this->getColumnNamesAndTypeForFile($this->myconfig['activeXlsFullpath']);
        }

        //Returns the column names of NEW sheet.
        // Here we overwrite upload.xls and make it up to date!
        $diffArray = array();
        $latestColumns = $this->getColumnNamesAndTypeForFile($this->myconfig['uploadXlsFullpath']);
        if (isset($oldColumns)) {
            $this->log('Excel old columns:', 'debug');
            $this->log($oldColumns, 'debug');
            $this->log('Excel new columns:', 'debug');
            $this->log($latestColumns, 'debug');
            $diff1=array_diff(array_keys($oldColumns), array_keys($latestColumns));
            $diff2=array_diff(array_keys($latestColumns), array_keys($oldColumns));

            $diffArray['diff1'] = $diff1;
            $diffArray['diff2'] = $diff2;
        }
        $this->log('Excel returning diff:', 'debug');
        $this->log($diffArray, 'debug');
        return $diffArray;
    }

    public function preview(){
        //saving upload.xls
        move_uploaded_file( $_FILES["file"]["tmp_name"][0], $this->myconfig['uploadXlsFullpath']);
        $this->log( pathinfo($_FILES["file"]["tmp_name"][0]), 'debug');
        $this->convertXLStoPHP($this->myconfig['uploadXlsFullpath']);
    }

    public function preview_layout($cellIdToValueMap)
    {
        $phpFilename = $this->myconfig['uploadPHPFullpath'];
        $this->log('ExcelLoader, preview_layout map:', 'debug');
        $this->log($cellIdToValueMap, 'debug');

        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        // save to Excel with embedded format
        $XLfile = $this->myconfig['uploadXlsFullpath'];
        $objPHPExcel = PHPExcel_IOFactory::load($XLfile);
        $activeSheet = $this->getActiveSheet($objPHPExcel);

        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $activeSheet->getStyle("A1:$highestColumn$highestRow")
                    ->getAlignment()
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                $cellId = "$j$i";
                if( !array_key_exists( $cellId, $cellIdToValueMap ) ){
                    continue;
                }
                $cellValue = $cellIdToValueMap[$cellId];
                $activeSheet->setCellValue($cellId, $cellValue);
                //$this->log('ExcelLoader,preview_layout, setting cell:'.$cellId.'='.$cellValue, 'debug');
                //$afterSave = $objPHPExcel->getActiveSheet()->getCell($cellId);
                //$this->log('ExcelLoader,preview_layout, after save:'.$afterSave, 'debug');
            }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($XLfile);

        //Returns the column names of NEW sheet.
        // Here we overwrite upload.xls and make it up to date!
        $diffArray = $this->getColumnDiff();

        return $diffArray;
    }

    private function preprocUserValue($column, $value){
        // if column is usertype, preprocess it
        $retval = $value;
        if( strcmp($column, 'usertype')==0 ){
            $userType = 4; // default is employee
            if( strcmp($value, 'senior manager')==0 ){
                $userType = 2;
            }else if( strcmp($value, 'employee')==0 ){
                $userType = 4;
            }else if( strcmp($value, 'administrator')==0 ){
                $userType = 1;
            }else if( strcmp($value, 'manager')==0 ){
                $userType = 3;
            }
            $retval = $userType;
        }
        return $retval;
    } 

    public function getUserList($userListExcelFile)
    {
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $objPHPExcel = PHPExcel_IOFactory::load($userListExcelFile);
        $activeSheet = $this->getActiveSheet($objPHPExcel);

        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $activeSheet->getStyle("A1:$highestColumn$highestRow")
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $usersColumns = array();
        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i < 2; $i++) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                $cellVal = $activeSheet->getCell("$j$i")->getCalculatedValue();
                #$cellVal = PHPExcel_Shared_String::SanitizeUTF8($cellVal);
                $usersColumns[$j] = strtolower($cellVal);
            }
        }
        $this->log('ExcelLoader, userList reader, usersColumns=', 'debug');
        $this->log($usersColumns, 'debug');
       
        $usersList = array();
        //showing some possible processes that can be made
        for ($i=2; $i <= $highestRow; $i++) {
            $aUserToAdd = array();
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                $cellVal = $activeSheet->getCell("$j$i")->getCalculatedValue();
                #$cellVal = PHPExcel_Shared_String::SanitizeUTF8($cellVal);
                $columnName = $usersColumns[$j];
                $this->log('ExcelLoader, userList reader, cell/columnName='.$cellVal.'/'.$columnName, 'debug');
                $aUserToAdd[$columnName] = $this->preprocUserValue($columnName, $cellVal);
            }
            $userData['User'] = $aUserToAdd;
            array_push($usersList, $userData);
        }
        return $usersList;
    }

    // FIXME: this is not portable for locale change
    private function normalizeExcelData($cell, $dbType){
        $this->log($cell.': before calculate', 'debug');
        // if( PHPExcel_Shared_Date::isDateTime($cell) )
        if($dbType == 'date'){
            $this->log($cell.' is date', 'debug');
            $formatted = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "YYYY-M-D");
            return $formatted;
        }

        // percent check
        $test = $cell->getFormattedValue();
        $pattern = '/^([\-0-9\.]*)%$/';
        if( preg_match($pattern, $test, $matched ) ){
            return $matched[1]; 
        }

        // get caluculated value only when the value starts with =
        $pattern = '/^[=].*$/';
        if( preg_match($pattern, $cell, $matched ) ){
            $value = $cell->getCalculatedValue();
            $this->log($cell.'-> calculated value ->'.$value, 'debug');
            if( in_array($dbType, array('int', 'integer')) ){
                $value = number_format((float)$value, 0, '.', '');
            }
            else if( in_array($dbType, array('decimal1')) ){
                $value = number_format((float)$value, 1, '.', '');
            }
            else if( in_array($dbType, array('decimal2', 'currency')) ){
                $value = number_format((float)$value, 2, '.', '');
            } 
            return $value;
        }
        
        //$this->log('normalize returning:'.$cell->getValue(), 'debug');
        //$this->log('normalize formatted:'.$cell->getFormattedValue(), 'debug');
        return htmlentities($cell->getValue());
    }
        
    public function getDataFromExcel($excelFilename, $cellIdToDbMapper)
    {
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        // prepare Excel file to read
        $objPHPExcel = PHPExcel_IOFactory::load($excelFilename);
        $activeSheet = $this->getActiveSheet($objPHPExcel);

        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $activeSheet->getStyle("A1:$highestColumn$highestRow")
                    ->getAlignment()
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $colNameToVal = array();
        $matchRegex = '/[a-zA-Z0-9]*:([a-zA-Z0-9_]*):([a-zA-Z0-9_]*):.+/';
        foreach( $cellIdToDbMapper as $cellId=>$dbDef){
            if( preg_match($matchRegex, $dbDef, $matched) ){
                $colName = $matched[1];
                $dbType = $matched[2];
            }else{
                continue;
            }
//            $this->log('getDataFromExcel, cellId='.$cellId.','.$dbDef, 'debug');
            $cell = $activeSheet->getCell($cellId);
            $cellType = $activeSheet->getStyle($cellId)
                        ->getNumberFormat()
                        ->getFormatCode();
            #echo $cellVal. var_dump($cellType);
            $this->log('cell:'.$cell.' dbType:'.$dbType, 'debug');
            $cellVal = $this->normalizeExcelData($cell, $dbType);
            $this->log('cell:'.$cell.' cellVal:'.$cellVal, 'debug');

            $colNameToVal[$colName] = $cellVal;
        }
        return $colNameToVal;
    }
}
?>
