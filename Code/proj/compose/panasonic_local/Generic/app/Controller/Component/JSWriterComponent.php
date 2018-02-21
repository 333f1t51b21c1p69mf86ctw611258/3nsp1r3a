<?php
App::uses('Component', 'Controller');

class JSWriterComponent extends Component {
    public $components = array('DBMapping', 'ExcelLoader', 'ConfigService');
    var $jsHead = <<<END
$(document).ready(function(){
END;
    var $jsTail = <<<END
});
END;

    private function parseAndGenerateFormula($formula,$tmpvalname){
        $this->log('JSWriterComponent:parseAndGenerateFormula, formula/valname='.$formula.'/'.$tmpvalname, 'debug');
        // replace each concatenated term with formatted string
        // IDVAR => $('#IDVAR').val()

        $fbody = 'var '.$tmpvalname.'=';
        $this->log('JSWriterComponent:formula.trim='.trim($formula), 'debug');
        // replace each concatenated term with formatted string
        $terms = preg_split("/[\(\)\+\-\*\/]+/", trim($formula));
        $this->log('JSWriterComponent:terms=', 'debug');
        $this->log($terms, 'debug');
        $map = array();
        foreach($terms as $t){
            $map[$t] = 'parseFloat($(\'#'.$t.'\').val()||"0")';
        }
        $replaced = ""; 
        $pattern = '/([\(]*)([\w]+)([\+\-\\\*])/';
        $cur_index = 0;
        while(1){
            while( preg_match($pattern, substr($formula, $cur_index), $matched) ){
                $this->log( 'matched:'.$matched[1].','.$matched[2].','.$matched[3]. "\n", 'debug');
                $cur_index += strlen($matched[1]) + strlen($matched[2]) + strlen($matched[3]);
                $replaced .= $matched[1]. $map[$matched[2]]. $matched[3];
            }   
            $pattern_close = '/([\(]*)([\w]+)([\)]*)/';
            if( preg_match($pattern_close, substr($formula, $cur_index), $matched) ){
                $this->log( 'close matched:'.$matched[1].','.$matched[2].','.$matched[3]. "\n", 'debug');
                $replaced .= $matched[1]. $map[$matched[2]]. $matched[3];
            }   
            $cur_index += strlen($matched[1]) + strlen($matched[2]) + strlen($matched[3]);
            if( strlen($formula)>=$cur_index ) break;
        }

        $this->log( 'replaced='.$replaced."\n", 'debug');

        $fbody .= $replaced . ';';

        $this->log("JSWriter, generatedFormula=".$fbody, 'debug');
        return $fbody;
    }
    public function sortByLength($a, $b){
        return strlen($b) - strlen($a);
    }

    private function parseAndGenerateSetValue($cellId, $setToValname){
        $this->log('JSWriterComponent:parseAndGenerateSetValue, id/valname='.$cellId.'/'.$setToValname, 'debug');
        $fbody = '$(\'#'. $cellId. '\').val('. $setToValname. '||"0");';
        $fbody .= "\n";
        $fbody .= '        NDigitCheck($(\'#'.$cellId.'\'));'; 
        return $fbody;
    }

