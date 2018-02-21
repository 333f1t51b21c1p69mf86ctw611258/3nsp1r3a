<?php
App::uses('Component', 'Controller');

class DOMEditorComponent extends Component {
    
    //public function startup($controller){ $this->controller = $controller;}

    public $components = array('SQLConnection', 'ConfigService');

    private function getMaxLength($type){

        $typeToMaxlen = array(
            'int'=>9,
            'double'=>15,
            'string'=>254,
            'text'=>999999999,
            'date'=>10,
            );

        $retval = array_key_exists($type, $typeToMaxlen);

        if( $retval ){
            return $typeToMaxlen[$type];
        }

        $defaultMaxLength = 50;

        return $defaultMaxLength;
    }

    private function getStringFromDB($dbArray, $colname){
        if( empty($dbArray) ){
            return NULL;
        }
        if( !in_array($colname, array_keys($dbArray) ) ){
            return NULL;
        }
        
        // if value can be an array(true for checkbox), implode
        if( is_array($dbArray[$colname]) )
            return implode(',', $dbArray[$colname]);

        return $dbArray[$colname];
    }

    // input: matched
    //   input:names:type:id:classes
    // return array with following values
    //   'name'=>val, 'type'=>val, 'id'=>val, 'classes'=>array()
    private function parseInputString($matched, $styles){
        //print_r('parseInputString, matched='.$matched);
        //print_r('parseInputString, styles=');
        //print_r($styles);
        //print_r('//');
        $matchedArray = preg_split('/[:<]/',$matched);

        $retArray = array();
        if( count($matchedArray)>=2 ){
            $retArray['name'] = $matchedArray[1];
            $retArray['type']  = 'string';
            $retArray['id']    = $matchedArray[1];
            $retArray['classes'] = in_array('class',array_keys($styles)) ? array($styles['class']) : array();
        } 
        if( count($matchedArray)>=3 ){
            $retArray['type'] = $matchedArray[2];
            if( strcmp($retArray['type'],'currency')==0 ){
                array_push($retArray['classes'], 'ringicellcurrency');
            }
        }
        if( count($matchedArray)>=4 ){
            if( $matchedArray[3] ){
                $retArray['id'] = $matchedArray[3];
            }
        }
        if( count($matchedArray)>=5 ){
            if( $matchedArray[4] ){
                $classesInExcel = explode(',', $matchedArray[4]);
                $retArray['classes'] = array_merge($retArray['classes'], $classesInExcel);
            } 
        }

        //print_r('parseInputString, classes='.count($retArray['classes']));
        //print_r($retArray['classes']);

        return $retArray;
    }

    public function processImage($dom){
        $this->log('DOMEditor processImage called', 'debug');
        $matchRegex = '/^image:.+/';

        $styles = Array('class' => 'ringicelllogo');
        foreach ($dom->getElementsByTagName('td') as $text){
            //print_r('processLabel:');
            //print_r($text);
            if( preg_match($matchRegex, $text->textContent, $matches) ){
                //print_r($text);

                $dataArray = $this->parseInputString($matches[0], $styles);

                $imgfilepath  = '../img/'. $dataArray['name'];
                //$type       = $dataArray['type'];
                $name = $id = $dataArray['id'];
                $classes    = $dataArray['classes'];

                $element = $dom->createElement('img');
            
                $element->setAttribute('src', $imgfilepath);
                foreach ( $styles as $key=>$value ){
                    $element->setAttribute( $key, $value );
                }
                $element->setAttribute( 'id', $id );
                $element->setAttribute( 'class', implode(" ", $classes) );
                $element->setAttribute( 'name', $name );

                $replace = $text->cloneNode();
                $replace->appendChild($element);

                $text->parentNode->replaceChild($replace, $text);
            }
        }

        return $dom;
    }

