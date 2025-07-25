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

namespace pocketmine\block\tile\utils;

use pocketmine\block\tile\Container;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

final class ContainerHelper{

	private function __construct(){
		//NOOP
	}

	/**
	 * @return Item[]|null
	 * @phpstan-return array<int, Item>|null
	 */
	public static function deserializeContents(CompoundTag $tag) : ?array{
		if(($inventoryTag = $tag->getTag(Container::TAG_ITEMS)) instanceof ListTag && $inventoryTag->getTagType() === NBT::TAG_Compound){
			$contents = [];
			/** @var CompoundTag $itemNBT */
			foreach($inventoryTag as $itemNBT){
				try{
					$contents[$itemNBT->getByte(SavedItemStackData::TAG_SLOT)] = Item::nbtDeserialize($itemNBT);
				}catch(SavedDataLoadingException $e){
					//TODO: not the best solution
					\GlobalLogger::get()->logException($e);
					continue;
				}
			}

			return $contents;
		}else{
			return null;
		}
	}

	/**
	 * @param Item[] $contents
	 * @phpstan-param array<int, Item> $contents
	 */
	public static function serializeContents(CompoundTag $tag, array $contents) : void{
		$items = [];
		foreach($contents as $slot => $item){
			$items[] = $item->nbtSerialize($slot);
		}

		$tag->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));
	}
}
