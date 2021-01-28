<?php

class Notifications{

    public static function addNotification(){
        Request::checkRequest(['matcher_uuid', 'match_id', 'mood']);

        if(!is_numeric(Request::$data['mood']) || !is_numeric(Request::$data['match_id'])){
            header("Incorrect values", true, 400);
            exit;
        }
        
        if(User::doesUserNotExist(Request::$data['matcher_uuid'])){
            return json_encode(['status'=> 'nok', 'error' => 'No matching user found']);
        }

        if(!Matches::checkMatchIdAndUuid()){
            return json_encode(['status'=> 'nok', 'error' => 'No match found for id and uuid']);
        }
        
        // Add notification
        $query = 'INSERT INTO notifications (
                            matcher_uuid,
                            match_id,
                            mood,
                            adddate)
                        VALUES (?,?,?, NOW())';
        
        $stmt = up_database::prepare($query);
        $stmt->bind_param('sii', Request::$data['matcher_uuid'], Request::$data['match_id'], Request::$data['mood']);
        $stmt->execute();
        up_database::serverError($stmt);
        $stmt->close();
        
        return json_encode(['status'=> 'ok']);
    }

    public static function getHistory(){
        Request::checkRequest(['matcher_uuid', 'match_id']);

        if(!is_numeric(Request::$data['match_id'])){
            header("Incorrect values", true, 400);
            exit;
        }

        if(User::doesUserNotExist(Request::$data['matcher_uuid'])){
            return json_encode(['status'=> 'nok', 'error' => 'No matching user found']);
        }

        $notifications = array();
        $notification_id = null;
        $matcher_uuid = null;
        $mood = null;
        $adddate = null;

        // GET all notifications related to the match
        $query = 'SELECT n.id
                        , n.matcher_uuid
                        , n.mood
                        , n.adddate
                    FROM 
                        matches m
                    LEFT JOIN
                        notifications n ON n.match_id = m.id
                    WHERE 
                        (n.matcher_uuid = m.partner_1 
                        OR n.matcher_uuid = m.partner_2)
                        AND (m.partner_1 = ? OR m.partner_2 = ?)
                        AND m.id = ?
                        AND n.mood != 2
                    ORDER BY
                        adddate';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ssi', Request::$data['matcher_uuid'], Request::$data['matcher_uuid'], Request::$data['match_id']);
        $stmt->execute();
        $stmt->bind_result($notification_id, $matcher_uuid, $mood, $adddate);
        while($stmt->fetch()){
            $notifications[] = [
                'id' => $notification_id,
                'user' => $matcher_uuid,
                'mood' => $mood,
                'date' => $adddate
            ];
        }
        up_database::serverError($stmt);
        $stmt->close();

        return json_encode(['status' => 'ok', 'notifications' => $notifications]);
    }
    
}
