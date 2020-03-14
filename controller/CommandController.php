<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * CommandController
 */

class CommandController extends Main_Controller {

    public function help(){
        $view = $this->view("command/help");

        return $this->reply($view);
    }

    public function follow(){
        $message = "Terimakasih telah menambahkan BOT ini.\n\nSilahkan ketik 'about' atau 'tentang' untung informasi lebih lanjut";

        return $this->reply($message);
    }

    public function cancel_action(){
        $user_id    = $this->userdata->user_id;
        $log        = get_log($user_id);

        clean_log($user_id);

        if( @$log['action'] == '' ) $this->reply("Tidak ada aksi sebelumnya");
        else $this->reply("Aksi telah dibatalkan");
    }

    public function account() {
        
        $card = [
            'type'      => 'bubble',
            'hero'      => [
                'type'          => 'image',
                'url'           => $this->user("picture_url","https://vignette.wikia.nocookie.net/line/images/1/10/2015-cony.png/revision/latest/top-crop/width/360/height/450?cb=20150806042102"),
                'size'          => 'full',
                'aspectRatio'   => '20:13',
                'aspectMode'    => 'cover'
            ],
            'body'      => [
                'type'      => 'box',
                'layout'    => 'vertical',
                'contents'  => [
                    [
                        'type'  => 'text',
                        'text'  => $this->user('display_name','user'),
                        'weight'=> 'bold',
                        'size'  => 'xl'
                    ],
                    [
                        'type'      => 'box',
                        'layout'    => 'vertical',
                        'margin'    => 'lg',
                        'spacing'   => 'sm',
                        'contents'  => [
                            // Saldo
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  => [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Saldo',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 3
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Rp '.number_format($this->user('balance',0),0,',','.'),
                                        'wrap'  => true,
                                        'color' => '#666666',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ],
                            // Saldo Terpakai
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  => [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Saldo Terpakai',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 3
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Rp '.number_format($this->user('balance_used',0),0,',','.'),
                                        'wrap'  => true,
                                        'color' => '#666666',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ],
                            // Total Order
                            [
                                'type'      => 'box',
                                'layout'    => 'baseline',
                                'spacing'   => 'sm',
                                'contents'  => [
                                    [
                                        'type'  => 'text',
                                        'text'  => 'Total Order',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 3
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => number_format($this->user('total_order',0),0,',','.'),
                                        'wrap'  => true,
                                        'color' => '#666666',
                                        'size'  => 'sm',
                                        'flex'  => 5
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'footer'    => [
                'type'      => 'box',
                'layout'    => 'vertical',
                'spacing'   => 'sm',
                'contents'  => [
                    // Button Deposit
                    [
                        'type'      => 'button',
                        'style'     => 'primary',
                        'action'   => [
                            'type'          => 'postback',
                            'label'         => 'Deposit',
                            'data'          => 'route=DepositController@deposit_method',
                            'displayText'   => 'Deposit',
                        ]
                    ],
                    // Riwayat Order
                    [
                        'type'      => 'button',
                        'style'     => 'primary',
                        'color'     => '#f0ad4e',
                        'action'   => [
                            'type'          => 'postback',
                            'label'         => 'Riwayat Order',
                            'data'          => 'route=CommandController@riwayat_order&params=riwayat',
                            'displayText'   => 'Riwayat',
                        ]
                    ],
                ]
            ]
        ];

        $account = [
            'type'      => 'flex',
            'altText'   => 'Account',
            'contents'  => $card
        ];

        $this->reply($account);
    }

    public function riwayat_order() {
        $user_id = $this->user('user_id',0);
        $page    = $this->segment(2)-1 > 0 ? $this->segment(2)-1 : 0;

        $history = $this->db->query("SELECT * FROM orders WHERE uid = ? ORDER BY oid DESC LIMIT 20 OFFSET {$page}",[ $user_id ]);

        if( @$history->num_rows == 0 ) {
            return $this->reply("Tidak ada riwayat order");
        }

        $list = "Menampilkan 10 riwayat order terakhir:\n\n";
        $no=1;
        while($data = $history->fetch_object()){
            $list .= "{$data->oid} | {$data->no_customer} | {$data->status}\n";
        }

        if( $this->user('total_order',0) > 20 ) {
            $list .= "\nUntuk halaman selanjutnya ketik 'riwayat 2'";
        }

        $this->reply($list);
    }

    public function kategori() {

        $category = $this->db->query("SELECT * FROM category WHERE status = 'active'");

        if( @$category->num_rows == 0 ){
            return $this->reply("Tidak ada kategori tersedia");
        }

        $list = "Kategori layanan:\n\n";
        while($data = $category->fetch_object()){
            $list .= "{$data->kode} = ";
            $list .= "{$data->nama}\n";
        }

        $list .= "\nKetikan 'harga kode' untuk melihat layanan\nContoh 'harga tsel'";

        $this->reply($list);
    }

    public function harga() {
        $kode = $this->segment(2);

        $category = $this->db->GetOne("SELECT * FROM category WHERE kode = ?",[$kode]);

        if( empty($category) ) return $this->reply("Kategori tidak '{$kode}' ditemukan");

        $category = (object) $category;

        if( $category->status != 'active' ) return $this->reply("Kategori '{$kode}' tidak aktif");
        
        $service = $this->db->query("SELECT * FROM services WHERE cid = ?",[$category->cid]);

        if( @$service->num_rows == 0 ) return $this->reply("Tidak ada layanan untuk kategori '{$kode}'");

        $list = "Daftar layanan:\n\n";
        while($data = $service->fetch_object()){
            $list .= "{$data->code} = {$data->name} | Rp ".number_format($data->price,0,',','.')."\n";
        }

        $list .= "\nKetikan 'beli kode nomor_pelanggan' untuk melakukan pembelian\nContoh 'beli xl1 081234567890'";

        $this->reply($list);
    }

    public function admin(){
        $view = $this->view("command/admin");

        return $this->reply($view);
    }

    public function about(){
        $view = $this->view("command/about");

        return $this->reply($view);
    }

    public function saran() {

        $user_id    = $this->user("user_id");
        $today      = date("Y-m-d");
        $cek_today  = $this->db->GetOne("SELECT * FROM feedback WHERE uid = ? AND created_at like '{$today}%'",[$user_id]);

        if( !empty($cek_today) ) return $this->reply("Anda sudah memberi masukan hari ini, silahkan coba lagi esok hari.");

        put_log($user_id,[
            'action'    => 'CommandController@saran_create'
        ]);

        return $this->reply("Silahkan masukan saran anda");
    }

    public function saran_create(){
        $user_id    = $this->user("user_id");
        $today      = date("Y-m-d");
        $cek_today  = $this->db->GetOne("SELECT * FROM feedback WHERE uid = ? AND created_at like '{$today}%'",[$user_id]);
        $saran      = $this->data['events'][0]['message']['text'];

        if( !empty($cek_today) ) {
            clean_log($user_id);
            return $this->reply("Anda sudah memberi masukan hari ini, silahkan coba lagi esok hari.");
        }

        $insert = [
            'uid'       => $user_id,
            'message'   => $saran
        ];

        if( !$fid = $this->db->insert("feedback",$insert) ){
            return $this->reply("Gagal mengirim saran, silahkan coba lagi.");
        }

        clean_log($user_id);

        $this->reply("Saran anda sudah diterima\nID Saran anda: #{$fid}\n\nterimakasih atas masukannya.");
        $this->sendMessage($this->admin_id(),"Ada saran dari {$this->userdata->display_name}\nID: #{$fid}\n\n$saran");
    }

    public function balas_saran(){

        $user_id    = $this->user("user_id");
        $fid        = $this->segment(3);
        $text       = $this->get_text();

        if( $user_id != $this->admin_id() ) return $this->reply("Maksudnya '{$text}' apa ya ?");

        $feedback = $this->db->GetOne("SELECT * FROM feedback WHERE fid = ?",[$fid]);

        if( empty($feedback) ) return $this->reply("Saran dengan id #{$fid} tidak ditemukan");
        $feedback = (Object) $feedback;

        $sender = $this->db->getOne("SELECT * FROM users WHERE user_id = '{$feedback->uid}'");
        $sender = (Object) $sender;

        $color_code = $this->color_code("success");

        $flex = [
            'type'  => 'bubble',
            'body'  => [
                'type'      => 'box',
                'layout'    => 'vertical',
                'contents'  => [
                    [
                        'type'      => 'text',
                        'text'      => 'KARTU SARAN',
                        'weight'    => 'bold',
                        'size'      => 'sm',
                        'color'     => $color_code,
                    ],
                    [
                        'type'      => 'text',
                        'text'      => "#$feedback->fid",
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
                                        'text'  => 'Tanggal',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $feedback->created_at,
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
                                        'text'  => 'Pengirim',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $sender->display_name,
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
                                        'text'  => 'Pesan',
                                        'color' => '#aaaaaa',
                                        'size'  => 'sm',
                                        'flex'  => 2
                                    ],
                                    [
                                        'type'  => 'text',
                                        'text'  => $feedback->message,
                                        'wrap'  => true,
                                        'size'  => 'sm',
                                        'flex'  => 5
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
                            'label' => 'Balas Saran',
                            'data'  => 'route=CommandController@balas_saran_conf&params='.$fid,
                            'displayText'   => 'Balas Saran '.$fid
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
            'altText'   => 'Balas saran',
            'contents'  => $flex
        ];

        return $this->reply($card);
    }

    public function balas_saran_conf() {
        $user_id    = $this->user("user_id");
        $fid        = $this->segment(1);

        if( $user_id != $this->admin_id() ) return $this->reply("Maksudnya '{$text}' apa ya ?");

        $feedback = $this->db->GetOne("SELECT * FROM feedback WHERE fid = ?",[$fid]);

        if( empty($feedback) ) return $this->reply("Saran dengan id #{$fid} tidak ditemukan");
        

        put_log($user_id,[
            'action'    => 'CommandController@balas_saran_act',
            'data'      => [
                'fid'   => $fid
            ]
        ]);

        $this->reply("Silahkan masukan balasan");
    }

    public function balas_saran_act(){
        $user_id    = $this->user("user_id");

        if( $user_id != $this->admin_id() ) return $this->reply("Maksudnya '{$text}' apa ya ?");
        
        $log = get_log($user_id);
        $fid = $log['data']['fid'];

        $feedback = $this->db->GetOne("SELECT * FROM feedback WHERE fid = ?",[$fid]);

        if( empty($feedback) ) return $this->reply("Saran dengan id #{$fid} tidak ditemukan");
        $sender = $this->db->getOne("SELECT * FROM users WHERE user_id = ?",[ $feedback['uid'] ]);
        if( empty($sender) ) return $this->reply("Pengirim saran dengan id #{$fid} tidak ditemukan di database");

        $feedback   = (Object) $feedback;
        $sender     = (Object) $sender;

        $balasan = $this->data['events'][0]['message']['text'];

        $message = "Balasan untuk saran #{$fid}:\n\n{$balasan}";

        $this->reply("Pesan terkirim");
        clean_log($user_id);
        $this->sendMessage($sender->user_id,$message);
    }
}