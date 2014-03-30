<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace PocketMine;

class ChatAPI{

	/** @var Server */
	private $server;

	function __construct(){
		$this->server = Server::getInstance();
	}

	public function init(){
		$this->server->api->console->register("me", "<action ...>", array($this, "commandHandler"));
		$this->server->api->console->register("say", "<message ...>", array($this, "commandHandler"));
		$this->server->api->ban->cmdWhitelist("me");
	}

	/**
	 * @param string $cmd
	 * @param array  $params
	 * @param string $issuer
	 * @param string $alias
	 *
	 * @return string
	 */
	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "say":
				$s = implode(" ", $params);
				if(trim($s) == ""){
					$output .= "Usage: /say <message>\n";
					break;
				}
				$sender = ($issuer instanceof Player) ? "Server" : ucfirst($issuer);
				Player::broadcastMessage("[$sender] " . $s);
				break;
			case "tell":
				if(!isset($params[0]) or !isset($params[1])){
					$output .= "Usage: /$cmd <player> <message>\n";
					break;
				}
				if(!($issuer instanceof Player)){
					$sender = ucfirst($issuer);
				}else{
					$sender = $issuer;
				}
				$n = array_shift($params);
				$target = Player::get($n);
				if(!($target instanceof Player)){
					$target = strtolower($n);
					if($target === "server" or $target === "console" or $target === "rcon"){
						$target = "Console";
					}
				}
				$mes = implode(" ", $params);
				$output .= "[me -> " . ($target instanceof Player ? $target->getDisplayName() : $target) . "] " . $mes . "\n";
				if($target instanceof Player){
					$target->sendMessage("[" . ($sender instanceof Player ? $sender->getDisplayName() : $sender) . " -> me] " . $mes);
				}
				if($target === "Console" or $sender === "Console"){
					console("[INFO] [" . ($sender instanceof Player ? $sender->getDisplayName() : $sender) . " -> " . ($target instanceof Player ? $target->getDisplayName() : $target) . "] " . $mes);
				}
				break;
		}

		return $output;
	}

}