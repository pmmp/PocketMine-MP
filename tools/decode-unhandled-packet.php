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

namespace pocketmine\tools\decode_unhandled_packet;

use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\utils\Utils;
use function base64_decode;
use function defined;
use function dirname;
use function in_array;
use function is_numeric;
use function var_dump;
use const PHP_BINARY;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param string[] $argv
 */
function main(array $argv) : int{
	if(!isset($argv[1])){
		echo "Usage: " . PHP_BINARY . " " . __FILE__ . " <base64 packet> [<protocol version>]\n";
		return 1;
	}
	$packetBuffer = Utils::assumeNotFalse(base64_decode($argv[1], true), "Not base64 encoded");
	$packet = PacketPool::getInstance()->getPacket($packetBuffer);
	if($packet === null){
		echo "Unknown Packet\n";
		return 1;
	}

	$protocolId = ProtocolInfo::CURRENT_PROTOCOL;
	if(isset($argv[2]) && is_numeric($argv[2]) && in_array((int) $argv[2], ProtocolInfo::ACCEPTED_PROTOCOL, true)){
		$protocolId = (int) $argv[2];
	}
	echo "Using protocol: $protocolId\n";
	$reader = PacketSerializer::decoder(
		$packetBuffer,
		0,
		new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary(GlobalItemTypeDictionary::getDictionaryProtocol($protocolId)))
	);
	$reader->setProtocolId($protocolId);
	$packet->decode($reader);

	var_dump($packet);
	return 0;
}

if(!defined('pocketmine\_PHPSTAN_ANALYSIS')){
	exit(main($argv));
}
