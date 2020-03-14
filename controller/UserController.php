<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * UserController
 * sample controller
 */

class UserController extends Controller {

    public function follow() {
        return $this->response("Thank you for adding this BOT");
    }

    public function unfollow() {
        // forget all memories with him
    }

    public function not_understand() {
        return $this->response("Oh baby i don't understand what is '{$this->get('text')}' means :(");
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

    public function image() {
        return $this->response("You send a picture");
    }

    public function location() {

        $response = "You send the location";
        $response .= "\name : ".$this->data['events'][0]['message']['address'];
        $response .= "\nlatitude : ".$this->data['events'][0]['message']['latitude'];
        $response .= "\nlongitude : ".$this->data['events'][0]['message']['longitude'];

        return $this->response($response);
    }

    public function audio() {
        return $this->response("You sent a voice recording");
    }

    public function sticker() {
        return $this->response("you send a sticker");
    }

    public function file(){
        $response = "You send a file";
        $response .= "\nname : ".$this->data['events'][0]['message']['fileName'];
        $response .= "\nsize : ".($this->data['events'][0]['message']['fileSize'] / 1000000);

        return $this->response($response);
    }
}