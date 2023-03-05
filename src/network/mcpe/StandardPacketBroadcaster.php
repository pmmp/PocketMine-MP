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
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\BinaryStream;
use function count;
use function spl_object_id;
use function strlen;

final class StandardPacketBroadcaster implements PacketBroadcaster{
	public function __construct(private Server $server){}

	public function broadcastPackets(array $recipients, array $packets) : void{
		$packetBufferTotalLengths = [];
		$packetBuffers = [];
		$compressors = [];
		/** @var NetworkSession[][][] $targetMap */
		$targetMap = [];
		foreach($recipients as $recipient){
			$serializerContext = $recipient->getPacketSerializerContext();
			$bufferId = spl_object_id($serializerContext);
			if(!isset($packetBuffers[$bufferId])){
				$packetBufferTotalLengths[$bufferId] = 0;
				$packetBuffers[$bufferId] = [];
				foreach($packets as $packet){
					$buffer = NetworkSession::encodePacketTimed(PacketSerializer::encoder($serializerContext), $packet);
					$packetBufferTotalLengths[$bufferId] += strlen($buffer);
					$packetBuffers[$bufferId][] = $buffer;
				}
			}

			//TODO: different compressors might be compatible, it might not be necessary to split them up by object
			$compressor = $recipient->getCompressor();
			$compressors[spl_object_id($compressor)] = $compressor;

			$targetMap[$bufferId][spl_object_id($compressor)][] = $recipient;
		}

		foreach($targetMap as $bufferId => $compressorMap){
			foreach($compressorMap as $compressorId => $compressorTargets){
				$compressor = $compressors[$compressorId];

				$threshold = $compressor->getCompressionThreshold();
				if(count($compressorTargets) > 1 && $threshold !== null && $packetBufferTotalLengths[$bufferId] >= $threshold){
					//do not prepare shared batch unless we're sure it will be compressed
					$stream = new BinaryStream();
					PacketBatch::encodeRaw($stream, $packetBuffers[$bufferId]);
					$batchBuffer = $stream->getBuffer();

					$promise = $this->server->prepareBatch(new PacketBatch($batchBuffer), $compressor, timings: Timings::$playerNetworkSendCompressBroadcast);
					foreach($compressorTargets as $target){
						$target->queueCompressed($promise);
					}
				}else{
					foreach($compressorTargets as $target){
						foreach($packetBuffers[$bufferId] as $packetBuffer){
							$target->addToSendBuffer($packetBuffer);
						}
					}
				}
			}
		}
	}
}
