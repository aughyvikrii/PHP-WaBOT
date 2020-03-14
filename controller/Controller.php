<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Controller
 * This controller is loaded by system,
 * so use this controller as global controller
 */

class Controller extends LineBOT {

    public function GetUserAccount($userId=false){
        if( $userId ) $userId = $this->get('user_id');
        if( !$userId ) return false;

        return GetUserAccount($userId);
    }

    public function leaveGroup($groupId=false){
        if(!$groupId) $groupId = $this->get("group_id");
        if( !$groupId ) return parent::responseError("which group? [".LOG_ID."]");

        leaveGroup($groupId);
    }


    public function leaveRoom($roomId=false){
        if(!$roomId) $roomId = $this->get("room_id");
        if( !$roomId ) return parent::responseError("which room? [".LOG_ID."]");

        leaveRoom($roomId);
    }
}