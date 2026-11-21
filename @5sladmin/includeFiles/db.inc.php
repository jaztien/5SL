<?php
define('DBHost', '127.0.0.1');//MsSQL//localhost//MySQL//127.0.0.1
define('DBPort', '3306');//MsSQL//1433//MySQL//3306
define('DBName', '5seasonlife');//MsSQL//FH//MySQL//5seasonlife
define('DBUser', 'root'); //MsSQL// sa // fh // MySQL// root
define('DBPassword', 'fullhouse2023@Jaz');    // fullhouse2023@Jaz //fullhouse2023 // 1qaZ2wsX // fh1234 MySQL//fullhouse2023@Jaz
//FTP Password: 18_Walnut_Trees

require(DOC_PATH_LAYER . "classFiles/pdoSQL.class.php");
//require("./classFiles/pdoSQL.class.php");

$DB = new DB(DBHost, DBPort, DBName, DBUser, DBPassword);
if(!$DB){
    echo "Connection Failed!";
    exit();
}

?>