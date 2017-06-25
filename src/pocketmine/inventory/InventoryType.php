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

use pocketmine\network\mcpe\protocol\types\WindowTypes;

/**
 * Saves all the information regarding default inventory sizes and types
 */
class InventoryType{

	//NOTE: Do not confuse these with the network IDs.
	const CHEST = 0;
	const DOUBLE_CHEST = 1;
	const PLAYER = 2;
	const FURNACE = 3;
	const CRAFTING = 4;
	const WORKBENCH = 5;
	const STONECUTTER = 6;
	const BREWING_STAND = 7;
	const ANVIL = 8;
	const ENCHANT_TABLE = 9;

	private static $default = [];

	private $size;
	private $title;
	private $typeId;

	/**
	 * @param $index
	 *
	 * @return InventoryType|null
	 */
	public static function get($index){
		return static::$default[$index] ?? null;
	}

	public static function init(){
		if(count(static::$default) > 0){
			return;
		}

		//TODO: move network stuff out of here
		//TODO: move inventory data to json
		static::$default = [
			static::CHEST =>         new InventoryType(27, "Chest", WindowTypes::CONTAINER),
			static::DOUBLE_CHEST =>  new InventoryType(27 + 27, "Double Chest", WindowTypes::CONTAINER),
			static::PLAYER =>        new InventoryType(36 + 4, "Player", WindowTypes::INVENTORY), //36 CONTAINER, 4 ARMOR
			static::CRAFTING =>      new InventoryType(5, "Crafting", WindowTypes::INVENTORY), //yes, the use of INVENTORY is intended! 4 CRAFTING slots, 1 RESULT
			static::WORKBENCH =>     new InventoryType(10, "Crafting", WindowTypes::WORKBENCH), //9 CRAFTING slots, 1 RESULT
			static::FURNACE =>       new InventoryType(3, "Furnace", WindowTypes::FURNACE), //2 INPUT, 1 OUTPUT
			static::ENCHANT_TABLE => new InventoryType(2, "Enchant", WindowTypes::ENCHANTMENT), //1 INPUT/OUTPUT, 1 LAPIS
			static::BREWING_STAND => new InventoryType(4, "Brewing", WindowTypes::BREWING_STAND), //1 INPUT, 3 POTION
			static::ANVIL =>         new InventoryType(3, "Anvil", WindowTypes::ANVIL) //2 INPUT, 1 OUTP
		];
	}

	/**
	 * @param int    $defaultSize
	 * @param string $defaultTitle
	 * @param int    $typeId
	 */
	private function __construct($defaultSize, $defaultTitle, $typeId = 0){
		$this->size = $defaultSize;
		$this->title = $defaultTitle;
		$this->typeId = $typeId;
	}

	/**
	 * @return int
	 */
	public function getDefaultSize(){
		return $this->size;
	}

	/**
	 * @return string
	 */
	public function getDefaultTitle(){
		return $this->title;
	}

	/**
	 * @return int
	 */
	public function getNetworkType(){
		return $this->typeId;
	}
}