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

namespace pocketmine\network\mcpe\cache;

use pocketmine\inventory\CreativeCategory;
use pocketmine\inventory\CreativeGroup;
use pocketmine\inventory\CreativeInventory;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeGroupEntry;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeItemEntry;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\utils\SingletonTrait;
use function array_map;
use function spl_object_id;

final class CreativeInventoryCache{
	use SingletonTrait;

	/**
	 * @var CreativeInventoryCacheEntry[]
	 * @phpstan-var array<int, CreativeInventoryCacheEntry>
	 */
	private array $caches = [];

	private function getCacheEntry(CreativeInventory $inventory) : CreativeInventoryCacheEntry{
		$id = spl_object_id($inventory);
		if(!isset($this->caches[$id])){
			$inventory->getDestructorCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$inventory->getContentChangedCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$this->caches[$id] = $this->buildCacheEntry($inventory);
		}
		return $this->caches[$id];
	}

	/**
	 * Rebuild the cache for the given inventory.
	 */
	private function buildCacheEntry(CreativeInventory $inventory) : CreativeInventoryCacheEntry{
		$groupList = [];

		$typeConverter = TypeConverter::getInstance();

		$index = 0;
		$groupIndexes = [];
		foreach($inventory->getItemGroups() as $group){
			if(!isset($groupIndexes[$id = spl_object_id($group)])){
				$groupIndexes[$id] = $index++;
				$groupList[] = $group;
			}
		}

		//creative inventory may have holes if items were unregistered - ensure network IDs used are always consistent
		$items = [];
		foreach($inventory->getAll() as $k => $item){
			$items[] = new CreativeItemEntry(
				$k,
				$typeConverter->coreItemStackToNet($item),
				$groupIndexes[spl_object_id($inventory->getItemGroupByIndex($k) ?? throw new \AssertionError("Item group not found"))]
			);
		}

		return new CreativeInventoryCacheEntry($groupList, $items);
	}

	public function buildPacket(CreativeInventory $inventory, NetworkSession $session) : CreativeContentPacket{
		$player = $session->getPlayer() ?? throw new \LogicException("Cannot prepare creative data for a session without a player");
		$language = $player->getLanguage();
		$forceLanguage = $player->getServer()->isLanguageForced();
		$typeConverter = $session->getTypeConverter();
		$cachedEntry = $this->getCacheEntry($inventory);
		$translate = function(Translatable $translatable) use ($session, $language, $forceLanguage) : string{
			if(!$forceLanguage){
				[$message,] = $session->prepareClientTranslatableMessage($translatable);
			}else{
				$message = $language->translate($translatable);
			}
			return $message;
		};

		return CreativeContentPacket::create(
			array_map(fn(CreativeGroup $group) => new CreativeGroupEntry(
				match ($group->category) {
					CreativeCategory::CONSTRUCTION => CreativeContentPacket::CATEGORY_CONSTRUCTION,
					CreativeCategory::NATURE => CreativeContentPacket::CATEGORY_NATURE,
					CreativeCategory::EQUIPMENT => CreativeContentPacket::CATEGORY_EQUIPMENT,
					CreativeCategory::ITEMS => CreativeContentPacket::CATEGORY_ITEMS
				},
				$group->name instanceof Translatable ? $translate($group->name) : $group->name,
				$group->icon === null ? ItemStack::null() : $typeConverter->coreItemStackToNet($group->icon)
			), $cachedEntry->groupEntries),
			$cachedEntry->itemEntries
		);
	}
}
