<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Order Controller
 */

  class OrderController extends Main_Controller {
      
    /**
     * Order function
     * @string beli * *
     * @param 1 kode layanan
     * @param 2 no pelanggan
     */

    public function order() {

        $service_code = strtolower($this->segment(2));
        $no_customer  = $this->segment(3);

        if( !$service_code || !$no_customer ) return $this->reply("Terjadi kesalahan, silahkan coba lagi.");

        $service = (object) $this->db->GetOne("SELECT * FROM services WHERE code = '{$service_code}'");

        if( empty($service) )
            return $this->reply("Layanan dengan kode '{$service_code}' tidak ditemukan");
        else if ( $service->status != 'active' )
            return $this->reply("Layanan dengan kode '{$service_code}' sedang tidak aktif");

        if( $this->userdata->balance < $service->price )
            return $this->reply("Saldo anda tidak cukup, silahkan lakukan deposit terlebih dahulu.");

        $flex = [
            'type'  => 'bubble',
            'body'  => [
                'type'      => 'box',
                'layout'    => 'vertical',
                'contents'  => [
                    [
                        'type'      => 'text',
                        'text'      => 'KARTU PEMBELIAN',
                        'weight'    => 'bold',
                        'size'      => 'sm',
                        'color'     => '#5cb85c',
                    ],
                    [
                        'type'      => 'text',
                        'text'      => $service->name,
                        'weight'    => 'bold',
                        'size'      => 'xl'
                    ],
                    [
                        'type'      => 'box',
                        'layout'    => 'vertical',
                        'margin'    => 'lg',
                        'spacing'   => 'sm',
                        'contents'  => [
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  =>  [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Layanan',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $service->name,
                                        'color' => '#666666',
                                        'weight'=> 'bold',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ],
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  =>  [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'No. Pelanggan',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $no_customer,
                                        'color' => '#666666',
                                        'weight'=> 'bold',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ],
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  =>  [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Harga',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => "Rp ".number_format($service->price,0,',','.'),
                                        'color' => '#666666',
                                        'weight'=> 'bold',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            'footer'    => [
                'type'      => 'box',
                'layout'    => 'vertical',
                'spacing'   => 'sm',
                'contents'  => [
                    [
                        'type'      => 'button',
                        'style'     => 'primary',
                        'height'    => 'sm',
                        'action'    => [
                            'type'  => 'postback',
                            'label' => 'Konfirmasi',
                            'data'  => "route=OrderController@confirm_order&params=beli {$service->code} {$no_customer}",
                            'displayText'   => 'Ya'
                        ]
                    ],
                    [
                        'type'  => 'spacer',
                        'size'  => 'sm'
                    ]
                ],
                'flex'  => 0
            ]
        ];

        $card = [
            'type'      => 'flex',
            'altText'   => 'Account',
            'contents'  => $flex
        ];

        $this->reply($card);
    }

    /**
     * Konfirmasi order
     */

    public function confirm_order() {
        $service_code = strtolower($this->segment(2));
        $no_customer  = $this->segment(3);

        if( !$service_code || !$no_customer ) return $this->reply("Terjadi kesalahan, silahkan coba lagi.");

        $service = (object) $this->db->GetOne("SELECT * FROM services WHERE code = '{$service_code}'");

        if( empty($service) )
            return $this->reply("Layanan dengan kode '{$service_code}' tidak ditemukan");
        else if ( $service->status != 'active' )
            return $this->reply("Layanan dengan kode '{$service_code}' sedang tidak aktif");

        if( $this->userdata->balance < $service->price )
            return $this->reply("Saldo anda tidak cukup, silahkan lakukan deposit terlebih dahulu.");

        $order_data = [
            'uid'           => $this->userdata->user_id,
            'sid'           => $service->sid,
            'no_customer'   => $no_customer,
            'price'         => $service->price,
            'service'       => $service->name,
            'status'        => 'pending'
        ];

        if( !$oid = $this->db->insert("orders",$order_data) ){
            return $this->reply("Gagal order pulsa, silahkan coba lagi.");
        }

        $update = $this->db->update("users",[
            'balance'       => "balance-{$service->price}",
            'balance_used'  => "balance_used+{$service->price}",
            'total_order'   => "total_order+1"
        ]," user_id = '{$this->userdata->user_id}' ");

        $this->status_order($oid);
    }

    /**
     * Cek Status
     */

    public function status_order($oid=false){

        if($oid) $first = true;
        else $first = false;

        if( !$oid && $first == false ) $oid = $this->segment(3);

        $order = $this->db->GetOne("SELECT * FROM orders WHERE oid = {$oid} AND uid = '{$this->userdata->user_id}'");

        if( empty($order) ) {
            return $this->reply("Orderan tidak ditemukan");
        }

        $order = (Object) $order;

        if( ($order->status == 'pending' || $order->status == 'process') && $first == false ) {

            if( $order->status == 'pending' ) {
                $status = [
                    'pending','process','cancel','success'
                ];
            } else {
                $status = [
                    'process','cancel','success'
                ];
            }

            $new_status = $status[array_rand($status)];

            $order->status = $new_status;

            $this->db->update("orders",[
                "status"    => $new_status,
            ], " oid = {$oid}");

            if( $new_status == 'cancel' ){

                $this->db->update("users",[
                    "balance"           => "balance+{$order->price}",
                    "balance_used"      => "balance_used-{$order->price}",
                ]," user_id = '{$this->userdata->user_id}' ");

            }
        }

        switch($order->status){
            case 'pending':
                $color_code = $this->color_code(); break;
            
            case 'process':
                $color_code = $this->color_code('primary'); break;
            
            case 'success':
                $color_code = $this->color_code('success'); break;

            case 'cancel':
                $color_code = $this->color_code('danger'); break;

            default: $color_code = $this->color_code();
        }

        $flex = [
            'type'  => 'bubble',
            'body'  => [
                'type'      => 'box',
                'layout'    => 'vertical',
                'contents'  => [
                    [
                        'type'      => 'text',
                        'text'      => 'DETAIL PEMBELIAN',
                        'weight'    => 'bold',
                        'size'      => 'sm',
                        'color'     => $color_code,
                    ],
                    [
                        'type'      => 'text',
                        'text'      => "#{$order->oid}",
                        'weight'    => 'bold',
                        'size'      => 'xl'
                    ],
                    [
                        'type'      => 'box',
                        'layout'    => 'vertical',
                        'margin'    => 'lg',
                        'spacing'   => 'sm',
                        'contents'  => [
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  =>  [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Layanan',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $order->service,
                                        'color' => '#666666',
                                        'weight'=> 'bold',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ],
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  =>  [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'No. Pelanggan',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $order->no_customer,
                                        'color' => '#666666',
                                        'weight'=> 'bold',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ],
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  =>  [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Harga',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => "Rp ".number_format($order->price,0,',','.'),
                                        'color' => '#666666',
                                        'weight'=> 'bold',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ],
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  =>  [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Status',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $order->status,
                                        'color' => $color_code,
                                        'weight'=> 'bold',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            'footer'    => [
                'type'      => 'box',
                'layout'    => 'vertical',
                'spacing'   => 'sm',
                'contents'  => [
                    [
                        'type'      => 'button',
                        'style'     => 'primary',
                        'height'    => 'sm',
                        'action'    => [
                            'type'  => 'postback',
                            'label' => 'Cek Status',
                            'data'  => "route=OrderController@status_order&params=cek order {$order->oid}",
                            'displayText'   => "Cek order {$order->oid}"
                        ]
                    ],
                    [
                        'type'  => 'spacer',
                        'size'  => 'sm'
                    ]
                ],
                'flex'  => 0
            ]
        ];

        if( in_array($order->status,["success",'cancel']) ){
            unset($flex['footer']);
        }

        $card = [
            'type'      => 'flex',
            'altText'   => 'Account',
            'contents'  => $flex
        ];

        $this->reply($card);
    }

    public function status_order_sc(){
        $oid = $this->segment(2);

        return $this->status_order($oid);
    }
  }