    public function processLabel($dom){
        $this->log('DOMEditor processLabel called', 'debug');
        $matchRegex = '/label:.+/';

        $styles = Array('class' => 'ringicell');
        foreach ($dom->getElementsByTagName('td') as $text){
            //print_r('processLabel:');
            //print_r($text);
            if( preg_match($matchRegex, $text->textContent, $matches) ){
                //print_r($text);

                $dataArray = $this->parseInputString($matches[0], $styles);

                $label      = $dataArray['name'];
                //$type       = $dataArray['type'];
                $name = $id = $dataArray['id'];
                $classes    = $dataArray['classes'];

                /*
                $element = $dom->createElement('input');
                $element->setAttribute('value', $label);
                foreach ( $styles as $key=>$value ){
                    $element->setAttribute( $key, $value );
                }
                $element->setAttribute( 'id', $id );
                $element->setAttribute( 'class', implode(" ", $classes) );
                $element->setAttribute( 'name', $name );

                $replace = $text->cloneNode();
                $replace->appendChild($element);

                $text->parentNode->replaceChild($replace, $text);
                */
                
                $text->setAttribute( 'id', $id );
                $text->setAttribute( 'class', implode(" ", $classes) );
                $text->setAttribute( 'name', $name );
                $text->nodeValue = $label;
            }
        }

        return $dom;
    }

    //   input:names:type:id:classes
    public function processInputStrings($dbArray, $dom, $readonlyColumns=array(), $uploadIgnoredColumns=array()){
        $matchRegex = '/input:.+/';
        return $this->processStrings($matchRegex, $dbArray, $dom, $readonlyColumns, $uploadIgnoredColumns);
    }

    private function isNumberType($typeInDb){
        $numTypes = array('int', 'integer', 'float', 'double', 'decimal1', 'decimal2', 'currency');
        return in_array($typeInDb, $numTypes);
    }

    private function getNumberStep($type){
        $integer_type = array('int', 'integer');
        $decimal_type = array('float', 'double');
        $decimal1_type = array('decimal1');
        $decimal2_type = array('decimal2', 'currency');
        
        if( in_array($type, $integer_type) ) return '1.0';
        if( in_array($type, $decimal_type) ) return '0.01'; // FIXME
        if( in_array($type, $decimal1_type) ) return '0.1'; 
        if( in_array($type, $decimal2_type) ) return '0.01'; 

        return '0.01';
    }

