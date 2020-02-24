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

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\utils\Color;
use function count;
#ifndef COMPILE
use pocketmine\utils\Binary;
#endif

class ClientboundMapItemDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET;

	public const BITFLAG_TEXTURE_UPDATE = 0x02;
	public const BITFLAG_DECORATION_UPDATE = 0x04;

	/** @var int */
	public $mapId;
	/** @var int */
	public $type;
	/** @var int */
	public $dimensionId = DimensionIds::OVERWORLD;
	/** @var bool */
	public $isLocked = false;

	/** @var int[] */
	public $eids = [];
	/** @var int */
	public $scale;

	/** @var MapTrackedObject[] */
	public $trackedEntities = [];
	/** @var MapDecoration[] */
	public $decorations = [];

	/** @var int */
	public $width;
	/** @var int */
	public $height;
	/** @var int */
	public $xOffset = 0;
	/** @var int */
	public $yOffset = 0;
	/** @var Color[][] */
	public $colors = [];

	protected function decodePayload() : void{
		$this->mapId = $this->buf->getEntityUniqueId();
		$this->type = $this->buf->getUnsignedVarInt();
		$this->dimensionId = $this->buf->getByte();
		$this->isLocked = $this->buf->getBool();

		if(($this->type & 0x08) !== 0){
			$count = $this->buf->getUnsignedVarInt();
			for($i = 0; $i < $count; ++$i){
				$this->eids[] = $this->buf->getEntityUniqueId();
			}
		}

		if(($this->type & (0x08 | self::BITFLAG_DECORATION_UPDATE | self::BITFLAG_TEXTURE_UPDATE)) !== 0){ //Decoration bitflag or colour bitflag
			$this->scale = $this->buf->getByte();
		}

		if(($this->type & self::BITFLAG_DECORATION_UPDATE) !== 0){
			for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
				$object = new MapTrackedObject();
				$object->type = $this->buf->getLInt();
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					$this->buf->getBlockPosition($object->x, $object->y, $object->z);
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					$object->entityUniqueId = $this->buf->getEntityUniqueId();
				}else{
					throw new BadPacketException("Unknown map object type $object->type");
				}
				$this->trackedEntities[] = $object;
			}

			for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
				$icon = $this->buf->getByte();
				$rotation = $this->buf->getByte();
				$xOffset = $this->buf->getByte();
				$yOffset = $this->buf->getByte();
				$label = $this->buf->getString();
				$color = Color::fromRGBA(Binary::flipIntEndianness($this->buf->getUnsignedVarInt()));
				$this->decorations[] = new MapDecoration($icon, $rotation, $xOffset, $yOffset, $label, $color);
			}
		}

		if(($this->type & self::BITFLAG_TEXTURE_UPDATE) !== 0){
			$this->width = $this->buf->getVarInt();
			$this->height = $this->buf->getVarInt();
			$this->xOffset = $this->buf->getVarInt();
			$this->yOffset = $this->buf->getVarInt();

			$count = $this->buf->getUnsignedVarInt();
			if($count !== $this->width * $this->height){
				throw new BadPacketException("Expected colour count of " . ($this->height * $this->width) . " (height $this->height * width $this->width), got $count");
			}

			for($y = 0; $y < $this->height; ++$y){
				for($x = 0; $x < $this->width; ++$x){
					$this->colors[$y][$x] = Color::fromRGBA(Binary::flipIntEndianness($this->buf->getUnsignedVarInt()));
				}
			}
		}
	}

	protected function encodePayload() : void{
		$this->buf->putEntityUniqueId($this->mapId);

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

		$this->buf->putUnsignedVarInt($type);
		$this->buf->putByte($this->dimensionId);
		$this->buf->putBool($this->isLocked);

		if(($type & 0x08) !== 0){ //TODO: find out what these are for
			$this->buf->putUnsignedVarInt($eidsCount);
			foreach($this->eids as $eid){
				$this->buf->putEntityUniqueId($eid);
			}
		}

		if(($type & (0x08 | self::BITFLAG_TEXTURE_UPDATE | self::BITFLAG_DECORATION_UPDATE)) !== 0){
			$this->buf->putByte($this->scale);
		}

		if(($type & self::BITFLAG_DECORATION_UPDATE) !== 0){
			$this->buf->putUnsignedVarInt(count($this->trackedEntities));
			foreach($this->trackedEntities as $object){
				$this->buf->putLInt($object->type);
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					$this->buf->putBlockPosition($object->x, $object->y, $object->z);
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					$this->buf->putEntityUniqueId($object->entityUniqueId);
				}else{
					throw new \InvalidArgumentException("Unknown map object type $object->type");
				}
			}

			$this->buf->putUnsignedVarInt($decorationCount);
			foreach($this->decorations as $decoration){
				$this->buf->putByte($decoration->getIcon());
				$this->buf->putByte($decoration->getRotation());
				$this->buf->putByte($decoration->getXOffset());
				$this->buf->putByte($decoration->getYOffset());
				$this->buf->putString($decoration->getLabel());
				$this->buf->putUnsignedVarInt(Binary::flipIntEndianness($decoration->getColor()->toRGBA()));
			}
		}

		if(($type & self::BITFLAG_TEXTURE_UPDATE) !== 0){
			$this->buf->putVarInt($this->width);
			$this->buf->putVarInt($this->height);
			$this->buf->putVarInt($this->xOffset);
			$this->buf->putVarInt($this->yOffset);

			$this->buf->putUnsignedVarInt($this->width * $this->height); //list count, but we handle it as a 2D array... thanks for the confusion mojang

			for($y = 0; $y < $this->height; ++$y){
				for($x = 0; $x < $this->width; ++$x){
					//if mojang had any sense this would just be a regular LE int
					$this->buf->putUnsignedVarInt(Binary::flipIntEndianness($this->colors[$y][$x]->toRGBA()));
				}
			}
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleClientboundMapItemData($this);
	}
}
