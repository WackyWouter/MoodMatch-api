<?php

class Request{
    public static $data = null;
    
    public static function decrypt($text){
        return openssl_decrypt($text, METHOD, KEY, 0, IV);
    }

    public static function checkRequest(array $requiredPostArr)
    {
        foreach($requiredPostArr as $var){
            if(!isset(self::$data[$var])){
                header('Missing ' + $var, true, 400);
                exit;
            }
        }
    }


}