    public function processStrings($matchRegex, $dbArray, $dom, $readonlyColumns=array(), $uploadIgnoredColumns=array()){
        $styles = Array('class' => 'ringicell'); 
        $this->log('DOMEditor processString, dbArray passed', 'debug');
        //$this->log($dbArray, 'debug');
        foreach ($dom->getElementsByTagName('td') as $text){
            if( preg_match($matchRegex, $text->textContent, $matches) ){
                // FIXME 
                // hidden for td is set earlier in AppController
                // so the containee input will be set to hidden
                $hidden = false;
                if( $text->getAttribute( 'hidden' ) ){
                    $hidden = true;
                    $text->removeAttribute( 'hidden' );
                }
                //print_r($text);

                //$this->log('DOMEditor processString, matches = ','debug');
                //$this->log($matches, 'debug');
                $dataArray = $this->parseInputString($matches[0], $styles);

                $colname  = $dataArray['name'];
                $type     = $dataArray['type'];
                $id       = $dataArray['id'];
                $classes  = $dataArray['classes'];
               
                // take one value from names 
                $valFromDB = $this->getStringFromDB($dbArray, $colname);
                if( strcmp($type,'string')== 0 ){
                    $element = $dom->createElement('textarea', $valFromDB);
                }else{
                    $element = $dom->createElement('input');
                    $element->setAttribute('value', $valFromDB);
                }
                // if input is NG, original is textarea with the following value
                // $element = $dom->createElement('textarea', $valFromDB);
                foreach ( $styles as $key=>$value ){
                    $element->setAttribute( $key, $value );
                }
                $maxlength = $this->getMaxLength($type);
                $element->setAttribute( "maxlength",$maxlength );
                $element->setAttribute( 'id', $id );
                $element->setAttribute( 'name', $colname );
                if( $hidden ){
                    $element->setAttribute( 'hidden', true );
                }
                if( $this->isNumberType($type) ){
                    $element->setAttribute( 'type', 'number' );
                    // FIXME: step shouldn't always be 0.01 but finer
                    $element->setAttribute( 'step', $this->getNumberStep($type) );
                }
                if( !empty($readonlyColumns) ){
                    if( strcmp($readonlyColumns[0], '*')==0 ||
                        in_array($colname, $readonlyColumns)){
                        $element->setAttribute('readonly', true );
                    }
                }
                if( !empty($uploadIgnoredColumns) ){
                    if( in_array($colname, $uploadIgnoredColumns) ){
                        array_push($classes, 'uploadignoredcell');
                        $element->setAttribute('readonly', true );
                    }
                }

                $element->setAttribute( 'class', implode(" ", $classes) );
                $replace = $text->cloneNode();
                $replace->appendChild($element);

                $text->parentNode->replaceChild($replace, $text);
            }
        }

        return $dom;
    }
    private function convertToMDY($dateLocalFormat){
        if(empty($dateLocalFormat)) return NULL;

        return date("m/d/Y", strtotime($dateLocalFormat));
    }
    //   date:names:date:id:classes
    public function processDate($dbArray, $dom, $readonlyColumns = NULL, $uploadIgnoredColumns=array()){
        $matchRegex = '/date:.+/';
        // forward readOnly to processString so a non selectable word is displayed
        if( strcmp($readonlyColumns[0], '*')==0 ){
            return $this->processStrings($matchRegex, $dbArray, $dom, $readonlyColumns);
        }

        $styles = Array('class' => 'ringicelldate ringicell' );
        foreach ($dom->getElementsByTagName('td') as $text){
            if( preg_match($matchRegex, $text->textContent, $matches) ){
                $dataArray = $this->parseInputString($matches[0], $styles);

                $colname  = $dataArray['name'];
                $type     = $dataArray['type'];
                $id       = $dataArray['id'];
                $classes  = $dataArray['classes'];

                $valFromDB = $this->getStringFromDB($dbArray, $colname);
                $element = $dom->createElement('input');
				
                $element->setAttribute('value', $this->convertToMDY($valFromDB));
                foreach ( $styles as $key=>$value ){
                    $element->setAttribute( $key, $value );
                }

                if( !empty($uploadIgnoredColumns) ){
                    if( in_array($colname, $uploadIgnoredColumns) ){
                        $element->setAttribute('readonly', true );
                        array_push($classes, 'uploadignoredcell');
                        //array_push($classes, 'readonly');
                    }
                }
                $element->setAttribute( 'id', $id );
                $element->setAttribute( 'class', implode(" ", $classes) );
                $element->setAttribute( 'name', $colname );
                $element->setAttribute( 'onfocus', "blur();" );
	
                if( !empty($readonlyColumns) ){
                    if( strcmp($readonlyColumns[0], '*')==0 ||
                        in_array($colname, $readonlyColumns)){
                        $element->setAttribute('readonly', true );
                    }
                }
                $replace = $text->cloneNode();
                $replace->appendChild($element);

                $text->parentNode->replaceChild($replace, $text);
            }
        }

        return $dom;
    }

    public function processCombobox($appName, $dbArray, $dom, $readonlyColumns, $blankAllowColumns = array()){
        $elemKind = 'combobox';
        return $this->processMultiInput($appName, $dbArray, $dom, $readonlyColumns, $elemKind, $blankAllowColumns = array());
    }

    public function processCheckbox($appName, $dbArray, $dom, $readonlyColumns, $blankAllowColumns = array()){
        $elemKind = 'checkbox';
        return $this->processMultiInput($appName, $dbArray, $dom, $readonlyColumns, $elemKind, $blankAllowColumns = array());
    }

    public function processRadioButton($appName, $dbArray, $dom, $readonlyColumns, $blankAllowColumns = array()){
        $elemKind = 'radio';
        return $this->processMultiInput($appName, $dbArray, $dom, $readonlyColumns, $elemKind, $blankAllowColumns = array());
    }

    private function multivalue_selected($needle, $arrayOrSingleValue){
        if( !is_array($arrayOrSingleValue) ){
            return (strcmp($needle, $arrayOrSingleValue)==0);
        }
        if( in_array($needle, $arrayOrSingleValue) ){
            return true;
        }
        return false;
    }

