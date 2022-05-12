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

use pocketmine\data\bedrock\blockstate\BlockStateData;
use pocketmine\data\bedrock\blockstate\BlockStateDeserializeException;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use function str_starts_with;

final class SavedItemData{

	public const TAG_NAME = "Name";
	private const TAG_DAMAGE = "Damage";
	public const TAG_BLOCK = "Block";
	private const TAG_TAG = "tag";
	private const TAG_ITEM_IDENTIFIER = "ItemIdentifier";

	public function __construct(
		private string $name,
		private int $meta = 0,
		private ?BlockStateData $block = null,
		private ?CompoundTag $tag = null
	){}

	public function getName() : string{ return $this->name; }

	public function getMeta() : int{ return $this->meta; }

	public function getBlock() : ?BlockStateData{ return $this->block; }

	public function getTag() : ?CompoundTag{ return $this->tag; }

	public static function fromNbt(CompoundTag $tag) : self{
		try{
			//required
			$name = $tag->getString(self::TAG_NAME);
			$damage = $tag->getShort(self::TAG_DAMAGE);

			//optional
			$blockStateNbt = $tag->getCompoundTag(self::TAG_BLOCK);
			$extraData = $tag->getCompoundTag(self::TAG_TAG);
		}catch(NbtException $e){
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}

		//TODO: this hack probably doesn't belong here; it's necessary to deal with spawn eggs from before 1.16.100
		if(
			$name === ItemTypeIds::SPAWN_EGG &&
			($itemIdentifierTag = $tag->getTag(self::TAG_ITEM_IDENTIFIER)) instanceof StringTag &&
			str_starts_with($itemIdentifierTag->getValue(), "minecraft:")
		){
			\GlobalLogger::get()->debug("Handling legacy spawn egg for " . $itemIdentifierTag->getValue());
			$name = $itemIdentifierTag->getValue() . "_spawn_egg";
		}

		try{
			$blockStateData = $blockStateNbt !== null ? BlockStateData::fromNbt($blockStateNbt) : null;
		}catch(BlockStateDeserializeException $e){
			throw new SavedDataLoadingException("Failed to load item saved data: " . $e->getMessage(), 0, $e);
		}

		return new self(
			$name,
			$damage,
			$blockStateData,
			$extraData
		);
	}

	public function toNbt() : CompoundTag{
		$result = CompoundTag::create();
		$result->setString(self::TAG_NAME, $this->name);
		$result->setShort(self::TAG_DAMAGE, $this->meta);

		if($this->block !== null){
			$result->setTag(self::TAG_BLOCK, $this->block->toNbt());
		}
		if($this->tag !== null){
			$result->setTag(self::TAG_TAG, $this->tag);
		}

		return $result;
	}
}
