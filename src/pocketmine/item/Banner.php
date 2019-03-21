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

use Ds\Deque;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\utils\BannerPattern;
use pocketmine\block\utils\DyeColor;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\tile\Banner as TileBanner;

class Banner extends Item{
	public const TAG_PATTERNS = TileBanner::TAG_PATTERNS;
	public const TAG_PATTERN_COLOR = TileBanner::TAG_PATTERN_COLOR;
	public const TAG_PATTERN_NAME = TileBanner::TAG_PATTERN_NAME;

	/** @var DyeColor */
	private $color;

	public function __construct(int $variant, string $name, DyeColor $color){
		parent::__construct(self::BANNER, $variant, $name);
		$this->color = $color;
	}

	/**
	 * @return DyeColor
	 */
	public function getColor() : DyeColor{
		return $this->color;
	}

	public function getBlock() : Block{
		return BlockFactory::get(Block::STANDING_BANNER);
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * @return Deque|BannerPattern[]
	 */
	public function getPatterns() : Deque{
		$result = new Deque();
		$tag = $this->getNamedTag()->getListTag(self::TAG_PATTERNS);
		if($tag !== null){
			/** @var CompoundTag $t */
			foreach($tag as $t){
				$result->push(new BannerPattern($t->getString(self::TAG_PATTERN_NAME), DyeColor::fromMagicNumber($t->getInt(self::TAG_PATTERN_COLOR), true)));
			}
		}
		return $result;
	}

	/**
	 * @param Deque|BannerPattern[] $patterns
	 */
	public function setPatterns(Deque $patterns) : void{
		$tag = new ListTag();
		/** @var BannerPattern $pattern */
		foreach($patterns as $pattern){
			$tag->push(CompoundTag::create()
				->setString(self::TAG_PATTERN_NAME, $pattern->getId())
				->setInt(self::TAG_PATTERN_COLOR, $pattern->getColor()->getInvertedMagicNumber())
			);
		}
		$this->getNamedTag()->setTag(self::TAG_PATTERNS, $tag);
	}

	public function getFuelTime() : int{
		return 300;
	}
}
