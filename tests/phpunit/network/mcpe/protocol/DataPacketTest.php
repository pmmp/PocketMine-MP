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

namespace pocketmine\network\mcpe\protocol;

use PHPUnit\Framework\TestCase;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class DataPacketTest extends TestCase{

	public function testHeaderFidelity() : void{
		$pk = new TestPacket();
		$pk->senderSubId = 3;
		$pk->recipientSubId = 2;
		$pk->encode();

		$pk2 = new TestPacket();
		$pk2->setSerializer(new PacketSerializer($pk->getSerializer()->getBuffer()));
		$pk2->decode();
		self::assertSame($pk2->senderSubId, 3);
		self::assertSame($pk2->recipientSubId, 2);
	}
}
