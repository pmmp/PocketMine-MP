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

namespace pocketmine\data\bedrock\item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\Binary;
use function array_map;
use function count;

final class SavedItemStackData{

	public const TAG_COUNT = "Count";
	public const TAG_SLOT = "Slot";
	public const TAG_WAS_PICKED_UP = "WasPickedUp";
	public const TAG_CAN_PLACE_ON = "CanPlaceOn";
	public const TAG_CAN_DESTROY = "CanDestroy";

	/**
	 * @param string[] $canPlaceOn
	 * @param string[] $canDestroy
	 */
	public function __construct(
		private SavedItemData $typeData,
		private int $count,
		private ?int $slot,
		private ?bool $wasPickedUp,
		private array $canPlaceOn,
		private array $canDestroy
	){}

	public function getTypeData() : SavedItemData{ return $this->typeData; }

	public function getCount() : int{ return $this->count; }

	public function getSlot() : ?int{ return $this->slot; }

	public function getWasPickedUp() : ?bool{ return $this->wasPickedUp; }

	/** @return string[] */
	public function getCanPlaceOn() : array{ return $this->canPlaceOn; }

	/** @return string[] */
	public function getCanDestroy() : array{ return $this->canDestroy; }

	public function toNbt() : CompoundTag{
		$result = CompoundTag::create();
		$result->setByte(self::TAG_COUNT, Binary::signByte($this->count));

		if($this->slot !== null){
			$result->setByte(self::TAG_SLOT, Binary::signByte($this->slot));
		}
		if($this->wasPickedUp !== null){
			$result->setByte(self::TAG_WAS_PICKED_UP, $this->wasPickedUp ? 1 : 0);
		}
		if(count($this->canPlaceOn) !== 0){
			$result->setTag(self::TAG_CAN_PLACE_ON, new ListTag(array_map(fn(string $s) => new StringTag($s), $this->canPlaceOn)));
		}
		if(count($this->canDestroy) !== 0){
			$result->setTag(self::TAG_CAN_DESTROY, new ListTag(array_map(fn(string $s) => new StringTag($s), $this->canDestroy)));
		}

		return $result->merge($this->typeData->toNbt());
	}
}
