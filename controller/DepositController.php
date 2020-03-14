<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Deposit Controller
 */

 class DepositController extends Main_Controller {

    public function deposit_method() {

        $thumb = [
            'emoney'  => 'https://lh3.googleusercontent.com/proxy/Ys5QSzisYv57E_x-GX0hHDFaFtIb46I7bExCbVbuco6_k7E4JaVVOOsk2tsKwnsDWJXcn--eaLOHMvXq0LZIhLg9B6omZJdr_kI',
            'bank'    => 'https://3.bp.blogspot.com/-RbgVrA-lEuo/WIvsnVSjRxI/AAAAAAAABQo/crDV5S4WI5QMOrO4bGBV9p2HIc7HQLzDwCLcB/w700/CARA%2BMENDAFTAR%2B%2BDAN%2BMEMBUKA%2BREKENING%2BBANK%2BBNI%252C%2BBCA%252C%2BBRI%2BON%2BINDO%2BBLOG.jpg',
            'pulsa' => 'https://3.bp.blogspot.com/-bEp9qwtVW2Y/WB8-5bGMF1I/AAAAAAAAN88/YHVTPOSMPz8Sil5IumltptQCSOBP8CJ1ACK4B/s640/Telkomsel%252C%2BIndosat%252C%2BXL%252C%2BAxis%2BSmart.png'
        ];

        $get_method = $this->db->query("SELECT * FROM deposit_method WHERE status = 'active'");

        if( $get_method->num_rows == 0 ){
            return $this->reply("Metode deposit sedang tidak tersedia! silahkan coba beberapa saat lagi.");
        }

        // Create Carousel
        $group = [];
        while($method = $get_method->fetch_object()){
            
            $type = $method->type;

            if( !isset($group[ $type ]) ) {
                $group[$method->type ] = [
                    'thumbnailImageUrl' => $thumb[$type],
                    'imageBackgroundColor'  => '#ffffff',
                    'title'                 => 'Deposit '.$type,
                    'text'                  => 'Deposit dengan '.$type,
                    'actions'               => [
                        [
                            'type'  => 'postback',
                            'label' => $method->name,
                            'data'  => 'route=DepositController@act_deposit&params='.$method->dmid,
                            'displayText'   => 'Deposit '.$method->name
                        ]
                    ]
                ];
            } else {
                $group[$type]['actions'][] = [
                    'type'  => 'postback',
                    'label' => $method->name,
                    'data'  => 'route=DepositController@act_deposit&params='.$method->dmid,
                    'displayText'   => 'Deposit '.$method->name
                ];
            }

        }

        $columns = [];

        foreach($group as $data){
            $columns[] = $data;
        }

        $carousel = [
            'type'      => 'template',
            'altText'   => 'deposit method',
            'template'  => [
                'type'      => 'carousel',
                'columns'   => $columns
            ]
        ];

        return $this->reply($carousel);
    }

    public function act_deposit() {
        $dmid = $this->segment(1);

        $get_method = $this->db->query("SELECT * FROM deposit_method WHERE dmid = ?",[$dmid]);

        if( $get_method->num_rows == 0 ) {
            return $this->reply("Metode deposit sedang tidak aktif!");
        }

        $method = $get_method->fetch_object();

        if( $method->status != 'active' ) {
            return $this->reply("Metode deposit sedang tidak aktif!");
        }

        $response = "Masukan nominal deposit\nMinimal Rp ".number_format($method->minimum,0,',','.')." \nMaksimal Rp ".number_format($method->maximum,0,',','.')."\nContoh input 1.000.000\n\nKetik 'batal' untuk membatalkan aksi";

        put_log($this->userdata->user_id,[
            'action'    => 'DepositController@input_amount_deposit',
            'data'      => [
                'dmid'  => $dmid
            ]
        ]);

        $this->reply($response);
    }

    public function input_amount_deposit(){

        $user_id= $this->get_id();
        $log    = get_log($user_id);
        $dmid   = $log['data']['dmid'];
        $input_amount   = $this->get_text();

        if( !$dmid ){
            clean_log($user_id);
            return $this->response("Terjadi kesalahan, ulangi dari awal!");
        }

        $get_method = $this->db->query("SELECT * FROM deposit_method WHERE dmid = ?",[$dmid]);

        if( $get_method->num_rows == 0 ){
            clean_log($user_id);
            return $this->reply("Terjadi kesalahan, ulangi dari awal!");
        }

        $method = $get_method->fetch_object();

        if( $method->status != 'active' ){
            clean_log($user_id);
            return $this->reply("Metode deposit sedang tidak aktif, silahkan pilih metode lain!");
        }

        $input_amount = preg_replace('/[^0-9]/','',$input_amount);

        if( $input_amount < $method->minimum ) {
            return $this->reply("Minimal deposit {$method->name} adalah Rp ".number_format($method->minimum,0,',','.'));
        } else if ( $input_amount > $method->maximum ) {
            return $this->reply("Maksimal deposit {$method->name} adalah Rp ".number_format($method->maximum,0,',','.'));
        }

        $code       = sprintf("%03d",rand(000,999));
        $total_pay  = $input_amount + $code;
        $get_amount = $total_pay;

        $data = [
            'dmid'          => $dmid,
            'uid'           => $user_id,
            'input_amount'  => $input_amount,
            'code'          => $code,
            'total_pay'     => $total_pay,
            'get_amount'    => $get_amount,
            'method_name'   => $method->name,
            'pay_target'    => $method->pay_target,
            'status'        => 'waiting'
        ];

        if( !$did = $this->db->insert("deposits",$data) ){
            return $this->reply("Gagal melakukan deposit! silahkan coba lagi.");
        }

        $this->status_deposit($did);
    }

    public function status_deposit($did=false){

        if( $did ) $first = true;
        else $first = false;

        if( !$did ) $did = $this->segment(3);

        if( !$did ) return $this->reply("Deposit tidak ditemukan");

        $deposit = $this->db->query("SELECT * FROM deposits WHERE did = ? AND uid = ?",[$did,$this->user('user_id')]);

        if( $deposit->num_rows == 0 ) return $this->reply("Deposit tidak ditemukan");

        $deposit = $deposit->fetch_object();

        if( $deposit->status == 'waiting' && $first == false ){

            $get_amount = $deposit->get_amount;

            $status = [
                'success'
            ];
    
            $new_status = $status[array_rand($status)];
    
            $update = $this->db->update("deposits",['status' => $new_status],"  did = {$did} ");
    
            if( $new_status == 'success' ){
                $this->db->update("users",[
                    "balance"   => "balance+{$get_amount}"
                ]," user_id = '".$this->user('user_id')."'");
            }

            $deposit->status = $new_status;
        }

        switch($deposit->status){
            case 'waiting':
                $color_code = $this->color_code(); break;
            
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
                        'text'      => 'KARTU DEPOSIT',
                        'weight'    => 'bold',
                        'size'      => 'sm',
                        'color'     => $color_code,
                    ],
                    [
                        'type'      => 'text',
                        'text'      => "#$deposit->did",
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
                                        'text'  => 'Metode',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $deposit->method_name,
                                        'color' => '#aaaaaa',
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
                                        'text'  => 'Tujuan',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $deposit->pay_target,
                                        'color' => '#aaaaaa',
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
                                        'text'  => 'Saldo',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Rp '.number_format($deposit->get_amount,0,',','.'),
                                        'color' => '#aaaaaa',
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
                                        'text'  => 'Bayar',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Rp '.number_format($deposit->total_pay,0,',','.'),
                                        'color' => '#aaaaaa',
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
                                        'text'  => $deposit->status,
                                        'color' => $color_code,
                                        'size'  => 'sm',
                                        'flex'  => 5,
                                        'weight' => 'bold'
                                    ]
                                ]
                            ],
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
                            'data'  => 'route=DepositController@status_deposit&params=cek deposit '.$deposit->did,
                            'displayText'   => 'Cek Deposit '.$deposit->did
                        ]
                    ],
                    [
                        'type'      => 'button',
                        'style'     => 'primary',
                        'color'     => '#d9534f',
                        'height'    => 'sm',
                        'action'    => [
                            'type'  => 'postback',
                            'label' => 'Batalkan',
                            'data'  => 'route=DepositController@cancel_deposit&params=cancel deposit '.$deposit->did,
                            'displayText'   => 'Cancel Deposit '.$deposit->did
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

        if( $deposit->status != 'waiting' ) {
            unset($flex['footer']);
        } else {
            clean_log($this->userdata->user_id);
        }

        $card = [
            'type'      => 'flex',
            'altText'   => 'Account',
            'contents'  => $flex
        ];

        $this->reply($card);
    }

    public function status_deposit_sc(){
        $did = $this->segment(2);

        return $this->status_deposit($did);
    }

    public function cancel_deposit(){
        $did = $this->segment(3);

        if( !$did ) return $this->reply("Deposit tidak ditemukan");

        $deposit = $this->db->GetOne("SELECT * FROM deposits WHERE did = ? AND uid = ?",[$did,$this->user('user_id')]);

        if( !$deposit ) return $this->reply("Deposit tidak ditemukan");

        if( $deposit->status != 'waiting' ) return $this->reply("Deposit tidak dapat dibatalkan");

        $this->db->update("deposits",[
            "status"    => 'cancel'
        ], " did = {$did} ");

        $this->reply("Deposit dibatalkan.");
    }

    public function riwayat_deposit() {
        $user_id = $this->user('user_id',0);
        $page    = $this->segment(2)-1 > 0 ? $this->segment(2)-1 : 0;

        $history = $this->db->query("SELECT * FROM deposits WHERE uid = ? ORDER BY did DESC LIMIT 20 OFFSET ?",[ $user_id, "{$page}" ]);

        if( @$history->num_rows == 0 ) {
            return $this->reply("Tidak ada riwayat deposit");
        }

        $list = "Menampilkan 10 riwayat deposit terakhir:\n\n";
        $no=1;
        while($data = $history->fetch_object()){
            $list .= "{$data->did} | {$data->method_name} | {$data->status}\n";
        }

        if( $this->user('total_order',0) > 20 ) {
            $list .= "\nUntuk halaman selanjutnya ketik 'riwayat 2'";
        }

        $this->reply($list);
    }
 }