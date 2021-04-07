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

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class MobArmorEquipmentPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET;

	/** @var int */
	public $entityRuntimeId;

	//this intentionally doesn't use an array because we don't want any implicit dependencies on internal order

	/** @var ItemStackWrapper */
	public $head;
	/** @var ItemStackWrapper */
	public $chest;
	/** @var ItemStackWrapper */
	public $legs;
	/** @var ItemStackWrapper */
	public $feet;

	public static function create(int $entityRuntimeId, ItemStackWrapper $head, ItemStackWrapper $chest, ItemStackWrapper $legs, ItemStackWrapper $feet) : self{
		$result = new self;
		$result->entityRuntimeId = $entityRuntimeId;
		$result->head = $head;
		$result->chest = $chest;
		$result->legs = $legs;
		$result->feet = $feet;

		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->head = ItemStackWrapper::read($in);
		$this->chest = ItemStackWrapper::read($in);
		$this->legs = ItemStackWrapper::read($in);
		$this->feet = ItemStackWrapper::read($in);
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$this->head->write($out);
		$this->chest->write($out);
		$this->legs->write($out);
		$this->feet->write($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleMobArmorEquipment($this);
	}
}
