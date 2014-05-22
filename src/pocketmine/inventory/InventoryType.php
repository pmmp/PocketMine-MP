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
	const PLAYER = 1;
	const FURNACE = 2;
	const CRAFTING = 3;
	const WORKBENCH = 4;

	private static $default = [];

	private $size;
	private $title;

	public static function get($index){
		return isset(static::$default[$index]) ? static::$default[$index] : null;
	}

	public static function init(){
		if(count(static::$default) > 0){
			return;
		}

		static::$default[static::CHEST] = new InventoryType(27, "Chest");
		static::$default[static::PLAYER] = new InventoryType(36, "Player"); //9 HOTBAR slots, 27 CONTAINER, 4 ARMOR
		static::$default[static::FURNACE] = new InventoryType(3, "Furnace");
		static::$default[static::CRAFTING] = new InventoryType(5, "Crafting"); //4 CRAFTING slots, 1 RESULT
		static::$default[static::WORKBENCH] = new InventoryType(10, "Crafting"); //9 CRAFTING slots, 1 RESULT
	}

	/**
	 * @param int $defaultSize
	 * @param string $defaultTitle
	 */
	private function __construct($defaultSize, $defaultTitle){
		$this->size = $defaultSize;
		$this->title = $defaultTitle;
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
}