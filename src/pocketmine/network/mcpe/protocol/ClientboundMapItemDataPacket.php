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


namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\Color;

class ClientboundMapItemDataPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET;

	const BITFLAG_TEXTURE_UPDATE = 0x02;
	const BITFLAG_DECORATION_UPDATE = 0x04;

	public $mapId;
	public $type;

	public $eids = [];
	public $scale;
	public $decorations = [];

	public $width;
	public $height;
	public $xOffset = 0;
	public $yOffset = 0;
	/** @var Color[][] */
	public $colors = [];

	public function decode(){
		$this->mapId = $this->getEntityUniqueId();
		$this->type = $this->getUnsignedVarInt();

		if(($this->type & 0x08) !== 0){
			$count = $this->getUnsignedVarInt();
			for($i = 0; $i < $count; ++$i){
				$this->eids[] = $this->getEntityUniqueId();
			}
		}

		if(($this->type & (self::BITFLAG_DECORATION_UPDATE | self::BITFLAG_TEXTURE_UPDATE)) !== 0){ //Decoration bitflag or colour bitflag
			$this->scale = $this->getByte();
		}

		if(($this->type & self::BITFLAG_DECORATION_UPDATE) !== 0){
			$count = $this->getUnsignedVarInt();
			for($i = 0; $i < $count; ++$i){
				$weird = $this->getVarInt();
				$this->decorations[$i]["rot"] = $weird & 0x0f;
				$this->decorations[$i]["img"] = $weird >> 4;

				$this->decorations[$i]["xOffset"] = $this->getByte();
				$this->decorations[$i]["yOffset"] = $this->getByte();
				$this->decorations[$i]["label"] = $this->getString();

				$this->decorations[$i]["color"] = Color::fromARGB($this->getLInt()); //already BE, don't need to reverse it again
			}
		}

		if(($this->type & self::BITFLAG_TEXTURE_UPDATE) !== 0){
			$this->width = $this->getVarInt();
			$this->height = $this->getVarInt();
			$this->xOffset = $this->getVarInt();
			$this->yOffset = $this->getVarInt();
			for($y = 0; $y < $this->height; ++$y){
				for($x = 0; $x < $this->width; ++$x){
					$this->colors[$y][$x] = Color::fromABGR($this->getUnsignedVarInt());
				}
			}
		}
	}

	public function encode(){
		$this->reset();
		$this->putEntityUniqueId($this->mapId);

		$type = 0;
		if(($eidsCount = count($this->eids)) > 0){
			$type |= 0x08;
		}
		if(($decorationCount = count($this->decorations)) > 0){
			$type |= self::BITFLAG_DECORATION_UPDATE;
		}
		if(count($this->colors) > 0){
			$type |= self::BITFLAG_TEXTURE_UPDATE;
		}

		$this->putUnsignedVarInt($type);

		if(($type & 0x08) !== 0){ //TODO: find out what these are for
			$this->putUnsignedVarInt($eidsCount);
			foreach($this->eids as $eid){
				$this->putEntityUniqueId($eid);
			}
		}

		if(($type & (self::BITFLAG_TEXTURE_UPDATE | self::BITFLAG_DECORATION_UPDATE)) !== 0){
			$this->putByte($this->scale);
		}

		if(($type & self::BITFLAG_DECORATION_UPDATE) !== 0){
			$this->putUnsignedVarInt($decorationCount);
			foreach($this->decorations as $decoration){
				$this->putVarInt(($decoration["rot"] & 0x0f) | ($decoration["img"] << 4));
				$this->putByte($decoration["xOffset"]);
				$this->putByte($decoration["yOffset"]);
				$this->putString($decoration["label"]);
				$this->putLInt($decoration["color"]->toARGB());
			}
		}

		if(($type & self::BITFLAG_TEXTURE_UPDATE) !== 0){
			$this->putVarInt($this->width);
			$this->putVarInt($this->height);
			$this->putVarInt($this->xOffset);
			$this->putVarInt($this->yOffset);
			for($y = 0; $y < $this->height; ++$y){
				for($x = 0; $x < $this->width; ++$x){
					$this->putUnsignedVarInt($this->colors[$y][$x]->toABGR());
				}
			}
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleClientboundMapItemData($this);
	}
}