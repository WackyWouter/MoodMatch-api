<?php

class Matches{

    public static function createMatch(){
        Request::checkRequest(['matcher_uuid', 'partner_uuid']);

        if(User::doesUserNotExist(Request::$data['matcher_uuid'] || User::doesUserNotExist(Request::$data['partner_uuid']))){
            return json_encode(['status'=> 'nok', 'error' => 'No matching user found']);
        }

        if(self::checkpartnerAlready(Request::$data['partner_uuid'])){
            return json_encode(['status' => 'nok', 'error' => 'The partner you\'re trying to match with is already matched with someone else.']);
        }
        if(self::checkpartnerAlready(Request::$data['matcher_uuid'])){
            return json_encode(['status' => 'nok', 'error' => 'You already have a partner. Please use the changePartner action to change partners.']);
        }

        // Create new match
        $query = 'INSERT INTO matches (
                            partner_1,
                            partner_2,
                            adddate)
                        VALUES (?,?, NOW())';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ss', Request::$data['matcher_uuid'], Request::$data['partner_uuid']);
        $stmt->execute();
        up_database::serverError($stmt);
        $id = $stmt->insert_id;
        $stmt->close();
        
        return json_encode(['status'=> 'ok', 'match' => $id]);
    }

    public static function changePartner(){
        Request::checkRequest(['matcher_uuid', 'partner_uuid']);

        if(User::doesUserNotExist(Request::$data['matcher_uuid'] || User::doesUserNotExist(Request::$data['partner_uuid']))){
            return json_encode(['status'=> 'nok', 'error' => 'No matching user found']);
        }

        if(self::checkpartnerAlready(Request::$data['partner_uuid'])){
            return json_encode(['status' => 'nok', 'error' => 'The partner you\'re trying to match with is already matched with someone else.']);
        }

       self::deletePartner();

        // create new match with new partner
        $query = 'INSERT INTO matches (
            partner_1,
            partner_2,
            adddate)
        VALUES (?,?, NOW())';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ss', Request::$data['matcher_uuid'], Request::$data['partner_uuid']);
        $stmt->execute();
        up_database::serverError($stmt);
        $id = $stmt->insert_id;
        $stmt->close();

        return json_encode(['status'=> 'ok', 'match' => $id]);
    }

    // TODO make function to delete partner
    public static function resetPartner(){
        Request::checkRequest(['matcher_uuid']);

        if(User::doesUserNotExist(Request::$data['matcher_uuid'])){
            return json_encode(['status'=> 'nok', 'error' => 'No matching user found']);
        }
        
        self::deletePartner();

        return json_encode(['status'=> 'ok']);
    }

    public static function currentStatus(){
        Request::checkRequest(['matcher_uuid', 'match_id']);

        if(User::doesUserNotExist(Request::$data['matcher_uuid'])){
            return json_encode(['status'=> 'nok', 'error' => 'No matching user found']);
        }


        $matches_id = null;
        $partner1 = null;
        $partner1_mood = null;
        $partner2 = null;
        $partner2_mood = null;

        // Get the mood of both partners in the match
        $query = 'SELECT m.id
                    , m.partner_1
                    , (SELECT u.mood FROM users u WHERE u.matcher_uuid = m.partner_1)
                    , m.partner_2
                    , (SELECT u.mood FROM users u WHERE u.matcher_uuid = m.partner_2)
                FROM 
                    matches m
                WHERE 
                    (m.partner_1 = ? OR m.partner_2 = ?)
                    AND m.id = ?';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ssi', Request::$data['matcher_uuid'], Request::$data['matcher_uuid'], Request::$data['match_id']);
        $stmt->execute();
        $stmt->bind_result($matches_id, $partner1, $partner1_mood, $partner2, $partner2_mood);
        $stmt->fetch();
        up_database::serverError($stmt);
        $stmt->close();

        if($matches_id == null){
            return json_encode(['status' => 'nok', 'error' => 'No matching results found']);
        }
        if($partner1_mood === null || $partner2_mood === null){
            return json_encode(['status' => 'nok', 'error' => 'Something is wrong with your match. Please contact the creator or re-match with your partner.']);
        }

        if($partner1 == Request::$data['matcher_uuid']){
            return json_encode(['status' => 'ok', 'you' => $partner1_mood, 'partner' => $partner2_mood]);
        }else {
            return json_encode(['status' => 'ok', 'you' => $partner2_mood, 'partner' => $partner1_mood]);
        }
    }

    // check if new partner is already matched with someone
    private static function checkpartnerAlready($partner_uuid){
        $partner = null;
        $query = 'SELECT partner_1 FROM matches WHERE partner_1 = ? OR partner_2 = ? LIMIT 0,1';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ss', $partner_uuid, $partner_uuid);
        $stmt->execute();
        $stmt->bind_result($partner);
        $stmt->fetch();
        up_database::serverError($stmt);
        $stmt->close();

        if($partner != null){
            return true;
        }
        return false;
    }

    private static function deletePartner(){
        $partner_1 = null;
        $partner_2 = null;

        // Get old partner uuid
        $query = 'SELECT partner_1, partner_2 FROM matches WHERE partner_1 = ? OR partner_2 = ? LIMIT 0,1';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ss', Request::$data['matcher_uuid'], Request::$data['matcher_uuid']);
        $stmt->execute();
        $stmt->bind_result($partner_1, $partner_2);
        $stmt->fetch();
        up_database::serverError($stmt);
        $stmt->close();
 
        // Remove old matches
        $query = 'DELETE FROM matches WHERE partner_1 = ? OR partner_2 = ?';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ss', Request::$data['matcher_uuid'], Request::$data['matcher_uuid']);
        $stmt->execute();
        up_database::serverError($stmt);
        $stmt->close();

        // remove old notifications from partner and user
        $query = 'DELETE FROM notifications WHERE matcher_uuid = ? OR matcher_uuid = ?';
        $stmt = up_database::prepare($query);
        $stmt->bind_param('ss', $partner_1, $partner_2);
        $stmt->execute();
        up_database::serverError($stmt);
        $stmt->close();
    }

}