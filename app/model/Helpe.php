<?php

class Helpe extends DB{
    public static function getRD($lid){
        $q = DB::query("select * from loans where lid='$lid'");
        return $q[0];
    }

    public static function rdCustomer($cid){
        $q = DB::query("select * from users where uid='$cid'");
        return $q[0];
    }

    public static function checkGenuine($lid,$dated){
        $row=DB::query("select * from loan_deposits where lid='$lid' and dated='$dated'");
        if(empty($row)){
            return "tru";
        }else{
            return "fall";
        }
    }

}