    // 
    // document.ready
    //   main
    //     $id.change(function(){ fbody }).change();
    //   fbody
    private function genFunction($cellId, $formula){
//        $this->log('JSWriter:genFunction, id/formula='.$cellId.'/'.$formula, 'debug');

        $fname = "runCalcFor". $cellId;

        //function xxx(){
        $fbody  = '    function '. $fname;
        $fbody .= '(){';
        $fbody .= "\n";

        // generate formula
        //   e.g. td_a + td_b => var val=$('#td_a').val()+$('#td_b').val();
        $tmpvalname = 'tmpval';
        $fbody .= '        ';
        $fbody .= $this->parseAndGenerateFormula($formula, $tmpvalname);
        $fbody .= "\n";

        // set value
        //  $('#xxx').val( <formula>
        $fbody .= "        ";
        $fbody .= $this->parseAndGenerateSetValue($cellId, $tmpvalname);
        $fbody .= "\n";

        // close the function and return
        $fbody .= '    }';
        $fbody .= "\n";

        // main block
        $termsAll = preg_split("/[\(\)\+\-\*\/]+/", trim($formula));
//        $this->log('termsall formula', 'debug');
//        $this->log($formula, 'debug');
        $terms = array_unique($termsAll);
//        $this->log('termsall', 'debug');
//        $this->log($termsAll, 'debug');
        foreach( $terms as $t ){
            if(preg_match('/^[0-9.]{1,}$/', $t)){
                continue;
            }
            $main = "      $('#". $t. "').change(function(){ ". 
                    $fname. "(); }).change();";
            $fbody .= $main;
            $fbody .= "\n";
        }
    
//        $this->log('JSWriter:genFunction, returning:'.$fbody, 'debug');
        return $fbody;
    }

    private function gen_overwritable_file( $filename_fullpath, $jsContent ){
        file_put_contents( $filename_fullpath, $jsContent );
        //chmod( $filename_fullpath, 0766 );
    }

    public function generateJavaScript($dirpath, $filename, $tdToFormulaMap){
        $this->log('generateJavaScript, writing to '.$dirpath.DS.$filename, 'debug');
        $this->log('generateJavaScript, td->formula Map:', 'debug');
//        $this->log($tdToFormulaMap, 'debug');

        // generate 
        $jsContent = $this->jsHead;
        $jsContent .= "\n";

        foreach($tdToFormulaMap as $cellId=>$formula){
            $jsContent .= $this->genFunction($cellId, $formula);
        }
        $jsContent .= $this->jsTail;
        $jsContent .= "\n";

        $this->gen_overwritable_file( $dirpath.DS.$filename, $jsContent );
    }   

    public function generateJSFormula($dirpath, $filename, $formulaMap){  //e.g. formulaMap[colName]=A1+A2
        $update = array();
        $this->log('generateJSFormula, writing to '.$dirpath.DS.$filename, 'debug');
        //$this->log('generateJSFormula, formulaMap=', 'debug');
        //$this->log($formulaMap, 'debug');
        // generate 
        $jsContent = $this->jsHead;
        $jsContent .= "\n";
        $jsContent .= "console.time('formula function');\n";

        foreach($formulaMap as $colName=>$formula){
            if(preg_match('/SUMIF/i', $formula)){
                list($fbody, $update) = $this->getSumif($colName, $formula, $update);
                $jsContent .= $fbody;
            } else {
                if(preg_match('/SUM/i', $formula)){
                    $formula = $this->getSum($formula);
//                    $this->log('formula after getSum: '.$formula, 'debug');
                }
                //replace all cellId in formula to colName
                $formula = $this->replaceCellIdToColName($colName, $formula);
                $formulaMap[$colName] = $formula;
                if(strlen($formula) != 0){
                    $jsContent .= '    //'.$colName." = ".$formula;
                    $jsContent .= "\n";
                    list($fbody, $update) = $this->convertFormula($colName, $formula, $update);
                    $jsContent .= $fbody;
                }
            }
        }

        $jsContent .= $this->getChangeAction($formulaMap, $update);
        $jsContent .= $this->jsTail;
        $jsContent .= "\n";

        $this->gen_overwritable_file( $dirpath.DS.$filename, $jsContent );
    }
    public function replaceCellIdToColName($colName, $formula){
        $this->log('DBMapping replaceCellIdToValueName', 'debug');
        $colNameMap = $this->DBMapping->getCellIdToColNameMap();
        //$this->log('colNameMap: ', 'debug');
        //$this->log($colNameMap, 'debug');
        $count = 0;
        $terms = preg_split("/[\(\)\+\-\*\/]+/", trim($formula));
//        $this->log('replaceCellIdToColName terms=', 'debug');
//        $this->log($terms, 'debug');
        foreach($terms as $t){
            if(strlen($t) == 0){
//                $this->log('t:null', 'debug');
            }else{
//                $this->log('t:"'.$t.'"', 'debug');
                if(preg_match('/^[0-9.]{1,}$/', $t)){
                    continue;
                }
                $count = 1;
                if(isset($colNameMap[$t])){
                    $termColName = $colNameMap[$t];
//                    $this->log($t.':termColName: '.$termColName, 'debug');
                }else{
                    $termColName = $t;
                }
                $formula = str_replace($t, $termColName, $formula, $count);
            }
        }
//        $this->log('formulaReplaced: '.$formula, 'debug');
        return $formula;
    }

