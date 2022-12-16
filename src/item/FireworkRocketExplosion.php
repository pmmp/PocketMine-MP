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
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\data\bedrock\FireworkRocketTypeIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Binary;

class FireworkRocketExplosion{

	protected const TAG_TYPE = "FireworkType"; //TAG_Byte
	protected const TAG_COLOR = "FireworkColor"; //TAG_ByteArray
	protected const TAG_FADE = "FireworkFade"; //TAG_ByteArray
	protected const TAG_TWINKLE = "FireworkFlicker"; //TAG_Byte
	protected const TAG_TRAIL = "FireworkTrail"; //TAG_Byte

	public static function fromCompoundTag(CompoundTag $tag) : self{
		$dyeColorIdMap = DyeColorIdMap::getInstance();

		$fade = null;
		if (($fadeTag = $tag->getByteArray(self::TAG_FADE)) !== "") {
			$fade = $dyeColorIdMap->fromInvertedId(Binary::readByte($fadeTag)) ?? throw new SavedDataLoadingException("Invalid fade color");
		}

		return new self(
			FireworkRocketTypeIdMap::getInstance()->fromId($tag->getByte(self::TAG_TYPE)) ?? throw new SavedDataLoadingException("Invalid firework type"),
			$dyeColorIdMap->fromInvertedId(Binary::readByte($tag->getByteArray(self::TAG_COLOR))) ?? throw new SavedDataLoadingException("Invalid dye color"),
			$fade,
			$tag->getByte(self::TAG_TWINKLE, 0) !== 0,
			$tag->getByte(self::TAG_TRAIL, 0) !== 0
		);
	}

	public function __construct(
		protected FireworkRocketType $type,
		protected DyeColor $color,
		protected ?DyeColor $fade,
		protected bool $twinkle,
		protected bool $trail
	){}

	public function getType() : FireworkRocketType{
		return $this->type;
	}

	public function setType(FireworkRocketType $type) : void{
		$this->type = $type;
	}

	public function getColor() : DyeColor{
		return $this->color;
	}

	public function setColor(DyeColor $color) : void{
		$this->color = $color;
	}

	public function getFade() : DyeColor{
		return $this->fade;
	}

	public function setFade(DyeColor $fade) : void{
		$this->fade = $fade;
	}

	public function willTwinkle() : bool{
		return $this->twinkle;
	}

	public function setTwinkle(bool $twinkle) : void{
		$this->twinkle = $twinkle;
	}

	public function getTrail() : bool{
		return $this->trail;
	}

	public function setTrail(bool $trail) : void{
		$this->trail = $trail;
	}

	public function toCompoundTag() : CompoundTag{
		$dyeColorIdMap = DyeColorIdMap::getInstance();

		return CompoundTag::create()
			->setByte(self::TAG_TYPE, FireworkRocketTypeIdMap::getInstance()->toId($this->type))
			->setByteArray(self::TAG_COLOR, Binary::writeByte($dyeColorIdMap->toInvertedId($this->color)))
			->setByteArray(self::TAG_FADE, $this->fade === null ? "" : Binary::writeByte($dyeColorIdMap->toInvertedId($this->fade)))
			->setByte(self::TAG_TWINKLE, $this->twinkle ? 1 : 0)
			->setByte(self::TAG_TRAIL, $this->trail ? 1 : 0)
		;
	}
}
