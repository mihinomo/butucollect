<?php 
class Login extends DB{

    public static function showLogin(){
        require_once("./app/views/common/head.php");
        require_once("./app/views/common/login.php");
        require_once("./app/views/common/foot.php");
    }
}