<?php

class Dashboard extends DB {
    public static function collectionToday($aid){
        $i =0;
        $dated = date("Y-m-d");
        $d = DB::queryt("select * from loan_deposits where agent='$aid' and dated='$dated'");

        while($row = $d->fetch(PDO::FETCH_ASSOC)){
            $i = $i + $row['amount'];
        }
        
        return $i;
    }

    public static function collectiondated($aid,$dated){
        $i =0;
        $d = DB::queryt("select * from loan_deposits where agent='$aid' and dated='$dated'");

        while($row = $d->fetch(PDO::FETCH_ASSOC)){
            $i = $i + $row['amount'];
        }
        
        return $i;
    }

    public static function customerToday($aid){
        $dated = date("Y-m-d");
        $d = DB::query("select * from loan_deposits where agent='$aid' and dated='$dated'");
        $count = count($d);
        return $count;
    }

    public static function customerdated($aid,$dated){
        $d = DB::query("select * from loan_deposits where agent='$aid' and dated='$dated'");
        $count = count($d);
        return $count;
    }

    public static function showDailyDeposit($agent,$dated){
        $q = DB::query("select * from loan_deposits where agent='$agent' and dated='$dated' and type='daily'");
        if(!empty($q)){
            echo '<div class="section mt-2">
                <div class="card">
                    <ul class="listview flush transparent simple-listview">';
        }

        foreach($q as $r){
            $lid = $r['lid'];
            $loan = DB::queryRow("select * from loans where lid='$lid'");
            $uid = $loan['cid'];
            $customer = DB::queryRow("select * from users where uid='$uid'");
            echo "<li>".$customer['c_name']."<span class='badge badge-info'>".$r['amount']."</span></li>";
        }
        if(!empty($q)){
            echo "</ul>
                        </div>
                    </div>";
        }
                    
    }
    public static function showRdDeposit($agent,$dated){
        $q = DB::query("select * from loan_deposits where agent='$agent' and dated='$dated' and type='rd'");
        if(!empty($q)){
            echo '<div class="section mt-2">
                    <div class="card">
                        <ul class="listview flush transparent simple-listview">';
        }

        foreach($q as $r){
            $lid = $r['lid'];
            $loan = DB::queryRow("select * from loans where lid='$lid'");
            $uid = $loan['cid'];
            $customer = DB::queryRow("select * from users where uid='$uid'");
            echo "<li>".$customer['c_name']."<span class='badge badge-info'>".$r['amount']."</span></li>";
        }
        if(!empty($q)){
            echo "</ul>
                    </div>
                </div>";
        }
                    
    }
}