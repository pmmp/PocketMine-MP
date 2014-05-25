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

namespace pocketmine\inventory;

/**
 * Saves all the information regarding default inventory sizes and types
 */
class InventoryType{
	const CHEST = 0;
	const DOUBLE_CHEST = 1;
	const PLAYER = 2;
	const FURNACE = 3;
	const CRAFTING = 4;
	const WORKBENCH = 5;
	const STONECUTTER = 6;

	private static $default = [];

	private $size;
	private $title;
	private $typeId;

	/**
	 * @param $index
	 *
	 * @return InventoryType
	 */
	public static function get($index){
		return isset(static::$default[$index]) ? static::$default[$index] : null;
	}

	public static function init(){
		if(count(static::$default) > 0){
			return;
		}

		static::$default[static::CHEST] = new InventoryType(27, "Chest", 0);
		static::$default[static::DOUBLE_CHEST] = new InventoryType(27 + 27, "Double Chest", 0);
		static::$default[static::PLAYER] = new InventoryType(40, "Player", 0); //27 CONTAINER, 4 ARMOR (9 reference HOTBAR slots)
		static::$default[static::FURNACE] = new InventoryType(3, "Furnace", 2);
		static::$default[static::CRAFTING] = new InventoryType(5, "Crafting", 1); //4 CRAFTING slots, 1 RESULT
		static::$default[static::WORKBENCH] = new InventoryType(10, "Crafting", 1); //9 CRAFTING slots, 1 RESULT
		static::$default[static::STONECUTTER] = new InventoryType(10, "Crafting", 3); //9 CRAFTING slots, 1 RESULT
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