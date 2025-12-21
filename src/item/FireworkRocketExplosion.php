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

use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\data\bedrock\FireworkRocketTypeIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Utils;
use function array_key_first;
use function chr;
use function count;
use function ord;
use function strlen;

class FireworkRocketExplosion{

	protected const TAG_TYPE = "FireworkType"; //TAG_Byte
	protected const TAG_COLORS = "FireworkColor"; //TAG_ByteArray
	protected const TAG_FADE_COLORS = "FireworkFade"; //TAG_ByteArray
	protected const TAG_TWINKLE = "FireworkFlicker"; //TAG_Byte
	protected const TAG_TRAIL = "FireworkTrail"; //TAG_Byte

	/**
	 * @throws SavedDataLoadingException
	 */
	public static function fromCompoundTag(CompoundTag $tag) : self{
		$colors = self::decodeColors($tag->getByteArray(self::TAG_COLORS));
		if(count($colors) === 0){
			throw new SavedDataLoadingException("Colors list cannot be empty");
		}

		return new self(
			FireworkRocketTypeIdMap::getInstance()->fromId($tag->getByte(self::TAG_TYPE)) ?? throw new SavedDataLoadingException("Invalid firework type"),
			$colors,
			self::decodeColors($tag->getByteArray(self::TAG_FADE_COLORS)),
			$tag->getByte(self::TAG_TWINKLE, 0) !== 0,
			$tag->getByte(self::TAG_TRAIL, 0) !== 0
		);
	}

	/**
	 * @return DyeColor[]
	 * @phpstan-return list<DyeColor>
	 * @throws SavedDataLoadingException
	 */
	protected static function decodeColors(string $colorsBytes) : array{
		$colors = [];

		$dyeColorIdMap = DyeColorIdMap::getInstance();
		for($i = 0, $len = strlen($colorsBytes); $i < $len; $i++){
			$colorByte = ord($colorsBytes[$i]);
			$color = $dyeColorIdMap->fromInvertedId($colorByte);
			if($color !== null){
				$colors[] = $color;
			}else{
				throw new SavedDataLoadingException("Unknown color $colorByte");
			}
		}

		return $colors;
	}

	/**
	 * @param DyeColor[] $colors
	 */
	protected static function encodeColors(array $colors) : string{
		$colorsBytes = "";

		$dyeColorIdMap = DyeColorIdMap::getInstance();
		foreach($colors as $color){
			$colorsBytes .= chr($dyeColorIdMap->toInvertedId($color));
		}

		return $colorsBytes;
	}

	/**
	 * @param DyeColor[] $colors
	 * @param DyeColor[] $fadeColors
	 * @phpstan-param non-empty-list<DyeColor> $colors
	 * @phpstan-param list<DyeColor> $fadeColors
	 */
	public function __construct(
		protected FireworkRocketType $type,
		protected array $colors,
		protected array $fadeColors = [],
		protected bool $twinkle = false,
		protected bool $trail = false
	){
		if(count($colors) === 0){
			throw new \InvalidArgumentException("Colors list cannot be empty");
		}

		$colorsValidator = function(DyeColor $_) : void{};

		Utils::validateArrayValueType($colors, $colorsValidator);
		Utils::validateArrayValueType($fadeColors, $colorsValidator);
	}

	public function getType() : FireworkRocketType{
		return $this->type;
	}

	/**
	 * Returns the colors of the particles.
	 *
	 * @return DyeColor[]
	 * @phpstan-return non-empty-list<DyeColor>
	 */
	public function getColors() : array{
		return $this->colors;
	}

	/**
	 * Returns the flash color of the explosion.
	 */
	public function getFlashColor() : DyeColor{
		return $this->colors[array_key_first($this->colors)];
	}

	/**
	 * Returns the mixure of colors from {@link FireworkRocketExplosion::getColors()})
	 */
	public function getColorMix() : Color{
		/** @var Color[] $colors */
		$colors = [];
		foreach($this->colors as $dyeColor){
			$colors[] = $dyeColor->getRgbValue();
		}
		return Color::mix(...$colors);
	}

	/**
	 * Returns the colors to which the particles will change their color after a few seconds.
	 * If it is empty, there will be no color change in the particles.
	 *
	 * @return DyeColor[]
	 * @phpstan-return list<DyeColor>
	 */
	public function getFadeColors() : array{
		return $this->fadeColors;
	}

	/**
	 * Returns whether the explosion has a flickering effect.
	 */
	public function willTwinkle() : bool{
		return $this->twinkle;
	}

	/**
	 * Returns whether the particles have a trail effect.
	 */
	public function getTrail() : bool{
		return $this->trail;
	}

	public function toCompoundTag() : CompoundTag{
		return CompoundTag::create()
			->setByte(self::TAG_TYPE, FireworkRocketTypeIdMap::getInstance()->toId($this->type))
			->setByteArray(self::TAG_COLORS, self::encodeColors($this->colors))
			->setByteArray(self::TAG_FADE_COLORS, self::encodeColors($this->fadeColors))
			->setByte(self::TAG_TWINKLE, $this->twinkle ? 1 : 0)
			->setByte(self::TAG_TRAIL, $this->trail ? 1 : 0);
	}
}
