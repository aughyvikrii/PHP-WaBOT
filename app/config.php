<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Simple Configuration
 */

$config = [

    // Bot Identify
    "api_base_url" => 'https://wablastgo.com/api/v1',
    "api_key"  => "",

    // Database config
    "database"  => [
        "type"  => "mysql",
        "host"  => "localhost",
        "user"  => "root",
        "pass"  => "",
        "db"    => "wablastgo"
    ],

    // Debug BOT
    "debug" => false,
];