<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * System Class Route
 */

 class Routing extends LineBOT {

    /**
     * Final exec route
     */
    private $exec_route;

    public function __construct() {
        parent::__construct();
    }

    public function run() {
        
        ## Validasi Route
        $this->routing();

        ## eksekusi class berdasarkan route
        $this->exec();
    }

    private function routing() {

        $event_type     = $this->get('event_type');
        $event_source   = $this->get('event_source');
        $event_category = $this->get('event_category');
        $text           = $this->get('text');
        $referer_id     = $this->get("{$event_source}_id");
        $log            = get_log("{$event_source}_{$referer_id}");

        if( isset($log['action']) && !empty($log['action']) ){
            $this->exec_route = $log['action'];
            return;
        }

        /**
         * events type message must send sub message with type
         * type [ sticker, audio, image, location, text ]
         * 
         * but if events type follow, join, unfollow is not send sub message
         */
        if($event_category == 'unknown' && $event_type == 'message')
            return parent::responseError("BOT does not understand the type of message you are sending! code: ".LOG_ID);

        /**
         * if the type sent is not 'message', there is an assumption that the type sent is join, follow, unfollow
         * so we create 'events type' as category
         */
        if( $event_type != 'message' ) $event_category = $event_type;

        /**
         * get route based on source
         */
        $route_group = $this->route[$event_source];

        if( isset($route_group[$event_category]) && !is_array($route_group[$event_category]) ){
            $this->exec_route = $route_group[$event_category];
            return;
        } else if ( is_array($route_group[$event_category]) == true && !empty($route_group[$event_category]) ) {

            $route_group    = $route_group[$event_category];
            $text           = strtolower($text);

            if( isset($route_group[$text]) && !is_array($route_group[$text]) ){
                $this->exec_route = $route_group[$text];
                return;
            } else {

                $text       = trim(preg_replace('/\s+/', ' ', $text));
                $first_text = substr($text,0,strpos($text," "));

                $route_like = preg_grep("/{$first_text}/", array_keys($route_group));

                foreach($route_like as $route_index => $route_name) {

                    $create_regex   = str_replace("*","(.*?)",$route_name);
                    $count_space    = substr_count($text," ");

                    if( preg_match("/$create_regex/",$text,$match) && substr_count($create_regex," ")==$count_space ){
                        $this->exec_route = $route_group[$route_name];
                        return;
                    }
                }

                if( !$this->exec_route && isset($route_group['*']) ) $this->exec_route = $route_group['*'];
                else return parent::responseError("BOT has no answer for your reply at this time! code: ".LOG_ID);

            }
        } else {
            return parent::responseError("BOT has no answer for your reply at this time! code: ".LOG_ID);
        }

        /////////////// SAMPAI SINI AJA

        $route_set  = '';
        $dataType   = $this->dataType();

        if( $request_type == 'join' ) $dataType = 'join';
        else if( $request_type == 'follow' ) $dataType = 'follow';

        $routeList  = $this->route[$this->get_source()][$dataType];

        $log        = get_log(parent::get_id());

        if( isset($log['action']) && !empty($log['action']) && !in_array($this->get_text(),['cancel','batal']) ) $dataType = 'log';

        /** 
         * If Type data is ["sticker","audio","image","location"]
         */
        if( in_array($dataType,["sticker","audio","image","location","join","follow"]) ) {
            $route_set = @$routeList;
        }
        
        /**
         * If type data is text
         */

        else if( $dataType == 'text' ){
            $text       = strtolower(@$this->get_text());
            $route_set  = @$routeList[$text];

            if( !$route_set ) {
                $split = explode(" ", $text);
                $query = $split[0];

                $search = preg_grep("/{$query} /", array_keys($routeList));
                
                $found = false;
                if( !empty($search) ) foreach($search as $key) {

                    $preg_regex = str_replace("*","(.*?)",$key);

                    if( preg_match("/".$preg_regex."/",$text)==true ){
                        $found = $key;
                    }
                }
                else { $found = "*"; }
                
                $route_set = @$routeList[$found];
            }
        }

        /**
         * if type postback
         * postback must return data with param controller & function
         */
        else if( $dataType == 'postback' ){
            $data = $this->data['events'][0]['postback']['data'];

            $explode = explode("&",$data);
            $params = [];
            foreach($explode as $str){
                $x = explode("=",$str);
                $params[trim($x[0])] = $x[1];
            }

            $route_set = $params['route'];
        }

        /**
         * Log From last action
         */
        else if ( $dataType == 'log' ){
            $route_set = $log['action'];
        }

        if( !$route_set ) {
            if( $this->get_source() == 'user' ){
                if( DEBUG ) $this->reply("Maksudnya '{$this->get_text()}' apa?");
            }
            die(json_encode([
                'error' => 'Route not found'
            ]));
        }

        $this->exec_route = $route_set;
    }

    private function exec() {
        $split = explode("@",$this->exec_route);
        
        $controller_name = $split[0];
        $function_name = $split[1];

        if( !file_exists(BASE_PATH."/controller/{$controller_name}.php") ) {
            return parent::responseError("An error has occurred, report to the admin! [NC-".LOG_ID."]");
        }

        require_once BASE_PATH."/controller/{$controller_name}.php";

        $class = new $controller_name;

        if( !method_exists($class,$function_name) ) {
            return parent::responseError("An error has occurred, report to the admin! [NF-".LOG_ID."]");
        }

        $class->$function_name();
    }
 }