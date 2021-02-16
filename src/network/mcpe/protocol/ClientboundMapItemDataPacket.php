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

use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\utils\Binary;
#ifndef COMPILE
use function count;
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

	protected function decodePayload(PacketSerializer $in) : void{
		$this->mapId = $in->getEntityUniqueId();
		$this->type = $in->getUnsignedVarInt();
		$this->dimensionId = $in->getByte();
		$this->isLocked = $in->getBool();

		if(($this->type & 0x08) !== 0){
			$count = $in->getUnsignedVarInt();
			for($i = 0; $i < $count; ++$i){
				$this->eids[] = $in->getEntityUniqueId();
			}
		}

		if(($this->type & (0x08 | self::BITFLAG_DECORATION_UPDATE | self::BITFLAG_TEXTURE_UPDATE)) !== 0){ //Decoration bitflag or colour bitflag
			$this->scale = $in->getByte();
		}

		if(($this->type & self::BITFLAG_DECORATION_UPDATE) !== 0){
			for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
				$object = new MapTrackedObject();
				$object->type = $in->getLInt();
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					$in->getBlockPosition($object->x, $object->y, $object->z);
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					$object->entityUniqueId = $in->getEntityUniqueId();
				}else{
					throw new PacketDecodeException("Unknown map object type $object->type");
				}
				$this->trackedEntities[] = $object;
			}

			for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
				$icon = $in->getByte();
				$rotation = $in->getByte();
				$xOffset = $in->getByte();
				$yOffset = $in->getByte();
				$label = $in->getString();
				$color = Color::fromRGBA(Binary::flipIntEndianness($in->getUnsignedVarInt()));
				$this->decorations[] = new MapDecoration($icon, $rotation, $xOffset, $yOffset, $label, $color);
			}
		}

		if(($this->type & self::BITFLAG_TEXTURE_UPDATE) !== 0){
			$this->width = $in->getVarInt();
			$this->height = $in->getVarInt();
			$this->xOffset = $in->getVarInt();
			$this->yOffset = $in->getVarInt();

			$count = $in->getUnsignedVarInt();
			if($count !== $this->width * $this->height){
				throw new PacketDecodeException("Expected colour count of " . ($this->height * $this->width) . " (height $this->height * width $this->width), got $count");
			}

			for($y = 0; $y < $this->height; ++$y){
				for($x = 0; $x < $this->width; ++$x){
					$this->colors[$y][$x] = Color::fromRGBA(Binary::flipIntEndianness($in->getUnsignedVarInt()));
				}
			}
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putEntityUniqueId($this->mapId);

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

		$out->putUnsignedVarInt($type);
		$out->putByte($this->dimensionId);
		$out->putBool($this->isLocked);

		if(($type & 0x08) !== 0){ //TODO: find out what these are for
			$out->putUnsignedVarInt($eidsCount);
			foreach($this->eids as $eid){
				$out->putEntityUniqueId($eid);
			}
		}

		if(($type & (0x08 | self::BITFLAG_TEXTURE_UPDATE | self::BITFLAG_DECORATION_UPDATE)) !== 0){
			$out->putByte($this->scale);
		}

		if(($type & self::BITFLAG_DECORATION_UPDATE) !== 0){
			$out->putUnsignedVarInt(count($this->trackedEntities));
			foreach($this->trackedEntities as $object){
				$out->putLInt($object->type);
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					$out->putBlockPosition($object->x, $object->y, $object->z);
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					$out->putEntityUniqueId($object->entityUniqueId);
				}else{
					throw new \InvalidArgumentException("Unknown map object type $object->type");
				}
			}

			$out->putUnsignedVarInt($decorationCount);
			foreach($this->decorations as $decoration){
				$out->putByte($decoration->getIcon());
				$out->putByte($decoration->getRotation());
				$out->putByte($decoration->getXOffset());
				$out->putByte($decoration->getYOffset());
				$out->putString($decoration->getLabel());
				$out->putUnsignedVarInt(Binary::flipIntEndianness($decoration->getColor()->toRGBA()));
			}
		}

		if(($type & self::BITFLAG_TEXTURE_UPDATE) !== 0){
			$out->putVarInt($this->width);
			$out->putVarInt($this->height);
			$out->putVarInt($this->xOffset);
			$out->putVarInt($this->yOffset);

			$out->putUnsignedVarInt($this->width * $this->height); //list count, but we handle it as a 2D array... thanks for the confusion mojang

			for($y = 0; $y < $this->height; ++$y){
				for($x = 0; $x < $this->width; ++$x){
					//if mojang had any sense this would just be a regular LE int
					$out->putUnsignedVarInt(Binary::flipIntEndianness($this->colors[$y][$x]->toRGBA()));
				}
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundMapItemData($this);
	}
}
