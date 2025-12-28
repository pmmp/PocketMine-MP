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

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PacketHandlerInterface;
use pocketmine\utils\AssumptionFailedError;
use function assert;
use function implode;
use function is_a;
use function sort;
use const SORT_STRING;

/**
 * Uses reflection to find out what packets a PacketHandler class type will actually process, so that decoding can be
 * skipped for packets that are never handled.
 * The reflected information is cached for subsequent use.
 */
final class PacketHandlerInspector{

	/**
	 * @var PacketHandlerAction[][]
	 * @phpstan-var array<class-string<PacketHandler>, array<class-string<Packet>, PacketHandlerAction>>
	 */
	private static array $cache = [];

	/**
	 * @return PacketHandlerAction[]
	 * @phpstan-return array<class-string<Packet>, PacketHandlerAction>
	 */
	public static function getHandlerActions(PacketHandler $handler) : array{
		if(isset(self::$cache[$handler::class])){
			return self::$cache[$handler::class];
		}

		$whitelist = [];
		$interface = new \ReflectionClass(PacketHandlerInterface::class);
		$handlerReflect = new \ReflectionClass($handler);

		foreach($interface->getMethods(\ReflectionMethod::IS_PUBLIC) as $handlerMethod){
			try{
				$implementation = $handlerReflect->getMethod($handlerMethod->getName());
			}catch(\ReflectionException $e){
				throw new AssumptionFailedError("PacketHandler implements PacketHandlerInterface, this should be impossible");
			}

			$packetArg = $implementation->getParameters()[0] ?? throw new AssumptionFailedError("PacketHandlerInterface method should always have a packet as the first argument");
			$packetArgType = $packetArg->getType();
			if(!$packetArgType instanceof \ReflectionNamedType){
				continue;
			}
			$packetClass = $packetArgType->getName();
			assert(is_a($packetClass, DataPacket::class, true));

			$implementor = $implementation->getDeclaringClass()->getName();
			$whitelist[$packetClass] = $implementor !== PacketHandler::class ? PacketHandlerAction::HANDLED : PacketHandlerAction::DISCARD_WITH_DEBUG;
		}

		foreach($handlerReflect->getAttributes(SilentDiscard::class) as $attribute){
			$info = $attribute->newInstance();
			$packetClass = $info->packetClass;
			if(isset($whitelist[$packetClass]) && $whitelist[$packetClass] !== PacketHandlerAction::DISCARD_WITH_DEBUG){
				$shortName = (new \ReflectionClass($packetClass))->getShortName();
				\GlobalLogger::get()->warning("#[SilentDiscard($shortName)] has no effect on " . $handler::class . ", as the handler for $shortName is implemented");
				continue;
			}
			$whitelist[$packetClass] = PacketHandlerAction::DISCARD_SILENT;
		}

		$allowedPackets = [];
		foreach($whitelist as $packetClass => $action){
			if($action === PacketHandlerAction::HANDLED){
				$allowedPackets[] = (new \ReflectionClass($packetClass))->getShortName();
			}
		}
		sort($allowedPackets, SORT_STRING);
		\GlobalLogger::get()->debug("Packets handled by " . $handler::class . ": " . implode(', ', $allowedPackets));

		return self::$cache[$handler::class] = $whitelist;
	}
}