    private function convertFormula($colName, $formula, $update){
//        $this->log('JSWriter:genFormula, id/formula='.$colName.'/'.$formula, 'debug');

        $fname = "run". $colName;

        //function xxx(){
        $fbody  = '    function '. $fname;
        $fbody .= '(){';
        $fbody .= "\n";

        // generate formula
        //   e.g. td_a + td_b => var val=$('#td_a').val()+$('#td_b').val();
        $tmpvalname = 'tmpval';
        $fbody .= '        ';
        $fbody .= $this->parseAndGenerateFormula($formula, $tmpvalname);
        $fbody .= "\n";

        // set value
        //  $('#xxx').val( <formula>
        $fbody .= "        ";
        $fbody .= $this->parseAndGenerateSetValue($colName, $tmpvalname);
        $fbody .= "\n";

        // close the function and return
        $fbody .= '    }'."\n";

        // main block
        $terms = preg_split("/[\(\)\+\-\*\/]+/", trim($formula));
//        $this->log('convertFormula termsall formula: '.$formula, 'debug');
        //$terms = array_unique($termsAll);
//        $this->log('terms', 'debug');
//        $this->log($terms, 'debug');
        foreach( $terms as $t ){
            if(preg_match('/^[0-9.]{1,}$/', $t)){continue;}
                // update array for writing change part
                $update = $this->updateArray($colName, $t, $update);
        }
//        $this->log('JSWriter:convertFormula, returning:'.$fbody, 'debug');
//        $this->log('JSWriter:convertFormula updated'.$colName, 'debug');
        return array($fbody, $update);
    }
    public function updateArray($colName, $t, $update){
        $key = $colName.'-'.$t;
        if(!empty($update)){
            $update[$key] = $t;
            //$update[$colname][$key] = $t;
        } else {
            $update = array($key => $t);
            //$update = array();
            //$update[$colName] = array($key => $t);
        }
        $update['have-formula-'.$colName] = $colName;
//        $this->log('update array:', 'debug');
//        $this->log($update, 'debug');
        return $update;
    }

