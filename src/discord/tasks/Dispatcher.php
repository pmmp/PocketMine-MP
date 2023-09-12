<?php

namespace pocketmine\discord\tasks;

use pocketmine\discord\Webhook;
use pocketmine\scheduler\AsyncTask;

class Dispatcher extends AsyncTask{
    private $webhook;

    public function __construct(Webhook $webhook) {
        $this->webhook = serialize($webhook);
    }

    public function onRun(): void
    {
        $data = unserialize($this->webhook);
        $ch = curl_init($data->webhook_url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data->getData()));
        $response = curl_exec($ch);
        // $res = curl_getinfo($ch, CURLINFO_RESPONSE_CODE); //use if something bugs out, returns http code
        // var_dump($res);
        $this->setResult($response);
        
    }

}

?>