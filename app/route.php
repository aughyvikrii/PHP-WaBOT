<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Personal Chat
 */
$route['user'] = [

    /**
     * First time someone add BOT
     */
    "follow"    => "UserController@follow",
    "unfollow"  => "UserController@unfollow",

    /**
     * Chat from user
     */
    "text"      => [
        'halo'      => 'UserController@halo',
        'say *'     => 'UserController@say_what',
        '*'         => 'UserController@not_understand'
    ],

    /**
     * If User send image
     */
    "image"     => "UserController@image",
    
    /**
     * If user send location
     */
    "location"  => "UserController@location",

    /**
     * if user send audio
     */
    "audio"     => "UserController@audio",

    /**
     * if user send chat
     */
    "sticker"   => "UserController@sticker",

    /**
     * if user send file
     */

     "file"     => "UserController@file",
];


/**
 * Group Chat
 */
$route['group'] = [

    // First time join group
    'join'      => 'GroupController@join',
    
    /**
     * Chat from user
     */
    "text"      => [
        'halo'      => 'GroupController@halo',
        'say *'     => 'GroupController@say_what',
        'leave'     => 'GroupController@leave',
    ],

];


/**
 * Room chat
 */
$route['room'] = [

    // First time join group
    'join'      => 'GroupController@join',
    
    /**
     * Chat from user
     */
    "text"      => [
        'halo'      => 'RoomController@halo',
        'say *'     => 'RoomController@say_what',
        'leave'     => 'RoomController@leave',
    ],

];