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

use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\Server;
use function spl_object_id;

final class StandardPacketBroadcaster implements PacketBroadcaster{

	/** @var Server */
	private $server;

	public function __construct(Server $server){
		$this->server = $server;
	}

	public function broadcastPackets(array $recipients, array $packets) : void{
		//TODO: we should be using session-specific serializer contexts for this
		$stream = PacketBatch::fromPackets(new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()), ...$packets);

		/** @var Compressor[] $compressors */
		$compressors = [];
		/** @var NetworkSession[][] $compressorTargets */
		$compressorTargets = [];
		foreach($recipients as $recipient){
			$compressor = $recipient->getCompressor();
			$compressorId = spl_object_id($compressor);
			//TODO: different compressors might be compatible, it might not be necessary to split them up by object
			$compressors[$compressorId] = $compressor;
			$compressorTargets[$compressorId][] = $recipient;
		}

		foreach($compressors as $compressorId => $compressor){
			if(!$compressor->willCompress($stream->getBuffer())){
				foreach($compressorTargets[$compressorId] as $target){
					foreach($packets as $pk){
						$target->addToSendBuffer($pk);
					}
				}
			}else{
				$promise = $this->server->prepareBatch($stream, $compressor);
				foreach($compressorTargets[$compressorId] as $target){
					$target->queueCompressed($promise);
				}
			}
		}

	}
}
