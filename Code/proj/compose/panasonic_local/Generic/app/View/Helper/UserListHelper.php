<?php
App::uses('AppHelper', 'View/Helper');

class UserListHelper extends AppHelper {
    private function generateHeader($aRecord){
        $out = "<tr>";
        foreach($aRecord as $aColumn){
            $out .= "<td> $aColumn </td>";
        }
        $out .= "</tr>";
        return $out; 
    }
    private function generateTableRow($headers, $aInst){
        $out = "<tr>";
        foreach($headers as $h){
            $out .= "<td>".$aInst[$h]."</td>";
        }
        $out .= "</tr>";
        return $out;
    }
    public function usersArrayToTable($usersArray){
        $this->log('usersArrayToTable, usersArray=', 'debug');
        $this->log($usersArray, 'debug');
        $out = "<h3>Confirm following user list is correct</h3>";
        $out .= "<table>";
        $headers = array('username', 'name', 'department', 'title', 'mail', 'usertype');
        $out .= $this->generateHeader($headers);
		foreach($usersArray as $num=>$tblValues){
            $userInst = $tblValues['User'];
            $out .= $this->generateTableRow($headers, $userInst);
        }
        $out .= "</table>";
        return $out;
    }
}




