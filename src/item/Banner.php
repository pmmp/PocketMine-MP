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
use pocketmine\block\tile\Banner as TileBanner;
use pocketmine\block\utils\BannerPatternLayer;
use pocketmine\block\utils\DyeColor;
use pocketmine\data\bedrock\BannerPatternTypeIdMap;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function count;

class Banner extends ItemBlockWallOrFloor{
	public const TAG_PATTERNS = TileBanner::TAG_PATTERNS;
	public const TAG_PATTERN_COLOR = TileBanner::TAG_PATTERN_COLOR;
	public const TAG_PATTERN_NAME = TileBanner::TAG_PATTERN_NAME;

	private DyeColor $color;

	/**
	 * @var BannerPatternLayer[]
	 * @phpstan-var list<BannerPatternLayer>
	 */
	private array $patterns = [];

	public function __construct(ItemIdentifier $identifier, Block $floorVariant, Block $wallVariant){
		parent::__construct($identifier, $floorVariant, $wallVariant);
		$this->color = DyeColor::BLACK();
	}

	public function getColor() : DyeColor{
		return $this->color;
	}

	/** @return $this */
	public function setColor(DyeColor $color) : self{
		$this->color = $color;
		return $this;
	}

	public function getMeta() : int{
		return DyeColorIdMap::getInstance()->toInvertedId($this->color);
	}

	/**
	 * @return BannerPatternLayer[]
	 * @phpstan-return list<BannerPatternLayer>
	 */
	public function getPatterns() : array{
		return $this->patterns;
	}

	/**
	 * @param BannerPatternLayer[] $patterns
	 *
	 * @phpstan-param list<BannerPatternLayer> $patterns
	 *
	 * @return $this
	 */
	public function setPatterns(array $patterns) : self{
		$this->patterns = $patterns;
		return $this;
	}

	public function getFuelTime() : int{
		return 300;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);

		$this->patterns = [];

		$colorIdMap = DyeColorIdMap::getInstance();
		$patternIdMap = BannerPatternTypeIdMap::getInstance();
		$patterns = $tag->getListTag(self::TAG_PATTERNS);
		if($patterns !== null && $patterns->getTagType() === NBT::TAG_Compound){
			/** @var CompoundTag $t */
			foreach($patterns as $t){
				$patternColor = $colorIdMap->fromInvertedId($t->getInt(self::TAG_PATTERN_COLOR)) ?? DyeColor::BLACK(); //TODO: missing pattern colour should be an error
				$patternType = $patternIdMap->fromId($t->getString(self::TAG_PATTERN_NAME));
				if($patternType === null){
					continue; //TODO: this should be an error
				}
				$this->patterns[] = new BannerPatternLayer($patternType, $patternColor);
			}
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);

		if(count($this->patterns) > 0){
			$patterns = new ListTag();
			$colorIdMap = DyeColorIdMap::getInstance();
			$patternIdMap = BannerPatternTypeIdMap::getInstance();
			foreach($this->patterns as $pattern){
				$patterns->push(CompoundTag::create()
					->setString(self::TAG_PATTERN_NAME, $patternIdMap->toId($pattern->getType()))
					->setInt(self::TAG_PATTERN_COLOR, $colorIdMap->toInvertedId($pattern->getColor()))
				);
			}

			$tag->setTag(self::TAG_PATTERNS, $patterns);
		}else{
			$tag->removeTag(self::TAG_PATTERNS);
		}
	}
}
