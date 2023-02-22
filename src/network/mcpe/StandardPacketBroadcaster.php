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

use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\Server;
use pocketmine\utils\BinaryStream;
use function array_map;
use function spl_object_id;

final class StandardPacketBroadcaster implements PacketBroadcaster{
	public function __construct(private Server $server){}

	public function broadcastPackets(array $recipients, array $packets) : void{
		$batchBuffers = [];

		/** @var string[][] $packetBuffers */
		$packetBuffers = [];
		$compressors = [];
		/** @var NetworkSession[][][] $targetMap */
		$targetMap = [];
		foreach($recipients as $recipient){
			$serializerContext = $recipient->getPacketSerializerContext();
			$bufferId = spl_object_id($serializerContext);
			if(!isset($batchBuffers[$bufferId])){
				$packetBuffers[$bufferId] = array_map(function(ClientboundPacket $packet) use ($serializerContext) : string{
					return NetworkSession::encodePacketTimed(PacketSerializer::encoder($serializerContext), $packet);
				}, $packets);
				$stream = new BinaryStream();
				PacketBatch::encodeRaw($stream, $packetBuffers[$bufferId]);
				$batchBuffers[$bufferId] = $stream->getBuffer();
			}

			//TODO: different compressors might be compatible, it might not be necessary to split them up by object
			$compressor = $recipient->getCompressor();
			$compressors[spl_object_id($compressor)] = $compressor;

			$targetMap[$bufferId][spl_object_id($compressor)][] = $recipient;
		}

		foreach($targetMap as $bufferId => $compressorMap){
			$batchBuffer = $batchBuffers[$bufferId];
			foreach($compressorMap as $compressorId => $compressorTargets){
				$compressor = $compressors[$compressorId];
				if(!$compressor->willCompress($batchBuffer)){
					foreach($compressorTargets as $target){
						foreach($packetBuffers[$bufferId] as $packetBuffer){
							$target->addToSendBuffer($packetBuffer);
						}
					}
				}else{
					$promise = $this->server->prepareBatch(new PacketBatch($batchBuffer), $compressor);
					foreach($compressorTargets as $target){
						$target->queueCompressed($promise);
					}
				}
			}
		}
	}
}
