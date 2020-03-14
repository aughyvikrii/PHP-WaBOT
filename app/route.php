<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

$route['user'] = [
    // Route By Feature Line
    "image"     => "Controller@photo",
    "location"  => "Controller@animation",
    "audio"     => "Controller@voice",
    "sticker"   => "Controller@sticker",
    "follow"    => "CommandController@follow",

    /**
     * Text Route
     * Delimiter use ' ' (space)
     * ex: "price *"    => "Controller@price"
     * so when text is "price book", it will use "Controller@price"
     */
    "text"      => [
        'help'          => 'CommandController@help',
        'bantuan'       => 'CommandController@help',
        'harga'         => 'CommandController@kategori',
        'harga *'       => 'CommandController@harga',
        'akun'          => 'CommandController@account',
        'deposit'       => 'DepositController@deposit_method',
        'cek deposit *' => 'DepositController@status_deposit',
        'deposit *'     => 'DepositController@status_deposit_sc',
        'cancel deposit *' => 'DepositController@cancel_deposit',
        'riwayat'       => 'CommandController@riwayat_order',
        'riwayat deposit'=> 'DepositController@riwayat_deposit',
        'beli * *'      => 'OrderController@order',
        'cek order *'   => 'OrderController@status_order',
        'status *'      => 'OrderController@status_order_sc',
        'cancel'        => 'CommandController@cancel_action',
        'batal'         => 'CommandController@cancel_action',
        'admin'         => 'CommandController@admin',
        'tentang'       => 'CommandController@about',
        'about'         => 'CommandController@about',
        'saran'         => "CommandController@saran",
        'balas saran *' => "CommandController@balas_saran",
        "*"             => "Main_Controller@text",
    ]
];

$route['group'] = [

    'join'      => 'GroupController@greeting_message',
    
    'text'      => [
        'halo bot'      => 'GroupController@sapa',
        'tentang bot'   => 'GroupController@about',
        'perintah bot'  => 'GroupController@command',
        'bot keluar'    => 'GroupController@leave',
    ]

];

$route['room'] = [

    'join'      => 'GroupController@greeting_message',
    
    'text'      => [
        'halo bot'      => 'GroupController@sapa',
        'tentang bot'   => 'GroupController@about',
        'perintah bot'  => 'GroupController@command',
        'bot keluar'    => 'GroupController@leave_room',
    ]

];