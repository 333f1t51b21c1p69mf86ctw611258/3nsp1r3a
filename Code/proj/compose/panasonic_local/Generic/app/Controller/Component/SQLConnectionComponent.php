<?php

App::uses('Component', 'Controller');

class SQLConnectionComponent extends Component{
    public function openSQLconnection(
        $host = 'mysql', 
        $username = 'root' , 
        $password = '', 
        $database = 'genericdata') 
    {
            $link = mysqli_connect($host, $username, $password);
            mysqli_select_db($link, $database);
            return $link;
	}
}
