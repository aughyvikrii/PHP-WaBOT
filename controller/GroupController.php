<?php

/**
 * GroupController
 */

 class GroupController extends Main_Controller {

    public function greeting_message() {
        $message = "Terimakasih telah menambahkan BOT ini.\n\nSilahkan ketik 'tentang bot' untung informasi lebih lanjut";   

        return $this->reply($message);
    }

    public function sapa() {

        $user_id = $this->data['events'][0]['source']['userId'];

        $account_data = $this->getAccountData($user_id);

        $this->reply("Hallo ".$account_data['displayName']);
    }

    public function about(){

        $view = $this->view("group/about");

        return $this->reply($view);
    }

    public function command(){

        $view = $this->view("group/command");

        return $this->reply($view);
    }

    public function leave(){

        $group_id = $this->data['events'][0]['source']['groupId'];

        $this->reply("Goodbye.");

        leaveGroup($group_id);

    }

    public function leave_room(){

        $room_id = $this->data['events'][0]['source']['roomId'];

        $this->reply("Goodbye.");

        leaveRoom($room_id);

    }
 }