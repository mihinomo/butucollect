<?php

class Agent extends DB {
    public static function getAgent($aid){
        $q = DB::query("select * from agents where aid='$aid'");
        return $q[0];
    }
}