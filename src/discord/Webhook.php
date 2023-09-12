<?php

namespace pocketmine\discord;

use pocketmine\discord\tasks\Dispatcher;
use pocketmine\Server;

class Webhook {
    public $webhook_url;
    public $content = '';
    public $username = 'OwnagePE Pocketmine';
    public $avatar_url = "https://img.freepik.com/free-photo/bare-tree-snowy-area-clear-sky_181624-4788.jpg?size=626&ext=jpg";
    public $embeds = [];
    public $tts = false;
    public $allow_mentions = ['roles' => false, 'users' => false, 'everyone' => false];

    public function __construct($url) {
        $this->webhook_url = $url;
    }

    public function setContent(String $message): void {
        $this->content = $message;
    }

    public function setUsername(String $username): void {
        $this->username = $username;
    }

    public function setAvatar(String $url): void {
        $this->avatar_url = $url;
    }

    public function setTts(Bool $tts): void {
        $this->tts = $tts;
    }

    public function setAllowMentions(Bool $allow): void {
        $this->allow_mentions = $allow;
    }

    public function addEmbed(Embed $embed): void {
        $this->embeds[] = $embed->getEmbed();
    }

    public function getData(): array {
        $webhook = [
            'content' => $this->content,
            'username' => $this->username,
            'avatar_url' => $this->avatar_url,
            'tts' => $this->tts,
            'embeds' => $this->embeds
        ];

        return $webhook;
    }

    public function send(): void {
        Server::getInstance()->getAsyncPool()->submitTask(new Dispatcher($this));
    }
}