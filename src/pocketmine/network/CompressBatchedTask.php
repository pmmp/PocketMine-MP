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

namespace pocketmine\network;

use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CompressBatchedTask extends AsyncTask{

	public $level = 7;
	public $data;
	public $final;
	public $targets;
	public $immediate = false;

	public function __construct(BatchPacket $data, array $targets, $level = 7, bool $sendImmediate = false){
		$this->data = serialize($data);
		$this->targets = $targets;
		$this->level = $level;
		$this->immediate = $sendImmediate;
	}

	public function onRun(){
		try{
			/** @var BatchPacket $pk */
			$pk = unserialize($this->data);
			$pk->compress($this->level);
			$this->final = serialize($pk);
			$this->data = null;
		}catch(\Throwable $e){

		}
	}

	public function onCompletion(Server $server){
		$server->broadcastPacketsCallback(unserialize($this->final), (array) $this->targets, $this->immediate);
	}
}
