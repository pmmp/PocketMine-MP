<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

class ChatAPI{
	private $server;
	function __construct(PocketMinecraftServer $server){
		$this->server = $server;
	}
	
	public function init(){
		
	}
	
	public function broadcast($message){
		$this->send(false, $message);
	}
	
	public function send($owner, $text, $whitelist = false, $blacklist = false){
		$message = "";
		if($owner !== false){
			$message = "<".$owner."> ";
		}
		$message .= $text;
		console("[CHAT] ".$message);
		$this->server->handle("server.chat", new Container($message, $whitelist, $blacklist));
	}
}