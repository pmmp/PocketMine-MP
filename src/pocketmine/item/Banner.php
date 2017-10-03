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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class Banner extends Item{

	public function __construct(int $meta = 0) {
		$this->block = BlockFactory::get(Block::STANDING_BANNER);
		parent::__construct(self::BANNER, $meta, "Banner");
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * Returns the color of the banner base.
	 *
	 * @return int
	 */
	public function getBaseColor() : int{
		return $this->getNamedTag()->Base->getValue();
	}

	/**
	 * Sets the color of the banner base.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @param int $color
	 */
	public function setBaseColor(int $color) : void{
		$namedTag = $this->getNamedTag();
		$namedTag->Base->setValue($color & 0x0f);
		$this->setNamedTag($namedTag);
	}

	/**
	 * Applies a new pattern on the banner with the given color.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @param string $pattern
	 * @param int    $color
	 *
	 * @return int ID of pattern.
	 */
	public function addPattern(string $pattern, int $color) : int{
		$patternId = 0;
		if($this->getPatternCount() !== 0) {
			$patternId = max($this->getPatternIds()) + 1;
		}

		$namedTag = $this->getNamedTag();
		$namedTag->Patterns->{$patternId} = new CompoundTag("", [
			new IntTag("Color", $color & 0x0f),
			new StringTag("Pattern", $pattern)
		]);

		$this->setNamedTag($namedTag);
		return $patternId;
	}

	/**
	 * Returns whether a pattern with the given ID exists on the banner or not.
	 *
	 * @param int $patternId
	 *
	 * @return bool
	 */
	public function patternExists(int $patternId) : bool{
		$this->correctNBT();
		return isset($this->getNamedTag()->Patterns->{$patternId});
	}

	/**
	 * Returns the data of a pattern with the given ID.
	 *
	 * @param int $patternId
	 *
	 * @return array
	 */
	public function getPatternData(int $patternId) : array{
		if(!$this->patternExists($patternId)){
			return [];
		}

		return [
			"Color" => $this->getNamedTag()->Patterns->{$patternId}->Color->getValue(),
			"Pattern" => $this->getNamedTag()->Patterns->{$patternId}->Pattern->getValue()
		];
	}

	/**
	 * Changes the pattern of a previously existing pattern.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @param int    $patternId
	 * @param string $pattern
	 * @param int    $color
	 *
	 * @return bool indicating success.
	 */
	public function changePattern(int $patternId, string $pattern, int $color) : bool{
		if(!$this->patternExists($patternId)){
			return false;
		}

		$namedTag = $this->getNamedTag();
		$namedTag->Patterns->{$patternId}->setValue([
			new IntTag("Color", $color & 0x0f),
			new StringTag("Pattern", $pattern)
		]);

		$this->setNamedTag($namedTag);
		return true;
	}

	/**
	 * Deletes a pattern from the banner with the given ID.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @param int $patternId
	 *
	 * @return bool indicating whether the pattern existed or not.
	 */
	public function deletePattern(int $patternId) : bool{
		if(!$this->patternExists($patternId)){
			return false;
		}

		$namedTag = $this->getNamedTag();
		unset($namedTag->Patterns->{$patternId});
		$this->setNamedTag($namedTag);

		return true;
	}

	/**
	 * Deletes the top most pattern of the banner.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteTopPattern() : bool{
		$keys = $this->getPatternIds();
		if(empty($keys)){
			return false;
		}

		$index = max($keys);
		$namedTag = $this->getNamedTag();
		unset($namedTag->Patterns->{$index});
		$this->setNamedTag($namedTag);

		return true;
	}

	/**
	 * Returns an array containing all pattern IDs
	 *
	 * @return array
	 */
	public function getPatternIds() : array{
		$this->correctNBT();

		$keys = array_keys((array) $this->getNamedTag()->Patterns);
		return array_filter($keys, function($key){
			return is_numeric($key);
		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Deletes the bottom pattern of the banner.
	 * Banner items have to be resent to see the changes in the inventory.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteBottomPattern() : bool{
		$keys = $this->getPatternIds();
		if(empty($keys)){
			return false;
		}

		$namedTag = $this->getNamedTag();
		$index = min($keys);
		unset($namedTag->Patterns->{$index});
		$this->setNamedTag($namedTag);

		return true;
	}

	/**
	 * Returns the total count of patterns on this banner.
	 *
	 * @return int
	 */
	public function getPatternCount() : int{
		return count($this->getPatternIds());
	}

	public function correctNBT() : void{
		$tag = $this->getNamedTag() ?? new CompoundTag();
		if(!isset($tag->Base) or !($tag->Base instanceof IntTag)) {
			$tag->Base = new IntTag("Base", $this->meta);
		}

		if(!isset($tag->Patterns) or !($tag->Patterns instanceof ListTag)) {
			$tag->Patterns = new ListTag("Patterns");
		}
		$this->setNamedTag($tag);
	}
}