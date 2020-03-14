<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");


## Load Route config
require_once BASE_PATH."/app/route.php";

## Load Main config
require_once BASE_PATH."/app/config.php";

## Load Function
require_once BASE_PATH."/system/lib/function.php";

## Load Class TelegramBOT
require_once BASE_PATH."/system/controller/LineBOT.php";

## Load Class Routing
require_once BASE_PATH."/system/controller/Routing.php";

define("DEBUG", @$config['debug'] );

define("CHANNEL_SECRET", @$config['channel_secret']);
define("CHANNEL_ACCESS_TOKEN", @$config['channel_access_token']);

## Load Custom Function
require_once BASE_PATH."/lib/function.php";

## Load Main_Controller
require_once BASE_PATH."/controller/Main_Controller.php";

if( DEBUG ) {
    $data = file_get_contents('php://input');
    file_put_contents(BASE_PATH."/log/".time().'.json',$data);
}