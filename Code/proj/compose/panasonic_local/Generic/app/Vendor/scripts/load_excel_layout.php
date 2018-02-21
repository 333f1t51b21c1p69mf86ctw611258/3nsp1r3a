<?php

require('../excel/PHPExcel/Reader/Excel5.php');

/**
  *  input:  excel layout + DB def 
  *  output: upload.php, uploadMapper.php
  */
class ExcelLoader 
{
    public function getCellIdToValueMap($mapperHtmlFilename, $valueHtmlFilename){

        $mapperDoc = file_get_contents($mapperHtmlFilename);
        $mapperDom = new DOMDocument;
        $mapperDom->loadHTML( $mapperDoc );

        $previewDoc = file_get_contents($valueHtmlFilename);
        $previewDom = new DOMDocument;
        $previewDom->loadHTML( $previewDoc );

        $cellIds         = $mapperDom->getElementsByTagname('td');
        $valuesInPreview = $previewDom->getElementsByTagname('td');

        echo 'Size of TDs found in map/preview:';
        var_dump($cellIds->length);

        $cellIdPattern = '/[A-Z]*[0-9]*/';
        $formatPattern = '/[0-9a-zA-Z_\-\+]*:[0-9a-zA-Z_\-\+]*:.*/';
        $valueMapper = Array();
        $formulaMapper = Array();
        for( $i=0; $i<$cellIds->length; $i++){
            $cellId = $cellIds->item($i);
            $value  = $valuesInPreview->item($i);

            // create A1->input:colname:type... sort of mapping
            $valueMapper[$cellId->nodeValue] = $value->nodeValue;
            //$this->log('getCellTdToValueMap,value extracted='.$value->nodeValue, 'debug');

            // save formula
            // create id->formula mapping
            $formula = $value->getAttribute('formula');
            //$this->log('getCellTdToValueMap,formula extracted='.$formula, 'debug');
            if( $formula ){
                $id = explode(":", $value->nodeValue)[1];
                $formulaMapper[$id] = $formula;
            }
        }

        return array( $valueMapper, $formulaMapper);
    }

    // extracted
    private function getPHPExcelInstance($XLfile) 
    {
        $objPHPExcel = PHPExcel_IOFactory::load($XLfile);

        $highestRow = $objPHPExcel
            ->getActiveSheet()
            ->getHighestRow();
        $highestColumn = $objPHPExcel
            ->setActiveSheetIndex(0)
            ->getHighestColumn();
        ++$highestColumn;

        $objPHPExcel
            ->getActiveSheet()
            ->getStyle("A1:$highestColumn$highestRow")
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //showing some possible processes that can be made
        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; $i++) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                //eliminating spaces before and after 'input:'
                if ($objPHPExcel->getActiveSheet()->getCell("$j$i") == 'input: '
                    or $objPHPExcel->getActiveSheet()->getCell("$j$i") == ' input') {
                    $objPHPExcel->getActiveSheet()->SetCellValue("$j$i", 'input:');
                }
            }
        }
        //$this->addImage($objPHPExcel->getActiveSheet());
        return $objPHPExcel;
    }

    private function nextColumn($col){ $tmp=$col; $tmp++; return $tmp; }

    private function adjustColWidth($html_out){
        $doc = file_get_contents($html_out);
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
        foreach( $convMap as $old=>$new ){
            $doc = str_replace($old,$new,$doc);
        }
        file_put_contents($html_out, $doc);
    }

    private function adjustColHeight($html_out){
        $doc = file_get_contents($html_out);
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
        foreach( $convMap as $old=>$new ){
            $doc = str_replace($old,$new,$doc);
        }
        file_put_contents($html_out, $doc);
    }

    private function saveHtmlWithCellId($XLfile) 
    {
        $objPHPExcel = PHPExcel_IOFactory::load($XLfile);

        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $objPHPExcel
            ->getActiveSheet()
            ->getStyle("A1:$highestColumn$highestRow")
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //showing some possible processes that can be made
        $highestColumnPlusOne = $this->nextColumn($highestColumn);
        for ($i=1; $i <= $highestRow; ++$i) {
            for ($j='A'; $j != $highestColumnPlusOne; ++$j) {
                $objPHPExcel->getActiveSheet()->SetCellValue("$j$i", "$j$i");
            }
        }
        //$this->addImage($objPHPExcel->getActiveSheet());
        return $objPHPExcel;
    }

    public function convert_excel_to_html($excel_in, $html_out, $mapper_out) {
        $objPHPExcel = $this->getPHPExcelInstance($excel_in);

        PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
        $objWriter->setUseInlineCSS(true);

        $objWriter->save($html_out);

        $this->adjustColWidth($html_out);
        $this->adjustColHeight($html_out);

        $objPHPExcelMapper = $this->saveHtmlWithCellId($excel_in);
        $objWriterMapper = PHPExcel_IOFactory::createWriter($objPHPExcelMapper, 'HTML');
        $objWriterMapper->setUseInlineCSS(true);
        $objWriterMapper->save($mapper_out);
    }
}

/**
  *  input:  mapper html
  *  output: cell/type info on Redis
  */
require_once('../predis/predis/autoload.php');
class RedisLoader
{
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

    private function getRedisInstance(){
        $this->connect_dictionary();
        return $this->redis;
    }
    
    private function generateDBMapping($pluginName, $type, $Mapping){
        $plugin_key = 'EXCELDATA_'.$pluginName.'_'.$type;
        foreach($Mapping as $field => $value){
            $this->getRedisInstance()->hset($plugin_key, $field, $value);
        }
    }

    public function load_cellinfo($xls_loader, $pluginName, $html_in, $mapper_in)
    {
        // generate tdToExcelId map 
        list ($cellIdToValueMap, $formulaMap) = 
            $xls_loader->getCellIdToValueMap($mapper_in, $html_in);

        /*
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
        */
        // DB Mapping
        $type = 'formula';
        $this->generateDBMapping($pluginName, $type, $formulaMap);
    }
}

// increase memory limit
ini_set('memory_limit', '512M');
date_default_timezone_set ('America/Chicago');

// main block
if (sizeof($argv) < 5) {
    echo 'usage: load_excel_layout.php <plugin> <excel> <out_html> <mapper_html>';
    exit;
}
$plugin = $argv[1];
$excel_file = $argv[2];
$html_file = $argv[3];
$mapper_file = $argv[4];

echo 'Converting Excel into html...';
$el = new ExcelLoader();
$el->convert_excel_to_html($excel_file, $html_file, $mapper_file);

echo 'Loading metadata to Redis...';
$rl = new RedisLoader();
$rl->load_cellinfo($el, $plugin, $html_file, $mapper_file);

