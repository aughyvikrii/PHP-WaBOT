<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Mainclass LineBOT
 */

 class WaBOT {

    /**
     * Config variable, all config should be here
     */

    public $config;

    /**
     * All Route from route file
     */

    public $route;

    /**
     * Data from telegram
     */

    public $data;

    /**
     * Assign Data
     */
    public $assign = array();

    public function __construct() {

        GLOBAL $route,$config;
        
        $this->config   = $config;
        $this->route    = $route;

        ## Ambil data yang di post
        $this->get_data();

        ## Database Connection
        $this->database_connection();
    }

    public function database_connection(){
        $database = @$this->config['database'];

        if( empty($database['db']) ) return;

        $db = "db_{$database['type']}";

        if( !file_exists(BASE_PATH."/system/lib/db/{$db}.php") ) die(json_encode([
            'error' => "database file {$db} doesn't exists"
        ]));
        // die("~Error: database file {$db} doesn't exists");

        require_once BASE_PATH."/system/lib/db/{$db}.php";

        $this->db = new $db($database);
    }

    public function config($key=false){
        if( !$key ) return $this->config;
        return isset($this->config[$key]) ? $this->config[$key] : false;
    }

    public function data($key=false){
        if( !$key ) return $this->data;
        return isset($this->data[$key]) ? $this->data[$key] : false;
    }

    public function dataType(){
        $type       = @$this->data['events'][0]['message']['type'];
        $eventType  = $this->data['events'][0]['type'];

        if(         $type == 'text'     ) return 'text';
        else if(    $type == 'sticker'  ) return 'sticker';
        else if(    $type == 'audio'    ) return 'audio';
        else if(    $type == 'image'    ) return 'image';
        else if(    $type == 'location' ) return 'location';
        else if( $eventType == 'postback' ) return 'postback';
        else if( $eventType == 'join' ) return 'join';
        else if( $eventType == 'follow' ) return 'follow';
        else {
            if( DEBUG ) $this->reply("~reply type not recognized");
            die(json_encode([
                'error' => "reply type not recognized"
            ]));
        }
        
    }

    private function get_data() {
        $data = file_get_contents('php://input');
        
        $this->data = isset($data) ? json_decode($data,true) : array();
    }
    
    public function get_text(){
        
        if( isset($this->data['events'][0]['message']['text']) )
            return preg_replace('/\s+/', ' ', $this->data['events'][0]['message']['text']);
            
        return false;
    }
    
    
    public function get_id(){
        if( isset($this->data['events'][0]['source']['userId']) )
            return $this->data['events'][0]['source']['userId'];
            
        return false;
    }
    
    public function assign($key,$value=false){

        if( is_array($key) ) {
            $merge = array_merge($this->assign,$key);
        } else if( is_array($key)==false  && $value ) {
            $merge = array_merge($this->assign,array(
                $key    => $value
            ));
        } else return;

        $this->assign = $merge;
    }

    public function view($file,$data=array()) {

        $path = BASE_PATH."/views/{$file}.php";

        if( !file_exists($path) ){
            if( DEBUG ) $this->response("~File {$file} doesn't exists.");
            die(json_encode([
                'error' => "Views {$file} doesn't exists."
            ]));
        }

        if( empty($this->assign) ) $this->assign = array();
        if( !empty($data) ) $this->assign = $data;

        extract($this->assign);
        ob_start();
        include_once $path;
        return ob_get_clean();
    }

    public function echoJson($array){
        echo json_encode($array);
        die;
    }

    public function response($text,$replyToken=false) {
        if( !$replyToken ) $replyToken = $this->data['events'][0]['replyToken'];

        if ( !$replyToken ) {
            $log = "ERROR[".LOG_ID."] Reply Token Not Defined";
            error_log($log);
            echoJson([ 'error' => $log ]);
        } else {
            ReplyChat($replyToken,$text);
        }

    }

    public function responseError($text){
        log_error($text);
        self::response($text);
    }

    public function sendMessage($to,$message,$notif=false){

        if( !is_array($message) ){
            $message = [
                [
                    'type'  => 'text',
                    'text'  => $message
                ]
            ];
        }

        $data = [
            'to'        => $to,
            'messages'   => $message,
            'notificationDisabled'  => $notif
        ];

        PushMessage($data);
    }

    public function segment($index=null){
        $event_type     = $this->get('event_type');
        $event_source   = $this->get('event_source');
        $event_category = $this->get('event_category');
        $text           = $this->get('text');

        $index -= 1;

        if( $event_category == 'text' ){
            $split = explode(" ",strtolower($text));
        } else if ( $type == 'postback' ) {
            $split_1 = explode("&",$this->data['events'][0]['postback']['data']);
            $postdata = [];
            foreach($split_1 as $data){
                $split_2 = explode("=",$data);
                $postdata[$split_2[0]] = $split_2[1];
            }

            $split = explode(" ",$postdata['params']);
        } else {
            return array();
        }

        $split = array_values($split);
        
        return ($index) === null ? $split : @$split[$index];
    }

    public function get_source(){

        return $this->data['events'][0]['source']['type'];

    }

    public function get($conf){

        switch(strtolower($conf)){

            case 'type':
                return @$this->data['type'];
            break;

            case 'source':
                return @$this->data['source'];
            break;

            case 'from':
                return @$this->data['from'];
            break;

            case 'device':
                return @$this->data['device'];
            break;

            /**
             * event_category
             * this is based on message type that user send
             */

            case 'event_category':

                $category = @$this->data['events'][0]['message']['type'];

                if( !$category ) return 'unknown';
                else return $category;

            break;

            /**
             * text
             */

            case 'text':
                return @$this->data['text'];
            break;

            case 'user_id';
                return @$this->data['events'][0]['source']['userId'];
            break;

            case 'group_id';
                return @$this->data['events'][0]['source']['groupId'];
            break;
            
            case 'room_id';
                return @$this->data['events'][0]['source']['roomId'];
            break;

            default:
                die("case '{$conf}' unknown");
            break;
        }

        return false;
    }
 }