    public function getSum($formula){
//        $this->log('genFormula input formula:'.$formula, 'debug');
        $mapperPath = $this->ExcelLoader->getUploadXlsFullpath();
        //$this->log($mapperPath, 'debug');
        //$objPHPExcel = PHPExcel_IOFactory::load($mapperPath);
        $objPHPExcel = $this->DBMapping->getObjPHPExcel($mapperPath);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;
//        $this->log('checker active excel range:'.$highestColumn.$highestRow, 'debug');

        $sum = '';
//        $this->log('SUM:'.$formula, 'debug');
        // FIXME this works only when formula = sum($i$j:$I$J)
        $rangeRaw = str_ireplace('SUM(', '', $formula);
        $rangeRaw = str_ireplace(')', '', $rangeRaw);
        $range = explode(':', $rangeRaw);
//        $this->log('range: ', 'debug');
//        $this->log($range, 'debug');
        if (count($range) == 2){
            $firstRow = preg_replace("/[^0-9]/", "", $range[0]);
            $firstColumn = preg_replace("/[^A-z]/", "", $range[0]);
            $lastRow = preg_replace("/[^0-9]/", "", $range[1]);
            $lastColumn = preg_replace("/[^A-z]/", "", $range[1]);
            ++$lastColumn;
//            $this->log('range '.$firstColumn.$firstRow.':'.$lastColumn.$lastRow, 'debug');
            if($firstRow > $highestRow) {$firstRow = $highestRow; }
            if(strcasecmp($lastColumn, $highestColumn) > 0) {$lastColumn = $highestColumn; }
            if($lastRow > $highestRow) {$lastRow = $highestRow; }
            if(strcasecmp($lastColumn, $highestColumn) > 0) {$lastColumn = $highestColumn; }
//            $this->log('range '.$firstColumn.$firstRow.':'.$lastColumn.$lastRow, 'debug');
            for ($i=$firstRow; $i<=$lastRow; $i++){
                for ($j=$firstColumn; $j!=$lastColumn; ++$j){
                    if ($i != $firstRow || $j != $firstColumn){
                        $sum .=  '+';
                    }
                    $sum .= "$j$i";
                }
            }
        }
        $formula = $sum;
//        $this->log('formula sum replaced:'.$formula, 'debug');
        return $formula;
    }
    public function getChangeAction($formulaMap, $update){
        $this->log('getChangeAction', 'debug');
        //$this->log('getChangeAction, formulaMap=', 'debug');
        //$this->log($formulaMap, 'debug');
        //$this->log('getChangeAction, update=', 'debug');
        //$this->log($update, 'debug');

        $fbody = '';
        $update_copy = $update;
        $referred_terms = $this->getRef($update);
        $cellsInFormula = '';
        //$time_start = microtime(true);
        foreach($update as $key => $term){
            if(preg_match('/have-formula-/', $key)){continue;}
            $raw_key = preg_split("/-/", trim($key));
            $colName = $raw_key[0];
            if(isset($done_col)){if($colName == $done_col){continue;}}
            $fbody .= '    function update'.$colName.'(){'."\n";
            if(isset($referred_terms)){
                foreach($referred_terms as $k => $t){
                    $raw_k = preg_split("/-/", trim($k));
                    $col = $raw_k[0];
                    if($col == $colName){
                        $fbody .= '        update'.$t.'();';
                        $fbody .= "\n"; 
                    }
                }
            }
            $fbody .= '        run'.$colName.'();'."\n    }\n";
            $done_col = $colName;
        }

        foreach($update as $key => $term){
            if(preg_match('/have-formula-/', $key)){
                $raw_key = preg_split("/have-formula-/", trim($key));
                $colName = $raw_key[1];
                $temp_cellsInFormula = $this->getCellsInFormula($colName, $colName, $referred_terms, $update_copy, $cellsInFormula);
                if(!empty($temp_cellsInFormula)){
                    foreach($temp_cellsInFormula as $temp_key => $temp_value){
                        $cellsInFormula[$temp_key] = $temp_value;
                    }
                }
            }
        }
        $this->log('cellsInFormula array', 'debug');
        $this->log($cellsInFormula, 'debug');

        $fbody .= 'var checker = document.getElementById("load_checker").value;'."\n";
        $fbody .= '$("input").focus(function(){'." checker = false; console.log('checker value:'+checker); });\n";
        $fbody .= "console.log('checker value:'+checker);\n";
        $fbody .= '    $("';
        $i = 0;
        $j = 0;
        $n_cellsInFormula = count($cellsInFormula);
        $prev_colName = '';
        foreach($cellsInFormula as $key => $term){
            $raw_key = preg_split("/-/", trim($key));
            $colName = $raw_key[0];
            $this->log('colName:'.$colName.' term:'.$term, 'debug');
            if($i != 0 && $colName != $prev_colName){ 
                $fbody .= '").change(function(){ if(!checker){ update'.$prev_colName.'(); } }).change();'."\n";
                $i = 2;
            }else if($i != 0){
                $fbody .= ',';
            }
            if($i == 2 && $j != $n_cellsInFormula + 1){
                $fbody .= '    $("';
                $i = 1;
            }
            $fbody .= "#".$term;
            $i = 1;
            $prev_colName = $colName;
            $j++;
            $this->log('fbody:'.$fbody, 'debug');
        }
        $fbody .= '").change(function(){ if(!checker){ update'.$colName.'(); } }).change();'."\n";
//        $this->log('fbody', 'debug');
//        $this->log($fbody, 'debug');
        $fbody .= "console.timeEnd('formula function');\n";
        return $fbody;

    }
    