    public function processMultiInput($appName, $dbArray, $dom, $readonlyColumns, $elemKind, $blankAllowColumns = array()){
        $matchRegex = '/'.$elemKind.':.+/';
        // forward readOnly to processString so a non selectable word is displayed
        if( strcmp($readonlyColumns[0], '*')==0 ){
            return $this->processStrings($matchRegex, $dbArray, $dom, $readonlyColumns);
        }
        $doc = $dom->saveHTML();
        $styles = Array('style' => 'margin-bottom:5px;margin-top:5px; font-size: 13px;');
        preg_match_all($matchRegex, $doc, $matches);
        //print_r($matches);
        foreach($matches[0] as $key => $val){
            $dataArray = $this->parseInputString($val, $styles);
            //print_r( 'process pulldown:' );
            $name = $dataArray['name'];
            $type = $dataArray['type'];
            $id = $dataArray['id'];
            $classes = $dataArray['classes'];

            $options = $this->ConfigService->get_multi_options($type);
            $dom = new DOMDocument;
            if( !empty($options) ){
                if( strcmp($elemKind,'combobox')==0 ){
                    $combobox = $dom->createElement('select');
                    $combobox->setAttribute('name', $name );
                    $combobox->setAttribute('id', $id);
                    $combobox->setAttribute('class', implode(" ", $classes) );
                    foreach ( $styles as $key=>$value ){
                        $combobox->setAttribute( $key, $value );
                    }
                    foreach( $options as $o ){
                        $option = $dom->createElement('option');
                        $option->setAttribute('value', $o);
                        if( !empty($dbArray[$name]) && 
                            $o == $dbArray[$name] ){
                            $option->setAttribute('selected', true);
                        }
                        $option->nodeValue = $o;
                        $combobox->appendChild($option);
                    }
                    $dom->appendChild($combobox);
                }
                else{
                    $divElem = $dom->createElement('div');
                    foreach( $options as $o ){
                        $inputElem = $dom->createElement('input');
                        $inputElem->setAttribute('type', $elemKind);
                        if( strcmp($elemKind, 'radio')==0 ){
                            $inputElem->setAttribute('name', $name);
                        }else if( strcmp($elemKind, 'checkbox')==0 ){
                            $inputElem->setAttribute('name', $name.'[]');
                        }
                        $inputElem->setAttribute('value', $o );
                        if( !empty($dbArray[$name]) && 
                            $this->multivalue_selected($o, $dbArray[$name]) ){
                            $inputElem->setAttribute('checked', true);
                        }
                        $divElem->appendChild($inputElem);
                        //$divElem->nodeValue = $o;
                        $labelElem = $dom->createElement('label');
                        $labelElem->nodeValue = $o;
                        $divElem->appendChild($labelElem);
                        $spaceElem = $dom->createElement('br');
                        $divElem->appendChild($spaceElem);
                        $dom->appendChild($divElem);
                    }
                }
            }
            $doc = preg_replace('/'.$elemKind.':.+/', $dom->saveHTML(), $doc, 1);
        }

        $dom = new DOMDocument;
        $dom->loadHTML($doc);

        $this->log('processMultiInput, returning for '.$elemKind, 'debug');

        return $dom;
    }

    //   <$elemKind>:names:type(NameID):id:classes
    private function processPulldown($dbArray, $dom, $allOptions, $readonlyColumns, $blankAllowColumns = array()){
        $matchRegex = '/pulldown:.+/';
        // forward readOnly to processString so a non selectable word is displayed
        if( strcmp($readonlyColumns[0], '*')==0 ){
            return $this->processStrings($matchRegex, $dbArray, $dom, $readonlyColumns);
        }
        $doc = $dom->saveHTML();
        $styles = Array('style' => 'margin-bottom:5px;margin-top:5px;');
        preg_match_all($matchRegex, $doc, $matches);
        //print_r($matches);
        foreach($matches[0] as $key => $val){
            $dataArray = $this->parseInputString($val, $styles);
            //print_r( 'process pulldown:' );
            $name = $dataArray['name'];
            $type = $dataArray['type'];
            $id = $dataArray['id'];
            $classes = $dataArray['classes'];
            $options = $allOptions[$type];
            $dom = new DOMDocument;
            $pulldown = $dom->createElement('select');
            $pulldown->setAttribute('name', $name );
            $pulldown->setAttribute('id', $id);
            $pulldown->setAttribute('class', implode(" ", $classes) );
            foreach ( $styles as $key=>$value ){
                $pulldown->setAttribute( $key, $value );
            }
            
            //$option = $dom->createElement('option');
            //$option->setAttribute('value','');
            //$pulldown->appendChild($option);
            foreach( $options as $oName=>$oValue ){
                $option = $dom->createElement('option');
                $option->setAttribute('value', $oValue );
                //print_r($dbArray);
                if ($oValue == $dbArray[$name]){
                    $option->setAttribute('selected', true);
                }
                $option->nodeValue = $oValue;
                $pulldown->appendChild($option);
            }
            $dom->appendChild($pulldown);
            $doc = preg_replace('/pulldown:.+/', $dom->saveHTML(), $doc, 1);
        }

        $dom = new DOMDocument;
        $dom->loadHTML($doc);

        return $dom;
    }

