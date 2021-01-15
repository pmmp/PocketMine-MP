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

use pocketmine\block\utils\BannerPattern;
use pocketmine\block\utils\DyeColor;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\world\World;

/**
 * @deprecated
 * @see \pocketmine\block\BaseBanner
 */
class Banner extends Spawnable{

	public const TAG_BASE = "Base";
	public const TAG_PATTERNS = "Patterns";
	public const TAG_PATTERN_COLOR = "Color";
	public const TAG_PATTERN_NAME = "Pattern";

	/** @var DyeColor */
	private $baseColor;

	/**
	 * @var BannerPattern[]
	 * @phpstan-var list<BannerPattern>
	 */
	private $patterns = [];

	public function __construct(World $world, Vector3 $pos){
		$this->baseColor = DyeColor::BLACK();
		parent::__construct($world, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$colorIdMap = DyeColorIdMap::getInstance();
		if(($baseColorTag = $nbt->getTag(self::TAG_BASE)) instanceof IntTag){
			$this->baseColor = $colorIdMap->fromInvertedId($baseColorTag->getValue());
		}

		$patterns = $nbt->getListTag(self::TAG_PATTERNS);
		if($patterns !== null){
			/** @var CompoundTag $pattern */
			foreach($patterns as $pattern){
				$this->patterns[] = new BannerPattern($pattern->getString(self::TAG_PATTERN_NAME), $colorIdMap->fromInvertedId($pattern->getInt(self::TAG_PATTERN_COLOR)));
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$colorIdMap = DyeColorIdMap::getInstance();
		$nbt->setInt(self::TAG_BASE, $colorIdMap->toInvertedId($this->baseColor));
		$patterns = new ListTag();
		foreach($this->patterns as $pattern){
			$patterns->push(CompoundTag::create()
				->setString(self::TAG_PATTERN_NAME, $pattern->getId())
				->setInt(self::TAG_PATTERN_COLOR, $colorIdMap->toInvertedId($pattern->getColor()))
			);
		}
		$nbt->setTag(self::TAG_PATTERNS, $patterns);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$colorIdMap = DyeColorIdMap::getInstance();
		$nbt->setInt(self::TAG_BASE, $colorIdMap->toInvertedId($this->baseColor));
		$patterns = new ListTag();
		foreach($this->patterns as $pattern){
			$patterns->push(CompoundTag::create()
				->setString(self::TAG_PATTERN_NAME, $pattern->getId())
				->setInt(self::TAG_PATTERN_COLOR, $colorIdMap->toInvertedId($pattern->getColor()))
			);
		}
		$nbt->setTag(self::TAG_PATTERNS, $patterns);
	}

	/**
	 * Returns the color of the banner base.
	 */
	public function getBaseColor() : DyeColor{
		return $this->baseColor;
	}

	/**
	 * Sets the color of the banner base.
	 */
	public function setBaseColor(DyeColor $color) : void{
		$this->baseColor = $color;
	}

	/**
	 * @return BannerPattern[]
	 * @phpstan-return list<BannerPattern>
	 */
	public function getPatterns() : array{
		return $this->patterns;
	}

	/**
	 * @param BannerPattern[] $patterns
	 * @phpstan-param list<BannerPattern> $patterns
	 */
	public function setPatterns(array $patterns) : void{
		$this->patterns = $patterns;
	}

	public function getDefaultName() : string{
		return "Banner";
	}
}
