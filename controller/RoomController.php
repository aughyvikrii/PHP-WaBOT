<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * RoomController
 * sample controller
 */

class RoomController extends Controller {

    public function join() {
        return $this->response("Thank you for adding me to this room");
    }

    public function leave() {
        $this->response("Goodbye :(");
        $this->leaveRoom();
    }

    public function halo() {
        $message = 'Oh hai';
        if( $userId = $this->get('user_id') ){

            $user_account = parent::GetUserAccount($userId);

            $message .= " ".$user_account['displayName'];
            
        }

        return $this->response($message);
    }

    public function say_what(){
        $segment = $this->segment(2);

        return $this->response("{$segment}");
    }
}