    // key: e.g. 'APPROVER'. '[a-ZA-Z]+'. '_'. $i;
    public function removeExtraRowFromTable($doc, $key){
        $dom = new DOMDocument;
        $dom->loadHTML($doc);
        foreach ($dom->getElementsByTagName('td') as $elem){
            // remove extra rows
            $regEx = '/'. $key. '/';
            //echo $regEx;
            if( preg_match($regEx, $elem->textContent, $matched ) ){
                $row = $elem->parentNode;
                $table = $row->parentNode;
                $table->removeChild($row);
            }
        }
        return $dom->saveHTML();
    }

    public function prepareDbConfigurableTd($dom){
        $i=0;
        foreach ($dom->getElementsByTagName('td') as $text){
            //$text->setAttribute('style', 'editDbConfigString');
            $text->setAttribute('class', 'editDbConfig');
            $text->setAttribute('id', 'td_'.$i);
            $i++;
        }

        return $dom;
    }

    public function maskNonAuthorized($dom, $columns){
        $this->log('DOMEditor maskNonAuthorized', 'debug');
        $matchRegex = '/.+:(.+):.+/';

        foreach ($dom->getElementsByTagName('td') as $text){
            if( !preg_match($matchRegex, $text->textContent, $matches) )
                continue;
            $this->log('matches='.$matches[1], 'debug');
            if( in_array($matches[1], $columns) ){
                $this->log('setting background', 'debug');
                $text->setAttribute('style', 'background-color:rgb(220,220,220);');
                $text->setAttribute('hidden', true ); // FIXME used to control contained input
                //$this->log('childNodes=', 'debug');
                //$this->log($text->childNodes->item(0), 'debug');
            }
        }
        return $dom;
    }

    public function updateHtmlForTd($dom, $tdId, $dbConfig, $formula){
        $this->log('updateHtmlForTd, td/config='.$tdId.'/'.$dbConfig, 'debug');
        $counter = 0;
        foreach ($dom->getElementsByTagName('td') as $text){
            $currentTdId = 'td_'.$counter;
            if( strcmp($currentTdId, $tdId)!=0 ){
                $this->log('updateHtmlForTd, loop i:'.$counter, 'debug');
                $counter ++;
                continue;
            }
            $this->log('updateHtmlForTd, setting nodeValue for i='.$counter.' to :'.$dbConfig, 'debug');
            $text->nodeValue = $dbConfig;
            $text->setAttribute('formula', $formula);
            $this->log($text, 'debug');
            break;
        }
        return $dom;
    }

