<?php
App::uses('Component', 'Controller');

class DBMappingComponent extends Component {
    public $components = array('ConfigService', 'ExcelLoader');

    private $pluginName;
    private $pluginKey;
    private $activeSheet;

    public function setParams($pluginName){
        $this->pluginname = $pluginName;
        $this->pluginKey = 'EXCELDATA_'.$pluginName;
        $this->log('setParams pluginName:'.$pluginName, 'debug');
    }

    public function generateDBMapping($pluginName, $type, $Mapping){ //type is formula or cellId
    // public function generateDBMapping($type, $Mapping){ //type is formula or cellId
        $this->log('DB Mapping ->', 'debug');
        $this->log($type.'Map', 'debug');
        $this->log($Mapping, 'debug');
        $plugin_key = 'EXCELDATA_'.$pluginName.'_'.$type;
        $this->log('pluginKey: '.$plugin_key, 'debug');
        foreach($Mapping as $field => $value){
            $this->log('plugin: '.$pluginName.', column: '.$field.', : '.$value, 'debug');
            $this->ConfigService->getRedisInstance()->hset($plugin_key, $field, $value);
        }
    }
    
    public function saveUpdate($pluginName, $dbConfig, $formula){
        $exploded = explode(':', $dbConfig);
        $colName = $exploded[3];
//        $this->log('DB Mapping saveUpdate colName='.$colName, 'debug');
        $cellId = $this->getValueFromRedis('cellId', $colName);
//        $this->log('saveUpdate '.$cellId. ' '.$colName.' '.$formula, 'debug');
        $formulaMap[$cellId] = $formula;
        $type = 'formula'; 
        $this->generateDBMapping($pluginName, $type, $formulaMap);
    }

    public function getObjPHPExcel($mapperPath){
        $this->log($mapperPath, 'debug');
        $objPHPExcel = PHPExcel_IOFactory::load($mapperPath);
        return $objPHPExcel;
    }

    private function getActiveSheet($excelObj)
    {
        if (empty($this->activeSheet)) {
            $this->activeSheet = $excelObj->getActiveSheet();
        }
        return $this->activeSheet;
    }

    public function generateColNameToCellIdMap($pluginName)
    {
        $this->log('generateColNameToCellIdMap', 'debug');
        
        $mapperPath = $this->ExcelLoader->getUploadXlsFullpath();
        //$this->log($mapperPath, 'debug');
        //$objPHPExcel = PHPExcel_IOFactory::load($mapperPath);
        $objPHPExcel = $this->getObjPHPExcel($mapperPath);
        $activeSheet = $this->getActiveSheet($objPHPExcel);

        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;  
//        $this->log('objPHPExcel range colNameMap A1:'.$highestColumn.$highestRow, 'debug');

        $activeSheet->getStyle("A1:$highestColumn$highestRow")
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumn; ++$j) {
                //eliminating spaces before and after 'input:'
                $cellVal = $activeSheet->getCell("$j$i")->getValue();
//                if (startsWith($cellVal, 'input')){
//                if( preg_match( '/^input.*/', $cellVal)){
//                $this->log($j.$i.': cellVal from xls file:'.$cellVal, 'debug');
                $exploded = explode(':', $cellVal);
                if(count($exploded) == 5){
                    if ($exploded[0] == 'label'){
                        $labelMap[$j.$i] = $exploded[3];
                    }else{
                        $colName = $exploded[1];
//                        $this->log($j.$i.': colName from xls file:'.$colName, 'debug');
                        $cellIdMap[$colName] = "$j$i";
                        if(isset($colName)){
                            $colNameMap["$j$i"] = $colName;
                        }
                    }
                }
            }
        }
