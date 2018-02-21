<?php
require_once(APP."/Vendor/excel/PHPExcel.php");

App::uses('Component', 'Controller');
class ExportComponent extends Component {

    public $components = array('ConfigService','ExcelLoader');
/**
 * The calling Controller
 *
 * @var Controller
 */
    public $controller;
    private $activeSheet;
/**
 * Starts up ExportComponent for use in the controller
 *
 * @param Controller $controller A reference to the instantiating controller object
 * @return void
 */
    public function startup(Controller $controller) {
        $this->controller = $controller;
    }

    private function nextColumn($col){ $tmp=$col; $tmp++; return $tmp; }

    private function getActiveSheet($excelObj)
    {
        if (empty($this->activeSheet)) {
            $this->activeSheet = $excelObj->getActiveSheet();
        }
        return $this->activeSheet;
    }

    // FIXME
    // this doesn't work - percent cell always returned as string 's'
    // when the actual value is text
    private function normalizeCellValue( $targetExcel, $coordinate, $value )
    {
        $this->log("normalize, cell=",'debug');
        $activeSheet = $this->getActiveSheet($targetExcel);

        $this->log($activeSheet->getCell($coordinate)->getDataType(),'debug');
        if( PHPExcel_Cell_DataType::TYPE_NUMERIC == $activeSheet->getCell($coordinate)->getDataType() ){ 
            $this->log('format=', 'debug');
            $this->log($activeSheet->getStyle($coordinate)
                        ->getNumberFormat(), 'debug');
            if( $activeSheet->getStyle($coordinate)
                        ->getNumberFormat() == PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE ){
                return float($value)/100;
            }
        }
        return $value;
    }

    private function ymd_to_mdy($cellval)
    {
        if (preg_match('/(?P<y>\d{4})-(?P<m>\d{2})-(?P<d>\d{2})/', $cellval, $m)) {
            return $m['m'].'/'.$m['d'].'/'.$m['y'];
        }
        return $cellval;
    }

