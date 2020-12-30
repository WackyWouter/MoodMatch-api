<?php

class Matches{

    public static function createMatch(){
        Request::checkRequest(['matcher_uuid', 'partner_uuid']);

        if(self::checkpartnerAlready(Request::$data['partner_uuid'])){
            return json_encode(['status' => 'nok', 'message' => 'The partner you\'re trying to match with is already matched with someone else.']);
        }
        if(self::checkpartnerAlready(Request::$data['matcher_uuid'])){
            return json_encode(['status' => 'nok', 'message' => 'You already have a partner. Please use the changePartner action to change partners.']);
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

        if(self::checkpartnerAlready(Request::$data['partner_uuid'])){
            return json_encode(['status' => 'nok', 'message' => 'The partner you\'re trying to match with is already matched with someone else.']);
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
        self::deletePartner();

        return json_encode(['status'=> 'ok']);
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