    public function getCellsInFormula($ref_colName, $ref_col, $referred_terms, $update, $cellsInFormula){ 
        // ref_col: printed colName
        // ex)total = A + B, A = C + D
        // $ref_colName:total $ref_term:A or B
        // get the following array
        // cellsInFormula[total-C] = C
        // cellsInFormula[total-D] = D
        // cellsInFormula[total-B] = B
        $update_copy = $update;
        foreach($update as $key => $term){
            $raw_key = preg_split("/-/", trim($key));
            $colName = $raw_key[0];     // A or B or total
            if($colName == $ref_colName){
                if($this->check_if_col_has_formula($term, $update)){
                    $raw_key = preg_split("/-/", trim($key));
                    $colName = $raw_key[0];     // A or B or total
                    //$this->log('should call function itself. '.$term.' has formula. referred by '.$ref_colName, 'debug');
                    $temp_cellsInFormula = $this->getCellsInFormula($term, $ref_col, $referred_terms, $update_copy, $cellsInFormula);
                    if(!empty($temp_cellsInFormula)){
                        foreach($temp_cellsInFormula as $temp_key => $temp_value){
                            $cellsInFormula[$temp_key] = $temp_value;
                        }
                    }
                }else{
                    //$this->log('when '.$term.' in '.$colName.' does not have formula', 'debug');
                    if(isset($referred_terms)){if($this->checkRef($ref_col, $referred_terms)){
                        //$this->log('but '.$colName.' has referred by another formula', 'debug');
                        continue;
                    }}
                    $cellsInFormula[$ref_col.'-'.$term] = $term;
                    //$this->log('$cellsInFormula['.$ref_col.'-'.$term.'] = '.$cellsInFormula[$ref_col.'-'.$term], 'debug');
                }
            }
        }
        if(isset($cellsInFormula)){
            return $cellsInFormula;
        }
    }
    public function check_if_col_has_formula($term, $update){
        //$this->log('check if '.$term.' has formula', 'debug');
        foreach($update as $key => $t){
            if(preg_match('/have-formula/', $key)){
                if($term == $t){
                    //$this->log($term.' has formula', 'debug');
                    return true;
                }
            }
        }
    }
    public function getRef($update){
        // get array reffered_terms(colName-referredTerm => referredTerm)
        //$this->log('getRef', 'debug');
        $update_copy = $update;
        foreach($update as $key => $term){
            if(preg_match('/have-formula-/', $key)){continue;}
            $raw_key = preg_split("/-/", trim($key));
            $colName = $raw_key[0];
            foreach($update_copy as $k => $t){
                $raw_k = preg_split("/-/", trim($k));
                $col = $raw_k[0];
                if($term == $col){
                    $referred_terms[$key] = $term;
                }
            }
        }
        if(isset($referred_terms)){
            $this->log('referred_terms array', 'debug');
            $this->log($referred_terms, 'debug');
            return $referred_terms;
        }
    }
    public function checkRef($colName, $referred_terms){
        // if $colName is referred by $ref_colName, return $ref_colName. 
        foreach($referred_terms as $key => $term){
            $raw_key = preg_split("/-/", trim($key));
            $ref_colName = $raw_key[0];
            if($term == $colName){
                return $ref_colName;
            }
        }
    }
        
