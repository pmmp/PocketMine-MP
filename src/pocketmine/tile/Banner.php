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

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\Player;

class Banner extends Spawnable implements Nameable{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
		createAdditionalNBT as createNameNBT;
	}

	public const TAG_BASE = "Base";
	public const TAG_PATTERNS = "Patterns";
	public const TAG_PATTERN_COLOR = "Color";
	public const TAG_PATTERN_NAME = "Pattern";

	public const PATTERN_BOTTOM_STRIPE = "bs";
	public const PATTERN_TOP_STRIPE = "ts";
	public const PATTERN_LEFT_STRIPE = "ls";
	public const PATTERN_RIGHT_STRIPE = "rs";
	public const PATTERN_CENTER_STRIPE = "cs";
	public const PATTERN_MIDDLE_STRIPE = "ms";
	public const PATTERN_DOWN_RIGHT_STRIPE = "drs";
	public const PATTERN_DOWN_LEFT_STRIPE = "dls";
	public const PATTERN_SMALL_STRIPES = "ss";
	public const PATTERN_DIAGONAL_CROSS = "cr";
	public const PATTERN_SQUARE_CROSS = "sc";
	public const PATTERN_LEFT_OF_DIAGONAL = "ld";
	public const PATTERN_RIGHT_OF_UPSIDE_DOWN_DIAGONAL = "rud";
	public const PATTERN_LEFT_OF_UPSIDE_DOWN_DIAGONAL = "lud";
	public const PATTERN_RIGHT_OF_DIAGONAL = "rd";
	public const PATTERN_VERTICAL_HALF_LEFT = "vh";
	public const PATTERN_VERTICAL_HALF_RIGHT = "vhr";
	public const PATTERN_HORIZONTAL_HALF_TOP = "hh";
	public const PATTERN_HORIZONTAL_HALF_BOTTOM = "hhb";
	public const PATTERN_BOTTOM_LEFT_CORNER = "bl";
	public const PATTERN_BOTTOM_RIGHT_CORNER = "br";
	public const PATTERN_TOP_LEFT_CORNER = "tl";
	public const PATTERN_TOP_RIGHT_CORNER = "tr";
	public const PATTERN_BOTTOM_TRIANGLE = "bt";
	public const PATTERN_TOP_TRIANGLE = "tt";
	public const PATTERN_BOTTOM_TRIANGLE_SAWTOOTH = "bts";
	public const PATTERN_TOP_TRIANGLE_SAWTOOTH = "tts";
	public const PATTERN_MIDDLE_CIRCLE = "mc";
	public const PATTERN_MIDDLE_RHOMBUS = "mr";
	public const PATTERN_BORDER = "bo";
	public const PATTERN_CURLY_BORDER = "cbo";
	public const PATTERN_BRICK = "bri";
	public const PATTERN_GRADIENT = "gra";
	public const PATTERN_GRADIENT_UPSIDE_DOWN = "gru";
	public const PATTERN_CREEPER = "cre";
	public const PATTERN_SKULL = "sku";
	public const PATTERN_FLOWER = "flo";
	public const PATTERN_MOJANG = "moj";

	public const COLOR_BLACK = 0;
	public const COLOR_RED = 1;
	public const COLOR_GREEN = 2;
	public const COLOR_BROWN = 3;
	public const COLOR_BLUE = 4;
	public const COLOR_PURPLE = 5;
	public const COLOR_CYAN = 6;
	public const COLOR_LIGHT_GRAY = 7;
	public const COLOR_GRAY = 8;
	public const COLOR_PINK = 9;
	public const COLOR_LIME = 10;
	public const COLOR_YELLOW = 11;
	public const COLOR_LIGHT_BLUE = 12;
	public const COLOR_MAGENTA = 13;
	public const COLOR_ORANGE = 14;
	public const COLOR_WHITE = 15;

	/** @var int */
	private $baseColor;
	/**
	 * @var ListTag
	 * TODO: break this down further and remove runtime NBT from here entirely
	 */
	private $patterns;

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->baseColor = $nbt->getInt(self::TAG_BASE, self::COLOR_BLACK, true);
		$this->patterns = $nbt->getListTag(self::TAG_PATTERNS) ?? new ListTag(self::TAG_PATTERNS);
		$this->loadName($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_BASE, $this->baseColor);
		$nbt->setTag($this->patterns);
		$this->saveName($nbt);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_BASE, $this->baseColor);
		$nbt->setTag($this->patterns);
		$this->addNameSpawnData($nbt);
	}

	/**
	 * Returns the color of the banner base.
	 *
	 * @return int
	 */
	public function getBaseColor() : int{
		return $this->baseColor;
	}

	/**
	 * Sets the color of the banner base.
	 *
	 * @param int $color
	 */
	public function setBaseColor(int $color) : void{
		$this->baseColor = $color;
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
		$this->patterns->push(new CompoundTag("", [
			new IntTag(self::TAG_PATTERN_COLOR, $color & 0x0f),
			new StringTag(self::TAG_PATTERN_NAME, $pattern)
		]));

		$this->onChanged();
		return $this->patterns->count() - 1; //Last offset in the list
	}

	/**
	 * Returns whether a pattern with the given ID exists on the banner or not.
	 *
	 * @param int $patternId
	 *
	 * @return bool
	 */
	public function patternExists(int $patternId) : bool{
		return $this->patterns->isset($patternId);
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

		$patternTag = $this->patterns->get($patternId);
		assert($patternTag instanceof CompoundTag);

		return [
			self::TAG_PATTERN_COLOR => $patternTag->getInt(self::TAG_PATTERN_COLOR),
			self::TAG_PATTERN_NAME => $patternTag->getString(self::TAG_PATTERN_NAME)
		];
	}

	/**
	 * Changes the pattern of a previously existing pattern.
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

		$this->patterns->set($patternId, new CompoundTag("", [
			new IntTag(self::TAG_PATTERN_COLOR, $color & 0x0f),
			new StringTag(self::TAG_PATTERN_NAME, $pattern)
		]));

		$this->onChanged();
		return true;
	}

	/**
	 * Deletes a pattern from the banner with the given ID.
	 *
	 * @param int $patternId
	 *
	 * @return bool indicating whether the pattern existed or not.
	 */
	public function deletePattern(int $patternId) : bool{
		if(!$this->patternExists($patternId)){
			return false;
		}

		$this->patterns->remove($patternId);

		$this->onChanged();
		return true;
	}

	/**
	 * Deletes the top most pattern of the banner.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteTopPattern() : bool{
		return $this->deletePattern($this->getPatternCount() - 1);
	}

	/**
	 * Deletes the bottom pattern of the banner.
	 *
	 * @return bool indicating whether the banner was empty or not.
	 */
	public function deleteBottomPattern() : bool{
		return $this->deletePattern(0);
	}

	/**
	 * Returns the total count of patterns on this banner.
	 *
	 * @return int
	 */
	public function getPatternCount() : int{
		return $this->patterns->count();
	}

	/**
	 * @return ListTag
	 */
	public function getPatterns() : ListTag{
		return $this->patterns;
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->setInt(self::TAG_BASE, $item !== null ? $item->getDamage() & 0x0f : 0);

		if($item !== null){
			if($item->getNamedTag()->hasTag(self::TAG_PATTERNS, ListTag::class)){
				$nbt->setTag($item->getNamedTag()->getListTag(self::TAG_PATTERNS));
			}

			self::createNameNBT($nbt, $pos, $face, $item, $player);
		}
	}

	public function getDefaultName() : string{
		return "Banner";
	}
}