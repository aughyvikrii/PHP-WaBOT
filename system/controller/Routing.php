<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * System Class Route
 */

 class Routing extends WaBOT {

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
        
        $type           = $this->get('type');
        $source         = $this->get('source');
        $referer_id     = $this->get("{$source}_id");
        $from           = $this->get('from');
        $device         = $this->get('device');
        $log            = get_log("{$from}_{$source}_{$device}");
        $text           = $this->get('text');

        if( isset($log['action']) && !empty($log['action']) ){
            $this->exec_route = $log['action'];
            return;
        }

        /**
         * if the type sent is not 'message', there is an assumption that the type sent is image, video, location etc
         * so we create 'events type' as category
         */

        /**
         * get route based on source
         */
        $route_group = $this->route[$source];
        if( isset($route_group[$type]) && !is_array($route_group[$type]) ){
            $this->exec_route = $route_group[$type];
            return;
        } else if ( is_array($route_group[$type]) == true && !empty($route_group[$type]) ) {
            $route_group    = $route_group[$type];
            $text           = strtolower($text);

            if( isset($route_group[$text])){
                $this->exec_route = $route_group[$text];
                return;
            } else {
                $text       = trim(preg_replace('/\s+/', ' ', $text));
                preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $text, $matches);
                $split_text = $matches[0];
                
                if(count($split_text) > 1) {
                    $first_text = $split_text[0];
                    $route_like = preg_grep("/{$first_text}/", array_keys($route_group));

                    $create_route_proto = "";
                    $_no=1;
                    foreach($split_text as $segment) {
                        if($_no=='1') $create_route_proto .= $segment." ";
                        else $create_route_proto .= "* ";
                        $_no++;
                    }
                    $create_route_proto = trim($create_route_proto);

                    if( isset($route_group[$create_route_proto]) ) $this->exec_route = $route_group[$create_route_proto];
                }

                if( !$this->exec_route && isset($route_group['*']) ) $this->exec_route = $route_group['*'];
                elseif(!$this->exec_route) {
                    echo "BOT has no answer for your reply at this time! code: ".LOG_ID;
                    exit();
                }

            }
        } else {
            echo "BOT has no answer for your reply at this time! code: ".LOG_ID;
            exit();
        }
    }

    private function exec() {
        $split = explode("@",$this->exec_route);
        $controller_name = $split[0];
        $function_name = $split[1];

        if( !file_exists(BASE_PATH."/controller/{$controller_name}.php") ) {
            echo ("An error has occurred, report to the admin! [NC-".LOG_ID."]");
            exit();
        }
        
        require_once BASE_PATH."/controller/{$controller_name}.php";

        $class = new $controller_name;
        
        if( !method_exists($class,$function_name) ) {
            echo "An error has occurred, report to the admin! [NF-".LOG_ID."]";
            exit();
        }

        $class->$function_name();
    }
 }