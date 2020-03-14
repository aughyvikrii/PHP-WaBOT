<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Maincontroller
 */

class Main_Controller extends LineBOT {

    public $userdata = false;

    public function __construct() {
        parent::__construct();
        self::checkAccount();
    }
    
    public function not_set() {
        $text = "~Error";

        $this->response($text);
    }
    
    public function text() {
        return $this->reply("Maksudnya '".$this->get_text()."' apa ?");
    }

    public function checkAccount() {
        $userId = @$this->data['events'][0]['source']['userId'];
        if( !$userId ) return;

        $check = $this->db->query("SELECT * FROM users WHERE user_id = ?",[$userId]);

        if( $check->num_rows == 0 ) {

            $user_data = self::getAccountData($userId);

            $insert = [
                'user_id'        => $userId,
                'display_name'  => $user_data['displayName'],
                'picture_url'   => $user_data['pictureUrl'],
                'balance'       => 0,
                'balance_used'  => 0,
                'status'        => 'active'
            ];

            $res = $this->db->insert("users",$insert);
        }

        $check = $this->db->query("SELECT * FROM users WHERE user_id = ?",[$userId]);

        $this->userdata = $check->fetch_object();
    }

    public function getAccountData($userId=false){
        if( !$userId ) $userId = @$this->data['events'][0]['source']['userId'];

        if( !$userId ) return [ "error" => "user id not define" ];

        return GetUserAccount($userId);
    }

    public function user($conf,$default=false){
        return @$this->userdata->$conf ? $this->userdata->$conf : $default;
    }

    public function color_code($type=false){
        
        if( $type == 'primary' ) return '#0275d8';
        else if( $type =='success' ) return '#5cb85c';
        else if( $type =='info' ) return '#5bc0de';
        else if( $type =='warning' ) return '#f0ad4e';
        else if( $type =='danger' ) return '#d9534f';
        else if( $type =='black' ) return '#292b2c';
        else if( $type =='white' ) return '#f7f7f7';
        else return '#666666';

    }
}