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

declare(strict_types=1);

namespace pocketmine\network;

use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CompressBatchedTask extends AsyncTask{

	public $level = 7;
	public $data;

	/**
	 * @param BatchPacket $batch
	 * @param string[]    $targets
	 */
	public function __construct(BatchPacket $batch, array $targets){
		$this->data = $batch->payload;
		$this->level = $batch->getCompressionLevel();
		$this->storeLocal($targets);
	}

	public function onRun(){
		$batch = new BatchPacket();
		$batch->payload = $this->data;
		$this->data = null;

		$batch->setCompressionLevel($this->level);
		$batch->encode();

		$this->setResult($batch->buffer);
	}

	public function onCompletion(Server $server){
		$pk = new BatchPacket($this->getResult());
		$pk->isEncoded = true;

		/** @var Player[] $targets */
		$targets = $this->fetchLocal();

		$server->broadcastPacketsCallback($pk, $targets);
	}
}
