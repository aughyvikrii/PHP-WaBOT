<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Personal Chat
 */
$route['user'] = [
    
    /**
     * Chat from user
     */
    "message"      => [
        'help'  => 'UserController@help',
        '*'     => 'UserController@not_understand'
    ],

    /**
     * If User send image
     */
    "image"     => "UserController@media",
    
    /**
     * If User send video
     */
    "video"     => "UserController@media",
    
    
    /**
     * If user send location
     */
    "location"  => "UserController@media",

    /**
     * If user send live_location
     */
    "live_location"  => "UserController@media",

    /**
     * if user send audio
     */
    "audio"     => "UserController@media",

    /**
     * if user send chat
     */
    "sticker"   => "UserController@media",

    /**
     * if user send file
     */

     "document"     => "UserController@media",

    /**
     * if user send contact
     */

     "contact"     => "UserController@media",
];


/**
 * Group Chat
 */
$route['group'] = [
    /**
     * Chat from user
     */
    "message"      => [
        'halo'      => 'GroupController@halo',
    ],

];