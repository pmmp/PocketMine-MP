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

namespace pocketmine\network\mcpe\protocol\serializer;

use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PacketPool;

class PacketBatch{

	/** @var string */
	private $buffer;

	public function __construct(string $buffer){
		$this->buffer = $buffer;
	}

	/**
	 * @return \Generator|Packet[]
	 * @phpstan-return \Generator<int, Packet, void, void>
	 * @throws PacketDecodeException
	 */
	public function getPackets(PacketPool $packetPool, int $max) : \Generator{
		$serializer = new PacketSerializer($this->buffer);
		for($c = 0; $c < $max and !$serializer->feof(); ++$c){
			yield $c => $packetPool->getPacket($serializer->getString());
		}
		if(!$serializer->feof()){
			throw new PacketDecodeException("Reached limit of $max packets in a single batch");
		}
	}

	/**
	 * Constructs a packet batch from the given list of packets.
	 *
	 * @param Packet ...$packets
	 *
	 * @return PacketBatch
	 */
	public static function fromPackets(Packet ...$packets) : self{
		$serializer = new PacketSerializer();
		foreach($packets as $packet){
			$packet->encode();
			$serializer->putString($packet->getSerializer()->getBuffer());
		}
		return new self($serializer->getBuffer());
	}

	public function getBuffer() : string{
		return $this->buffer;
	}
}
