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

use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\data\CreativeContentCache;
use pocketmine\inventory\data\CreativeGroup;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeItemEntry;
use pocketmine\utils\SingletonTrait;
use function array_reduce;
use function spl_object_id;

final class CreativeInventoryCache{
	use SingletonTrait;

	/**
	 * @var CreativeContentCache[]
	 * @phpstan-var array<int, CreativeContentCache>
	 */
	private array $caches = [];

	/**
	 * @var CreativeItemEntry[][]
	 * @phpstan-var array<int, list<CreativeItemEntry>>
	 */
	private array $itemEntries = [];

	public function getCache(CreativeInventory $inventory) : CreativeContentCache{
		$id = spl_object_id($inventory);
		if(!isset($this->caches[$id])){
			$inventory->getDestructorCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$inventory->getContentChangedCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$this->caches[$id] = $this->buildCreativeInventoryCache($inventory);
		}
		return $this->caches[$id];
	}

	/**
	 * Rebuild the cache for the given inventory.
	 */
	private function buildCreativeInventoryCache(CreativeInventory $inventory) : CreativeContentCache{
		/** @var CreativeGroup[] $groups */
		$groups = [];
		/** @var CreativeItemEntry[] $items */
		$items = [];

		$typeConverter = TypeConverter::getInstance();

		$index = 0;
		$mappedGroups = array_reduce($inventory->getItemGroups(), function (array $carry, CreativeGroup $group) use (&$index, &$groups) : array{
			if (!isset($carry[$id = spl_object_id($group)])) {
				$carry[$id] = $index++;
				$groups[] = $group;
			}
			return $carry;
		}, []);

		//creative inventory may have holes if items were unregistered - ensure network IDs used are always consistent
		foreach($inventory->getAll() as $k => $item){
			$items[] = new CreativeItemEntry(
				$k,
				$typeConverter->coreItemStackToNet($item),
				$mappedGroups[spl_object_id($inventory->getItemGroupByIndex($k) ?? throw new \AssertionError("Item group not found"))]
			);
		}

		return new CreativeContentCache($groups, $items);
	}
}
