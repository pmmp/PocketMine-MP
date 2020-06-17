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
use function strlen;

class LoginPacketTest extends TestCase{

	public function testInvalidChainDataJsonHandling() : void{
		$stream = new PacketSerializer();
		$stream->putUnsignedVarInt(ProtocolInfo::LOGIN_PACKET);
		$payload = '{"chain":[]'; //intentionally malformed
		$stream->putInt(ProtocolInfo::CURRENT_PROTOCOL);

		$stream2 = new PacketSerializer();
		$stream2->putLInt(strlen($payload));
		$stream2->put($payload);
		$stream->putString($stream2->getBuffer());

		$pk = PacketPool::getInstance()->getPacket($stream->getBuffer());

		$this->expectException(PacketDecodeException::class);
		$pk->decode(); //bang
	}
}
