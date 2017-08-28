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

namespace pocketmine\tile;

use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class Banner extends Spawnable{

	const PATTERN_BOTTOM_STRIPE = "bs";
	const PATTERN_TOP_STRIPE = "ts";
	const PATTERN_LEFT_STRIPE = "ls";
	const PATTERN_RIGHT_STRIPE = "rs";
	const PATTERN_CENTER_STRIPE = "cs";
	const PATTERN_MIDDLE_STRIPE = "ms";
	const PATTERN_DOWN_RIGHT_STRIPE = "drs";
	const PATTERN_DOWN_LEFT_STRIPE = "dls";
	const PATTERN_SMALL_STRIPES = "ss";
	const PATTERN_DIAGONAL_CROSS = "cr";
	const PATTERN_SQUARE_CROSS = "sc";
	const PATTERN_LEFT_OF_DIAGONAL = "ld";
	const PATTERN_RIGHT_OF_UPSIDE_DOWN_DIAGONAL = "rud";
	const PATTERN_LEFT_OF_UPSIDE_DOWN_DIAGONAL = "lud";
	const PATTERN_RIGHT_OF_DIAGONAL = "rd";
	const PATTERN_VERTICAL_HALF_LEFT = "vh";
	const PATTERN_VERTICAL_HALF_RIGHT = "vhr";
	const PATTERN_HORIZONTAL_HALF_TOP = "hh";
	const PATTERN_HORIZONTAL_HALF_BOTTOM = "hhb";
	const PATTERN_BOTTOM_LEFT_CORNER = "bl";
	const PATTERN_BOTTOM_RIGHT_CORNER = "br";
	const PATTERN_TOP_LEFT_CORNER = "tl";
	const PATTERN_TOP_RIGHT_CORNER = "tr";
	const PATTERN_BOTTOM_TRIANGLE = "bt";
	const PATTERN_TOP_TRIANGLE = "tt";
	const PATTERN_BOTTOM_TRIANGLE_SAWTOOTH = "bts";
	const PATTERN_TOP_TRIANGLE_SAWTOOTH = "tts";
	const PATTERN_MIDDLE_CIRCLE = "mc";
	const PATTERN_MIDDLE_RHOMBUS = "mr";
	const PATTERN_BORDER = "bo";
	const PATTERN_CURLY_BORDER = "cbo";
	const PATTERN_BRICK = "bri";
	const PATTERN_GRADIENT = "gra";
	const PATTERN_GRADIENT_UPSIDE_DOWN = "gru";
	const PATTERN_CREEPER = "cre";
	const PATTERN_SKULL = "sku";
	const PATTERN_FLOWER = "flo";
	const PATTERN_MOJANG = "moj";

	private $patternCount = 0;

	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->Base) or !($nbt->Base instanceof IntTag)){
			$nbt->Base = new IntTag("Base", 15);
		}
		if(!isset($nbt->Patterns) or !($nbt->Patterns instanceof ListTag)){
			$nbt->Patterns = new ListTag("Patterns");
		}
		parent::__construct($level, $nbt);
	}

	public function addAdditionalSpawnData(CompoundTag $nbt){
		$nbt->Patterns = $this->namedtag->Patterns;
		$nbt->Base = $this->namedtag->Base;
	}

	/**
	 * Returns the color of the banner base.
	 *
	 * @return int
	 */
	public function getBaseColor() : int{
		return $this->namedtag->Base->getValue();
	}

	/**
	 * Sets the color of the banner base.
	 *
	 * @param int $color
	 */
	public function setBaseColor(int $color){
		$this->namedtag->Base->setValue($color & 0x0f);
		$this->onChanged();
	}

	/**
	 * Applies a new pattern on the banner with the given color.
	 *
	 * @param string $pattern
	 * @param int    $color
	 *
	 * @return int ID of pattern.
	 */
	public function addPattern(string $pattern, int $color) : int{
		$this->namedtag->Patterns->{($id = $this->patternCount++)} = new CompoundTag("", [
			new IntTag("Color", $color & 0x0f),
			new StringTag("Pattern", $pattern)
		]);
		$this->onChanged();
		return $id;
	}

	/**
	 * Changes the pattern of a previously existing pattern.
	 *
	 * @param int    $id
	 * @param string $pattern
	 * @param int    $color
	 *
	 * @return bool indicating success.
	 */
	public function changePattern(int $id, string $pattern, int $color) : bool{
		if(!isset($this->namedtag->Patterns->{$id})){
			return false;
		}
		$this->namedtag->Patterns->{$id}->setValue([
			new IntTag("Color", $color & 0x0f),
			new StringTag("Pattern", $pattern)
		]);
		$this->onChanged();
		return true;
	}

	/**
	 * Deletes a layer of the banner with the given ID.
	 *
	 * @param int $patternLayer
	 *
	 * @return bool indicating whether the pattern existed or not.
	 */
	public function deletePattern(int $patternLayer) : bool{
		if(!isset($this->namedtag->Patterns->{$patternLayer})){
			return false;
		}
		unset($this->namedtag->Patterns->{$patternLayer});

		$this->onChanged();
		return true;
	}

	/**
	 * Deletes the top most pattern of the banner.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteTopPattern() : bool{
		if(empty((array) $this->namedtag->Patterns)){
			return false;
		}
		$index = (int) max(array_keys((array) $this->namedtag->Patterns));
		unset($this->namedtag->Patterns->{$index});

		$this->onChanged();
		return true;
	}

	/**
	 * Deletes the bottom pattern of the banner.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteBottomPattern() : bool{
		if(empty((array) $this->namedtag->Patterns)){
			return false;
		}
		$index = (int) min(array_keys((array) $this->namedtag->Patterns));
		unset($this->namedtag->Patterns->{$index});

		$this->onChanged();
		return true;
	}

	/**
	 * Returns the total count of patterns on this banner.
	 *
	 * @return int
	 */
	public function getPatternCount() : int{
		return count((array) $this->namedtag->Patterns);
	}
}