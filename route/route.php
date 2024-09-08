<?php

if(isset($_GET['user'])){
    $aai = $_GET['user'];
    setcookie('login',$aai,strtotime('+15 hour'),'/');
}

Route::set('index.php',function(){
    if(!isset($_COOKIE['login'])){
        require_once("./app/views/login.php");
    }else{
        require_once("./app/views/common/head.php");
        require_once("./app/views/login.php");
        require_once("./app/views/common/footer.php");
    }

});

Route::set('login',function(){
    require_once("./app/views/login.php");
});

Route::set('dashboard',function(){
    require_once("./app/views/common/head.php");
    require_once("./app/views/dashboard.php");
    require_once("./app/views/common/foot.php");
});

Route::set('deposit',function(){
    $explode = explode("/",$_SERVER['REQUEST_URI']);
    $lid = $explode[2];
    require_once("./app/views/common/head.php");
    require_once("./app/views/deposit.php");
    require_once("./app/views/common/footer.php");
});

Route::set('prehis',function(){
    require_once("./app/views/common/head.php");
    require_once("./app/views/prehis.php");
    require_once("./app/views/common/footer.php");
});

Route::set('depositrd',function(){
    $explode = explode("/",$_SERVER['REQUEST_URI']);
    $lid = $explode[2];
    require_once("./app/views/common/head.php");
    require_once("./app/views/depositrd.php");
    require_once("./app/views/common/footer.php");
});

Route::set('depositdaily',function(){
    $explode = explode("/",$_SERVER['REQUEST_URI']);
    $lid = $explode[2];
    require_once("./app/views/common/head.php");
    require_once("./app/views/depositdaily.php");
    require_once("./app/views/common/footer.php");
});

Route::set('api', function() {
    $explode = explode("/", $_SERVER['REQUEST_URI']);
    $tp = $explode[2];
    
    if ($tp == 'search') {
        header("Access-Control-Allow-Origin: *");
        $customer = $_GET['term'];

        // Prepare the search term for use in the query to prevent SQL injection
        $searchTerm = '%' . $customer . '%';

        // Using a JOIN to optimize the query and fetch only necessary data
        $sql = "SELECT u.id, u.uid, u.c_name, l.lid FROM users u 
                INNER JOIN loans l ON u.uid = l.cid 
                WHERE u.c_name LIKE :searchTerm AND l.status = 'active' and l.loan_type!='monthly'";

        $query = DB::queryt($sql, ['searchTerm' => $searchTerm]);

        $userData = [];
        while ($ro = $query->fetch(PDO::FETCH_ASSOC)) {
            $data = [
                'id'    => $ro['id'],
                'value' => $ro['c_name'],
                'label' => '<a href="/deposit/?lid='.$ro['lid'].'">
                            <img width="50" height="50"/>
                            <span>'.$ro['c_name'].'</span>
                            <span>('.$ro['lid'].')</span>
                            </a>'
            ];
            array_push($userData, $data);
        }

        echo json_encode($userData);
    }
});












