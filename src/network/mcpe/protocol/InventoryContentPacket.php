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
use function count;

class InventoryContentPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_CONTENT_PACKET;

	/** @var int */
	public $windowId;
	/** @var ItemStackWrapper[] */
	public $items = [];

	/**
	 * @param ItemStackWrapper[] $items
	 *
	 * @return InventoryContentPacket
	 */
	public static function create(int $windowId, array $items) : self{
		$result = new self;
		$result->windowId = $windowId;
		$result->items = $items;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->windowId = $in->getUnsignedVarInt();
		$count = $in->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$this->items[] = ItemStackWrapper::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt($this->windowId);
		$out->putUnsignedVarInt(count($this->items));
		foreach($this->items as $item){
			$item->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleInventoryContent($this);
	}
}