    public function getSumif($colName, $formula, $update){
//        $this->log('getSumif input formula:'.$formula, 'debug');
        $mapperPath = $this->ExcelLoader->getUploadXlsFullpath();
        //$this->log($mapperPath, 'debug');
        //$objPHPExcel = PHPExcel_IOFactory::load($mapperPath);
        $objPHPExcel = $this->DBMapping->getObjPHPExcel($mapperPath);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        ++$highestColumn;

        $colNameMap = $this->DBMapping->getCellIdToColNameMap();

        // FIXME this works only when formula = sumif(range, criteria, sum_range) && criteria is tagged with label
        $termsRaw = str_ireplace('SUMIF(', '', $formula);
        $termsRaw = str_ireplace(')', '', $termsRaw);
        $terms = preg_split('/(:|,)/', $termsRaw);
//        $this->log('terms: ', 'debug');
//        $this->log($terms, 'debug');
        if (count($terms) == 5){
            // load ranges and criteria
            $lastColumn = preg_replace("/[^A-z]/", "", $terms[1]);
            $range = array(preg_replace("/[^A-z]/", "", $terms[0]),
                         preg_replace("/[^0-9]/", "", $terms[0]),
                         $lastColumn,
                         preg_replace("/[^0-9]/", "", $terms[1]));
//            $this->log('range '.$range[0].$range[1].':'.$range[2].$range[3], 'debug');
            if($range[1] > $highestRow) {$range[1] = $highestRow; }
            if(strcasecmp($range[0], $highestColumn) > 0) {$range[0] = $highestColumn; }
            if($range[3] > $highestRow) {$range[3] = $highestRow; }
            if(strcasecmp($range[2], $highestColumn) > 0) {$range[2] = $highestColumn; }
            $criteria = array(preg_replace("/[^A-z]/", "", $terms[2]), 
                            preg_replace("/[^0-9]/", "", $terms[2]));
            $sum_range = array(preg_replace("/[^A-z]/", "", $terms[3]),
                            preg_replace("/[^0-9]/", "", $terms[3]),
                            preg_replace("/[^A-z]/", "", $terms[4]),
                            preg_replace("/[^0-9]/", "", $terms[4]));
            if($sum_range[1] > $highestRow) {$sum_range[1] = $highestRow; }
            if(strcasecmp($sum_range[0], $highestColumn) > 0) {$sum_range[0] = $highestColumn; }
            if($sum_range[3] > $highestRow) {$sum_range[3] = $highestRow; }
            if(strcasecmp($sum_range[2], $highestColumn) > 0) {$sum_range[2] = $highestColumn; }
//            $this->log('sum_range '.$sum_range[0].$sum_range[1].':'.$sum_range[2].$sum_range[3], 'debug');

            // generate range and sum_range array
            $rangeArray =  'var range = [';
            $sum_rangeArray =  'var sum_range = [';
            $fbody = '    function run'.$colName.'(){'."\n";
            for ($i=$range[1]; $i<=$range[3]; $i++){
                for ($j=$range[0]; strcasecmp($j,$range[2]) <= 0; ++$j){
                    $sum_j = $sum_range[0];
                    if(!array_key_exists("$j$i", $colNameMap)){continue;}
                    $term = $colNameMap["$j$i"];
                    $sum_term = $colNameMap["$sum_j$i"];
//                    $this->log($j.$i.':colName:'.$term,'debug');
                    $rangeArray .= $j.$i;
                    $sum_rangeArray .= $sum_j.$i;
                    $fbody .= "        var ".$j.$i."="."parseFloat($('#".$term."').val()||".'"0");'."\n";
                    $fbody .= "        var ".$sum_j.$i."="."parseFloat($('#".$sum_term."').val()||".'"0");'."\n";
                    if ($i != $range[3] || $j != $range[2]){
                        $rangeArray .= ',';
                        $sum_rangeArray .= ',';
                    }else{
                        $rangeArray .= '];';
                        $sum_rangeArray .= '];';
                    }

                    // update array for writing change part
                    $update = $this->updateArray($colName, $term, $update);
                    $update = $this->updateArray($colName, $sum_term, $update);
                    ++$sum_j;
                }
            }
//            $this->log('rangeArray:'.$rangeArray, 'debug');
/*            // generate range array and add var
            $rangeArray =  'var range = [';
            $fbody = '    function run'.$colName.'(){'."\n";
            for ($i=$range[1]; $i<=$range[3]; $i++){
                for ($j=$range[0]; strcasecmp($j,$range[2]) <= 0; ++$j){
                    if(!array_key_exists("$j$i", $colNameMap)){continue;}
                    $term = $colNameMap["$j$i"];
                    $this->log($j.$i.':colName:'.$term,'debug');
                    $rangeArray .= $j.$i;
                    $fbody .= "        var ".$j.$i."="."parseFloat($('#".$term."').val()||".'"0");'."\n";
                    if ($i != $range[3] || $j != $range[2]){
                        $rangeArray .= ',';
                    }else{
                        $rangeArray .= '];';
                    }

                    // update redis for writing change part
                    $update = $this->updateArray($colName, $term, $update);
                }
            }
            $this->log('rangeArray:'.$rangeArray, 'debug');
            // generate sum_range array
            $sum_rangeArray =  'var sum_range = [';
            for ($i=$sum_range[1]; $i<=$sum_range[3]; $i++){
                for ($j=$sum_range[0]; strcasecmp($j,$sum_range[2]) <= 0; ++$j){
                    if(!array_key_exists("$j$i", $colNameMap)){continue;}
                    $term = $colNameMap["$j$i"];
                    $sum_rangeArray .= $j.$i;
                    $fbody .= "        var ".$j.$i."="."parseFloat($('#".$term."').val()||".'"0");'."\n";
                    if ($i != $sum_range[3] || $j != $sum_range[2]){
                        $sum_rangeArray .= ',';
                    }else{
                        $sum_rangeArray .= '];';
                    }
                    // update redis for writing change part
                    $update = $this->updateArray($colName, $term, $update);
                }
            }
*/
//            $this->log('sum_rangeArray:'.$sum_rangeArray, 'debug');
            if(array_key_exists($criteria[0].$criteria[1], $colNameMap)){
                // update redis for writing change part
                $criteria_colName = $colNameMap[$criteria[0].$criteria[1]];
                $update = $this->updateArray($colName, $criteria_colName, $update);
                $criteria_body = "        var criteria = "."parseFloat($('#".$criteria_colName."').val()||".'"0");'."\n";
            }else{
                $label = $this->DBMapping->getValueFromRedis('label', $criteria[0].$criteria[1]);
                $criteria_body = "        var criteria = parseFloat(document.getElementById('".$label."').innerHTML);";
            }

//            $fbody .= "//        var criteria = parseFloat($('#".$criteria_colName."').val()||".'"0");'."\n";
            $fbody .= $criteria_body."\n";
            $fbody .= '        '.$rangeArray."\n".'        '.$sum_rangeArray."\n";
            $fbody .= '        var sum = 0;'."\n";
            $fbody .= '        for(i = 0; i < range.length; i++){'."\n";
            $fbody .= "            if(parseFloat(range[i]) == parseFloat(criteria)){\n";
            $fbody .= "                sum = sum + Number(sum_range[i]);\n";
            $fbody .= "            }\n";
            $fbody .= '        '.$this->parseAndGenerateSetValue($colName, 'sum')."\n";
            $fbody .= "        }\n";
            $fbody .= "    }\n";
        }
        return array($fbody, $update);
        // $this->log('formula sum replaced:', 'debug');
    }
}
