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
use pocketmine\event\TranslationContainer;
use pocketmine\Server;

class TimingsPasteTask extends AsyncTask{

	protected $sender;
	protected $data;
	protected $serverName;
	protected $version;

	protected $failed = false;

	public function __construct($sender, $data, $serverName, $version){
		parent::__construct($sender);
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
			$this->failed = ($data = curl_exec($ch)) === false;
		}catch(\Exception $e){
			$this->failed = true;
		}

		curl_close($ch);
		if(preg_match('#^Location: http://paste\\.ubuntu\\.com/([0-9]{1,})/#m', $data, $matches) == 0){
			$this->failed = true;
			return;
		}

		$this->setResult($matches[1]);
	}

	public function onCompletion(Server $server){
		$sender = $this->fetchLocal($server);
		if($this->failed){
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.pasteError"));
			return;
		}
		$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsUpload", ["http://paste.ubuntu.com/" . $this->getResult() . "/"]));
		$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsRead", ["http://" . $server->getProperty("timings.host", "timings.pmmp.io") . "/?url=" . $this->getResult()]));
	}
}