<?php 


spl_autoload_register('myAutoloader');

function myAutoloader($className)
{

    if(file_exists('./app/classes/'.$className.'.php')){
        require_once('./app/classes/'.$className.'.php');
    }elseif(file_exists('./app/classes/extras/'.$className.'.php')){
        require_once(('./app/classes/extras/'.$className.'.php'));
    }elseif(file_exists('./app/model/'.$className.'.php')){
        require_once(('./app/model/'.$className.'.php'));
    }
   
}

function parseUrl($url){
    $exp = explode('/',$url);
    return $exp;
}


?>