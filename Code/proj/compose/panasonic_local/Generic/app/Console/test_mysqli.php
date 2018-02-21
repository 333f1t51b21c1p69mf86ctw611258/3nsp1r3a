<?php
$servername = "mysql";
$username = "root";
$password = "";
$dbname = 'genericdata';
$dbport = '3306';

$link = mysqli_connect($servername, $username, $password, $dbname, $dbport);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
//printf("MySQL host info: %s\n", mysql_get_host_info());