    // export a data in one spreadsheet
    public function export_to_excel($pluginName, $data, $form_id)
    {
        $this->log('export_to_excel debug', 'debug');
        $cellid_to_colname = $this->ConfigService->get_cellid_to_colname_map($pluginName);

        // duplicate Excel sheet for processing
        $activeExcelUpload = $this->ExcelLoader->getActiveXlsFullpath();
        $excelToExport = tempnam("/tmp", "EXP").'.xlsx';
        copy($activeExcelUpload, $excelToExport);

        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        // load, update and write the temp XLS
        $targetExcel = PHPExcel_IOFactory::load($excelToExport);
        $activeSheet = $this->getActiveSheet($targetExcel);
        foreach( $cellid_to_colname as $cellid=>$colname ){
            if( !array_key_exists($colname, $data) ) continue;
            $cellval = $data[$colname];
            $cellval = $this->ymd_to_mdy($cellval);
            //$cellval = $this->normalizeCellValue($targetExcel, $cellid, $cellval);
            $activeSheet->setCellValue($cellid, $cellval);
        }

        // scan excel file to replace 'label' marker with value
        $highestRow = $activeSheet->getHighestRow();
        $highestColumn = $targetExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;
        $activeSheet->getStyle("A1:$highestColumn$highestRow")
                    ->getAlignment() 
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                $pattern = '/label:(\w+):.+/';
                $cell_value = $activeSheet->getCell("$j$i");
                if( preg_match($pattern, $cell_value, $matches) ){
                    $activeSheet->SetCellValue("$j$i", $matches[1]);
                }
            }
        }

        $activeSheet->getProtection()->setPassword('read-only');
        $activeSheet->getProtection()->setSheet(true);
        $activeSheet->getStyle("A1:$highestColumn$highestRow")
            ->getProtection()
            ->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);

        $objWriter = PHPExcel_IOFactory::createWriter($targetExcel, 'Excel2007');
        $objWriter->save($excelToExport);

        $targetExcel->disconnectWorksheets();

        $fileNameToExport = "export_".$form_id.'_'.date("Y-m-d").".xlsx";
        $params = array('filename' => $fileNameToExport,
                        'fullpath' => $excelToExport);

        return $params;
    }

    // header for export should contain (key) if used for ID
    private function add_keys($pluginName, $header){
        $keys = $this->ConfigService->get_impexp_keys($pluginName);
        //$this->log('add_keys:pluginName,keys='.$pluginName,'debug');       
        //$this->log($keys,'debug');       
        $retval = array(); 
        foreach( $header as $h ){
            $to_add = $h;
            //$this->log('add_keys:checking header:'.$h,'debug');       
            if( isset($keys) && in_array($h, $keys) ){
                //$this->log('add_keys:adding (key)', 'debug');
                $to_add .= '(key)';
            }
            array_push($retval, $to_add);
        }
        //$this->log('add_keys: retval=', 'debug');
        //$this->log($retval, 'debug');
        return $retval;
    }

    // export the whole table into one sheet
    public function exportExcelInFile($pluginName, $data, $fileName = '', $maxExecutionSeconds = null, $delimiter = ',', $enclosure = '"') {

        $this->controller->autoRender = false;

        // Flatten each row of the data array
        $flatData = array();
        foreach($data as $numericKey => $row){
            $flatRow = array();
            $this->flattenArray($row, $flatRow);
            $flatData[$numericKey] = $flatRow;
        }

        $headerRow = $this->getKeysForHeaderRow($flatData);
        $flatData = $this->mapAllRowsToHeaderRow($headerRow, $flatData);
        //$this->log('flatData=' ,'debug');
        //$this->log($flatData,'debug');
        if(!empty($maxExecutionSeconds)){
            ini_set('max_execution_time', $maxExecutionSeconds); //increase max_execution_time if data set is very large
        }

        $csvFile = tempnam("/tmp", "Exp");
        $this->log('csvfile: '.$csvFile, 'debug');
        $handle = fopen($csvFile, "w");

        // set validation condition if any
        $validations = $this->ConfigService->get_app_range_validation_map($pluginName);
        $this->log('range validation cond=','debug');
        $this->log($validations, 'debug');
        if( !empty($validations) ){
            foreach( $validations as $v ){
                fputcsv($handle, $v);
            }
        }
        
        fputcsv($handle, $this->add_keys($pluginName, $headerRow), $delimiter, $enclosure);
        foreach ($flatData as $key => $value) {
            fputcsv($handle, $value, $delimiter, $enclosure);
        }
        fclose($handle);

        $fileNameBase = "export_".date("Y-m-d").".xlsx";
        $fileNameFull = "/tmp/". $fileNameBase;
        $this->log('Excel export: '.$fileNameFull, 'debug');

        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '8MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $objReader = PHPExcel_IOFactory::createReader('CSV');
        $objPHPExcel = $objReader->load($csvFile);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($fileNameFull);
        // this will remove the file
        //unlink($csvFile);
   
        $params = array('filename' => $fileNameBase,
                        'fullpath' => $fileNameFull );

        return $params;
    }

    public function flattenArray($array, &$flatArray, $parentKeys = ''){
        foreach($array as $key => $value){
            //$chainedKey = ($parentKeys !== '')? $parentKeys.'.'.$key : $key;
            $chainedKey = $key;
            if(is_array($value)){
                $this->flattenArray($value, $flatArray, $chainedKey);
            } else {
                $flatArray[$chainedKey] = $value;
            }
        }
    }

    public function getKeysForHeaderRow($data){
        $headerRow = array();
        foreach($data as $key => $value){
            foreach($value as $fieldName => $fieldValue){
                if(array_search($fieldName, $headerRow) === false){
                    $headerRow[] = $fieldName;
                }
            }
        }

        return $headerRow;
    }

    public function mapAllRowsToHeaderRow($headerRow, $data){
        $newData = array();
        foreach($data as $intKey => $rowArray){
            foreach($headerRow as $headerKey => $columnName){
                if(!isset($rowArray[$columnName])){
                    //$rowArray[$columnName] = '';
                    $newData[$intKey][$columnName] = '';
                } else {
                    $newData[$intKey][$columnName] = $rowArray[$columnName];
                }
            }
        }

        return $newData;
    }
}
