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

namespace pocketmine\block\tile;

use Ds\Deque;
use pocketmine\block\utils\BannerPattern;
use pocketmine\block\utils\DyeColor;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\world\World;

/**
 * @deprecated
 * @see \pocketmine\block\Banner
 */
class Banner extends Spawnable{

	public const TAG_BASE = "Base";
	public const TAG_PATTERNS = "Patterns";
	public const TAG_PATTERN_COLOR = "Color";
	public const TAG_PATTERN_NAME = "Pattern";

	/** @var DyeColor */
	private $baseColor;

	/** @var BannerPattern[]|Deque */
	private $patterns;

	public function __construct(World $world, Vector3 $pos){
		$this->baseColor = DyeColor::BLACK();
		$this->patterns = new Deque();
		parent::__construct($world, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if($nbt->hasTag(self::TAG_BASE, IntTag::class)){
			$this->baseColor = DyeColor::fromMagicNumber($nbt->getInt(self::TAG_BASE), true);
		}

		$patterns = $nbt->getListTag(self::TAG_PATTERNS);
		if($patterns !== null){
			/** @var CompoundTag $pattern */
			foreach($patterns as $pattern){
				$this->patterns[] = new BannerPattern($pattern->getString(self::TAG_PATTERN_NAME), DyeColor::fromMagicNumber($pattern->getInt(self::TAG_PATTERN_COLOR), true));
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_BASE, $this->baseColor->getInvertedMagicNumber());
		$patterns = new ListTag();
		foreach($this->patterns as $pattern){
			$patterns->push(CompoundTag::create()
				->setString(self::TAG_PATTERN_NAME, $pattern->getId())
				->setInt(self::TAG_PATTERN_COLOR, $pattern->getColor()->getInvertedMagicNumber())
			);
		}
		$nbt->setTag(self::TAG_PATTERNS, $patterns);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_BASE, $this->baseColor->getInvertedMagicNumber());
		$patterns = new ListTag();
		foreach($this->patterns as $pattern){
			$patterns->push(CompoundTag::create()
				->setString(self::TAG_PATTERN_NAME, $pattern->getId())
				->setInt(self::TAG_PATTERN_COLOR, $pattern->getColor()->getInvertedMagicNumber())
			);
		}
		$nbt->setTag(self::TAG_PATTERNS, $patterns);
	}

	/**
	 * Returns the color of the banner base.
	 *
	 * @return DyeColor
	 */
	public function getBaseColor() : DyeColor{
		return $this->baseColor;
	}

	/**
	 * Sets the color of the banner base.
	 *
	 * @param DyeColor $color
	 */
	public function setBaseColor(DyeColor $color) : void{
		$this->baseColor = $color;
	}

	/**
	 * @return BannerPattern[]|Deque
	 */
	public function getPatterns() : Deque{
		return $this->patterns;
	}

	/**
	 * @param BannerPattern[]|Deque $patterns
	 */
	public function setPatterns(Deque $patterns) : void{
		$this->patterns = $patterns;
	}

	public function getDefaultName() : string{
		return "Banner";
	}
}
