<?php

class User{

    public static function newUser(){
        Request::checkRequest(['device_id']);

        $uuid4 = up_crypt::uuid4();
        $match_uuid = up_crypt::uuid4();

        $query = 'INSERT INTO users (
                            user_uuid,
                            matcher_uuid,
                            device_id,
                            adddate)
                        VALUES ( ?,?,?, NOW())';
        
        $stmt = up_database::prepare($query);
        $stmt->bind_param('sss', $uuid4, $match_uuid, Request::$data['device_id']);
        $stmt->execute();
        up_database::serverError($stmt);
        $stmt->close();
        
        return json_encode(['status'=> 'ok', 'matcher_uuid' => $match_uuid]);
    }

    public static function updateDeviceId(){
        Request::checkRequest(['device_id', 'matcher_uuid']);

        if(self::doesUserExist(Request::$data['matcher_uuid'])){
            return json_encode(['status'=> 'nok', 'error' => 'No matching user found']);
        }

        $query = 'UPDATE users SET device_id = ? WHERE matcher_uuid = ?';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ss', Request::$data['device_id'], Request::$data['matcher_uuid']);
        $stmt->execute();
        up_database::serverError($stmt);
        $stmt->close();
        
        return json_encode(['status'=> 'ok']);
    }
    
    public static function doesUserExist($matcher_uuid){

        $user = null;
        $query = 'SELECT user_uuid FROM users WHERE matcher_uuid = ?';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('s', $matcher_uuid);
        $stmt->execute();
        $stmt->bind_result($user);
        $stmt->fetch();
        up_database::serverError($stmt);
        $stmt->close();

        return $user != null;
    }
}