    public function getCellIdToValueMap($mapperHtmlFilename, $valueHtmlFilename){
        $this->log('getCellIdToValueMap', 'debug');

        $mapperDoc = file_get_contents($mapperHtmlFilename);
        $mapperDom = new DOMDocument;
        $mapperDom->loadHTML( $mapperDoc );

        $previewDoc = file_get_contents($valueHtmlFilename);
        $previewDom = new DOMDocument;
        $previewDom->loadHTML( $previewDoc );

        $cellIds         = $mapperDom->getElementsByTagname('td');
        $valuesInPreview = $previewDom->getElementsByTagname('td');
//        $this->log('getCellIdToValueMap mapper cellIds=', 'debug');
//        $this->log($cellIds, 'debug');
//        $this->log('getCellIdToValueMap values in preview=', 'debug');
//        $this->log($valuesInPreview, 'debug');

        $cellIdPattern = '/[A-Z]*[0-9]*/';
        $formatPattern = '/[0-9a-zA-Z_\-\+]*:[0-9a-zA-Z_\-\+]*:.*/';
        $valueMapper = Array();
        $formulaMapper = Array();
        for( $i=0; $i<$cellIds->length; $i++){
            $cellId = $cellIds->item($i);
            $value  = $valuesInPreview->item($i);

            // ignore mulformatted id or format 
/*
$this->log('### CELL ID='. $cellId->nodeValue, 'debug');
            if( !preg_match($cellIdPattern, $cellId->nodeValue, $matches) ||
                !preg_match($formatPattern, $value->nodeValue, $matches) ){
                continue;
            }
*/

            // create A1->input:colname:type... sort of mapping
            $valueMapper[$cellId->nodeValue] = $value->nodeValue;
            $this->log('getCellTdToValueMap,value extracted='.$value->nodeValue, 'debug');

            // save formula
            // create id->formula mapping
            $formula = $value->getAttribute('formula');
            $this->log('getCellTdToValueMap,formula extracted='.$formula, 'debug');
            if( $formula ){
                $id = explode(":", $value->nodeValue)[1];
                $formulaMapper[$id] = $formula;
            }
        }
//        $this->log('cellIdToValue:valueMapper=', 'debug');
//        $this->log($valueMapper, 'debug');
        return array( $valueMapper, $formulaMapper);
    }
/*
    private function add_richtext_toolbar(&$dom){
        $root_element = $dom->createElement('div');
        $dom->appendChild($root_element);

        $div_element = $dom->createElement('div');
        $div_element->setAttribute('class', 'btn-toolbar');
        $div_element->setAttribute('data-role', 'editor-toolbar');
        $div_element->setAttribute('data-target', '.editor');
        $root_element->appendChild($div_element);

        $btn_group_1 = $dom->createElement('div');
        $btn_group_1->setAttribute('class', 'btn-group');
        $div_element->appendChild($btn_group_1);

        $btn_dropdown = $dom->createElement('a');
        $btn_dropdown->setAttribute('class', 'btn dropdown-toggle btn-default');
        $btn_dropdown->setAttribute('data-toggle', 'dropdown');
        $btn_dropdown->setAttribute('title', 'Font');
        $btn_group_1->appendChild($btn_dropdown);

        $icon_font = $dom->createElement('i');
        $icon_font->setAttribute('class', 'icon-font');
        $btn_dropdown->appendChild($icon_font);
        $caret = $dom->createElement('b');
        $caret->setAttribute('class', 'caret');
        $btn_dropdown->appendChild($caret);
        $dropdown_menu = $dom->createElement('ul');
        $dropdown_menu->setAttribute('class', 'dropdown-menu');
        $btn_group_1->appendChild($dropdown_menu);
    }
*/

    public function processRTImage($dbArray, $dom, $readonlyColumns = NULL){
        $styles = Array('class'=>'ringicell mceImageEditor');
        $matchRegex = '/rtimage:.+/';
        return $this->processRTBase($dbArray, $dom, $matchRegex, $styles, $readonlyColumns);
    }
    public function processRichText($dbArray, $dom, $readonlyColumns = NULL){
        $styles = Array('class'=>'ringicell mceEditor');
        $matchRegex = '/richtext:.+/';
        return $this->processRTBase($dbArray, $dom, $matchRegex, $styles, $readonlyColumns);
    }
    private function processRTBase($dbArray, $dom, $matchRegex, $styles, $readonlyColumns = NULL){
        $doc = $dom->saveHTML();
        foreach ($dom->getElementsByTagName('td') as $text){
            if( preg_match($matchRegex, $text->textContent, $matches) ){
                $dataArray = $this->parseInputString($matches[0], $styles);

                $colname  = $dataArray['name'];
                $type     = $dataArray['type'];
                $id       = $dataArray['id'];
                $classes  = $dataArray['classes'];

                $valFromDB = $this->getStringFromDB($dbArray, $colname);

                $dom = new DOMDocument;
                $txt_element = $dom->createElement('textarea', $valFromDB);
                $txt_element->setAttribute( 'name', $colname);
                $txt_element->setAttribute( 'id', $id );
                $txt_element->setAttribute( 'class', implode(" ", $classes) );
                $dom->appendChild($txt_element);
                $doc = preg_replace($matchRegex, $dom->saveHTML(), $doc, 1);
            }
        }

        $dom = new DOMDocument;
        $dom->loadHTML($doc);

        return $dom;
    }
}
?>
