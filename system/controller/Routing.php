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

        $request_type = $this->data['events'][0]['message'];

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
            echo json_encode([
                'error' => 'Route not found'
            ]);
            die;
        }

        $this->exec_route = $route_set;
    }

    private function exec() {
        $split = explode("@",$this->exec_route);
        
        $file = $split[0];
        $func = $split[1];

        if( !file_exists(BASE_PATH."/controller/{$file}.php") ) {
            $this->reply("Terjadi kesalahan! lapor pada admin, kode : #NC");
        }

        require_once BASE_PATH."/controller/{$file}.php";

        $class = new $file;

        if( !method_exists($class,$func) ) {
            $this->reply("Terjadi kesalahan! lapor pada admin, kode : #NF");
        }

        $class->$func();
    }
 }