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

use PocketMine\Network\Protocol\Info;
use PocketMine\Plugin\PluginManager;

class PluginAPI extends \stdClass{
	private $server;

	public function __construct(){
		$this->server = Server::getInstance();
		$this->server->api->console->register("plugins", "", array($this, "commandHandler"));
		$this->server->api->console->register("version", "", array($this, "commandHandler"));
		$this->server->api->ban->cmdWhitelist("version");
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "plugins":
				$output = "Plugins: ";
				foreach($this->server->getPluginManager()->getPlugins() as $plugin){
					$d = $plugin->getDescription();
					$output .= $d->getName() . ": " . $d->getVersion() . ", ";
				}
				$output = $output === "Plugins: " ? "No plugins installed.\n" : substr($output, 0, -2) . "\n";
				break;
		}

		return $output;
	}

	public function __destruct(){
	}

	public function init(){

	}
}