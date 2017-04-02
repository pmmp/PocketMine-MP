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

namespace pocketmine\command\defaults\Threads;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class TimingsPasteTask extends AsyncTask{

	protected $name;
	protected $data;
	protected $serverName;
	protected $version;

	protected $messageParams;
	protected $failed = false;

	public function __construct($name, $data, $serverName, $version){
		$this->name = $name;
		$this->data = $data;
		$this->serverName = $serverName;
		$this->version = $version;
	}

	public function onRun(){

		$ch = curl_init("http://paste.ubuntu.com/");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent: " . $this->serverName . " " . $this->version]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		try{
			$data = curl_exec($ch);
			if($data === false){
				$this->failed = true;
			}
		}catch(\Exception $e){
			$this->failed = true;
		}

		curl_close($ch);
		if(preg_match('#^Location: http://paste\\.ubuntu\\.com/([0-9]{1,})/#m', $data, $matches) == 0){
			$this->failed = true;
			return;
		}

		$this->messageParams = $matches[1];
	}

	public function onCompletion(Server $server){
		$server->timingsPasteCallback($this->name, $this->messageParams, $this->failed);
	}
}