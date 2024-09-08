<?php 

date_default_timezone_set('Asia/Kolkata');
session_start();
ob_start();
define('BASE_PATH','/');


// # variables
$user_agent     =   $_SERVER['HTTP_USER_AGENT'];
$today = date("F j, Y, g:i a");
$date = date('Y-m-d');


require_once('autoloader.php');




//parseUrl($_SERVER['REQUEST_URI']);


//echo var_dump($_SERVER);


require_once('route/route.php');
//require_once('app/views/index.php');
//var_dump($_SERVER);
//var_dump($_POST);

