<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * UserController
 * sample controller
 */

class UserController extends Controller {

    /**
     * help function
     * information about command
     */

    public function help() {
        $view = $this->view('help');

        reply($view);
    }

    public function not_understand() {
        reply('Perintah tidak dikenali');
    }

    public function media() {
        $message_id = get_var('message_id');
        $media_path = BASE_PATH."/media/{$message_id}.json";
        file_put_contents($media_path,json_encode($this->data));

        echo "file disimpan $media_path";
    }

    //Send Image
    // public function sendImage() {
    //     $media = "image.jpg";

    //     $image = file_get_contents($media);
    //     $image_b64 = base64_encode($image);

    //     main_curl("send-image",[
    //         'api_key' => API_KEY,
    //         'device_key' => get_var('device'),
    //         'destination' => '081200000001',
    //         'image' =>  $image_b64,
    //         'filename' => $media,
    //         'caption'  => 'Kirim gambar'
    //     ]);
    // }
}