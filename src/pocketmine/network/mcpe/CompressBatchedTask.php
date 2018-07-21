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

namespace pocketmine\network\mcpe;

use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CompressBatchedTask extends AsyncTask{

	private $level;
	private $data;

	/**
	 * @param PacketStream $stream
	 * @param string[]     $targets
	 * @param int          $compressionLevel
	 */
	public function __construct(PacketStream $stream, array $targets, int $compressionLevel){
		$this->data = $stream->buffer;
		$this->level = $compressionLevel;
		$this->storeLocal($targets);
	}

	public function onRun() : void{
		$this->setResult(NetworkCompression::compress($this->data, $this->level), false);
	}

	public function onCompletion(Server $server) : void{
		/** @var Player[] $targets */
		$targets = $this->fetchLocal();

		$server->broadcastPacketsCallback($this->getResult(), $targets);
	}
}
