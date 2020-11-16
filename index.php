<?php

/**
 * PHP-WaBOT
 * 
 * Version      : V.1.0
 * Recode From  : PHP TelegramBOT ( https://github.com/aughyvikrii/PHP-TelegramBOT )
 * Recode By    : aughyvikrii < aughyvikrii@gmail.com >
 * Origin       : PHP-LineBOT < github@aughyvikrii >
 */

header("Content-Type: application/json");
date_default_timezone_set('Asia/Jakarta');

## time bot start
define("BOT_START",microtime(true));

define("BASE_PATH",__DIR__);

## Autoload system
require_once BASE_PATH."/system/autoload.php";

## Defines the Routing class
$routing = new Routing;

## run the function
$routing->run();