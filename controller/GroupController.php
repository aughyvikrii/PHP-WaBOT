<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * GroupController
 * sample controller
 */

class GroupController extends Controller {

    public function join() {
        return $this->response("Thank you for adding me to this group");
    }

    public function leave() {
        $this->response("Goodbye :(");
        $this->leaveGroup();
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