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

namespace pocketmine\item;

use pocketmine\block\BlockFactory;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

/**
 * Manages deserializing item types from their legacy ID/metadata.
 * This is primarily needed for loading inventories saved in the world (or playerdata storage).
 */
class ItemFactory{
	use SingletonTrait;

	/** @var Item[] */
	private array $list = [];

	public function __construct(){
		foreach(VanillaItems::getAll() as $item){
			if($item->isNull()){
				continue;
			}
			$this->register($item);
		}
	}

	/**
	 * Maps an item type to its corresponding ID. This is necessary to ensure that the item is correctly loaded when
	 * reading data from disk storage.
	 *
	 * NOTE: If you are registering a new item type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered item without specifying the
	 * $override parameter.
	 */
	public function register(Item $item, bool $override = false) : void{
		$id = $item->getTypeId();

		if(!$override && $this->isRegistered($id)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		$this->list[$id] = clone $item;
	}

	private static function itemToBlockId(int $id) : int{
		if($id > 0){
			throw new \InvalidArgumentException("ID $id is not a block ID");
		}
		return -$id;
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 */
	public function isRegistered(int $id) : bool{
		if($id <= 0){
			return BlockFactory::getInstance()->isRegistered(self::itemToBlockId($id));
		}

		return isset($this->list[$id]);
	}

	/**
	 * @return Item[]
	 */
	public function getAllKnownTypes() : array{
		return $this->list;
	}
}