//        $this->log('redis input', 'debug');
        $type = 'cellId'; 
        if(!empty($cellIdMap)){
            $this->generateDBMapping($pluginName, $type, $cellIdMap);
//        $this->log('cellIdMap:', 'debug');
//        $this->log($cellIdMap, 'debug');
        }
        $type = 'colName'; 
        if(!empty($colNameMap)){
            $this->generateDBMapping($pluginName, $type, $colNameMap);
            #$this->log('colNameMap:', 'debug');
            #$this->log($colNameMap, 'debug');
        }
        if(!empty($labelMap)){
            $type = 'label'; 
            $this->generateDBMapping($pluginName, $type, $labelMap);
            //        $this->log('labelMap:', 'debug');
            //        $this->log($labelMap, 'debug');
        }
    }

    public function getValueFromRedis($type, $field){
        $pluginName = $this->ExcelLoader->getPluginName();
        $pluginKey = 'EXCELDATA_'.$pluginName;
//        $this->log('value from redis hget'.$pluginKey.'_'.$type.' '.$field, 'debug');
        $value = $this->ConfigService->getRedisInstance()->hget($pluginKey.'_'.$type, $field);
        return $value;
    }

    public function importFormulaMap($pluginName, $excelPath)
    {
//        $this->log('importFormulaMap', 'debug');
        $pluginName = $this->ExcelLoader->getPluginName();
        $objPHPExcel = $this->getObjPHPExcel($excelPath);
        $activeSheet = $this->getActiveSheet($objPHPExcel);
        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
//        $this->log('objPHPExcel range A1:'.$highestColumn.$highestRow, 'debug');
        ++$highestColumn;  

        $formulaMap = array();
        $activeSheet->getStyle("A1:$highestColumn$highestRow")
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumn; ++$j) {
                $cellVal = $activeSheet->getCell("$j$i")->getValue();
                $pattern = '/^[=].*$/';
                if( preg_match($pattern, $cellVal) ){
                    $exploded = explode('=', $cellVal);
//                    $this->log($j.$i.': cellValue:'.$cellVal, 'debug');
                    $formula = $exploded[1];
//                    $this->log($j.$i.': formula from xls file:'.$formula, 'debug');
                    $formulaMap["$j$i"] = $formula;
                    $colName = $this->getValueFromRedis('colName', "$j$i");
                    $colNameToFormulaMap[$colName] = $formula;
                }else{
                    continue;
                }
            }
        }
        $type = 'formula';
        $this->generateDBMapping($pluginName, $type, $formulaMap);
        //$type = 'colName_formula';
        //$this->generateDBMapping($pluginName, $type, $colNameToFormulaMap);
    }
    
    public function getColNameToFormulaMap(){   //type is 'cellId' or 'colName'
        $this->log('colName to formula map', 'debug');
        $type = 'colName';
        $excelPath = $this->ExcelLoader->getUploadXlsFullpath();
        $objPHPExcel = $this->getObjPHPExcel($excelPath);
        $activeSheet = $this->getActiveSheet($objPHPExcel);
        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;  

        $formulaMap = array();
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumn; ++$j) {
                $field = $this->ConfigService->getRedisInstance()->hget($this->pluginKey.'_'.$type, "$j$i");
                //$this->log($j.$i.': colName from redis:'.$field, 'debug');
//                $this->log('hget('.$this->pluginKey.'_'.$type.', '."$j$i".')', 'debug');
                if( isset($field) ){
                    $formula = $this->ConfigService->getRedisInstance()->hget($this->pluginKey.'_formula', "$j$i");
                    if( isset($formula) ){
                        $colName = $field;
                        //$this->log($j.$i.': formula from redis:'.$formula, 'debug');
                        $formulaMap[$colName] = $formula;
                    }
                }
            }
        }
        //$this->log('colNameToFormulaMap ->', 'debug');
        //$this->log($formulaMap, 'debug');
        return $formulaMap;
    }
    public function getCellIdToFormulaMap(){   //type is 'cellId' or 'colName'
        $this->log('cellId to colName map', 'debug');
        $excelPath = $this->ExcelLoader->getUploadXlsFullpath();
        $objPHPExcel = $this->getObjPHPExcel($excelPath);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;  

        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumn; ++$j) {
                $formula = $this->ConfigService->getRedisInstance()->hget($this->pluginKey.'_formula', "$j$i");
                if( isset($formula) ){
                    //$this->log($j.$i.': formula from redis:'.$formula, 'debug');
                    $formulaMap["$j$i"] = $formula;
                }
            }
        }
//        $this->log('cellIdToFormulaMap ->', 'debug');
//        $this->log($formulaMap, 'debug');
        return $formulaMap;
    }

    public function getCellIdToColNameMap(){
        $this->log('cellId to colName map', 'debug');
        $excelPath = $this->ExcelLoader->getUploadXlsFullpath();
        $objPHPExcel = $this->getObjPHPExcel($excelPath);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;  

        $colNameMap = array();
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumn; ++$j) {
                $colName = $this->ConfigService->getRedisInstance()->hget($this->pluginKey.'_colName', "$j$i");
                if( isset($colName) ){
                    $this->log($j.$i.': colName from redis:'.$colName, 'debug');
                    $colNameMap["$j$i"] = $colName;
                }
            }
        }
        // $this->log('get cellIdToColNameMap ->', 'debug');
        // $this->log($colNameMap, 'debug');
        return $colNameMap;
    }
}
