<?php
App::uses('Component', 'Controller');

class SecurityComponent extends Component {

    public function hideColumns(&$dbArray, $columnsToHide){
        foreach($columnsToHide as $column){
            if(array_key_exists($column, $dbArray) ){
                //$dbArray[$column] = "";
                unset($dbArray[$column]);
            }
        }
    }
}
