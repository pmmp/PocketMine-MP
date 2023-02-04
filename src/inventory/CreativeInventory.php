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

namespace pocketmine\inventory;

use pocketmine\crafting\CraftingManagerFromDataHelper;
use pocketmine\crafting\json\ItemStackData;
use pocketmine\item\Item;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;

final class CreativeInventory{
	use SingletonTrait;

	/** @var Item[] */
	private array $creative = [];

	private function __construct(){
		$creativeItems = CraftingManagerFromDataHelper::loadJsonArrayOfObjectsFile(
			Path::join(\pocketmine\BEDROCK_DATA_PATH, "creativeitems.json"),
			ItemStackData::class
		);
		foreach($creativeItems as $data){
			$item = CraftingManagerFromDataHelper::deserializeItemStack($data);
			if($item === null){
				//unknown item
				continue;
			}
			$this->add($item);
		}
	}

	/**
	 * Removes all previously added items from the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 */
	public function clear() : void{
		$this->creative = [];
	}

	/**
	 * @return Item[]
	 */
	public function getAll() : array{
		return $this->creative;
	}

	public function getItem(int $index) : ?Item{
		return $this->creative[$index] ?? null;
	}

	public function getItemIndex(Item $item) : int{
		foreach($this->creative as $i => $d){
			if($item->equals($d, true, false)){
				return $i;
			}
		}

		return -1;
	}

	/**
	 * Adds an item to the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 */
	public function add(Item $item) : void{
		$this->creative[] = clone $item;
	}

	/**
	 * Removes an item from the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 */
	public function remove(Item $item) : void{
		$index = $this->getItemIndex($item);
		if($index !== -1){
			unset($this->creative[$index]);
		}
	}

	public function contains(Item $item) : bool{
		return $this->getItemIndex($item) !== -1;
	}
}
