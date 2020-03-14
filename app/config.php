<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Simple Configuration
 */

$config = [

    // Bot Identify
    "bot_name"              => "",
    "bot_link"              => "",
    "channel_secret"        => "",
    "channel_access_token"  => "",

    // Database config
    "database"  => [
        "type"  => "mysql",
        "host"  => "",
        "user"  => "",
        "pass"  => "",
        "db"    => ""
    ],

    // Debug BOT
    "debug" => false,
];