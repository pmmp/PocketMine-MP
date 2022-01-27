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

use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\Server;
use function spl_object_id;

final class StandardPacketBroadcaster implements PacketBroadcaster{

	/** @var Server */
	private $server;

	public function __construct(Server $server){
		$this->server = $server;
	}

	public function broadcastPackets(array $recipients, array $packets) : void{
		$buffers = [];
		$compressors = [];
		$targetMap = [];
		foreach($recipients as $recipient){
			$serializerContext = $recipient->getPacketSerializerContext();
			$bufferId = spl_object_id($serializerContext);
			if(!isset($buffers[$bufferId])){
				$buffers[$bufferId] = PacketBatch::fromPackets($serializerContext, ...$packets);
			}

			//TODO: different compressors might be compatible, it might not be necessary to split them up by object
			$compressor = $recipient->getCompressor();
			$compressors[spl_object_id($compressor)] = $compressor;

			$targetMap[$bufferId][spl_object_id($compressor)][] = $recipient;
		}

		foreach($targetMap as $bufferId => $compressorMap){
			$buffer = $buffers[$bufferId];
			foreach($compressorMap as $compressorId => $compressorTargets){
				$compressor = $compressors[$compressorId];
				if(!$compressor->willCompress($buffer->getBuffer())){
					foreach($compressorTargets as $target){
						foreach($packets as $pk){
							$target->addToSendBuffer($pk);
						}
					}
				}else{
					$promise = $this->server->prepareBatch($buffer, $compressor);
					foreach($compressorTargets as $target){
						$target->queueCompressed($promise);
					}
				}
			}
		}
	}
}
