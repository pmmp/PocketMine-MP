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

namespace pocketmine\data\bedrock;

use pocketmine\block\utils\BannerPatternType;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;

final class BannerPatternTypeIdMap{
	use SingletonTrait;

	/**
	 * @var BannerPatternType[]
	 * @phpstan-var array<string, BannerPatternType>
	 */
	private array $idToEnum = [];
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $enumToId = [];

	public function __construct(){
		$this->register("bo", BannerPatternType::BORDER());
		$this->register("bri", BannerPatternType::BRICKS());
		$this->register("mc", BannerPatternType::CIRCLE());
		$this->register("cre", BannerPatternType::CREEPER());
		$this->register("cr", BannerPatternType::CROSS());
		$this->register("cbo", BannerPatternType::CURLY_BORDER());
		$this->register("lud", BannerPatternType::DIAGONAL_LEFT());
		$this->register("rd", BannerPatternType::DIAGONAL_RIGHT());
		$this->register("ld", BannerPatternType::DIAGONAL_UP_LEFT());
		$this->register("rud", BannerPatternType::DIAGONAL_UP_RIGHT());
		$this->register("flo", BannerPatternType::FLOWER());
		$this->register("gra", BannerPatternType::GRADIENT());
		$this->register("gru", BannerPatternType::GRADIENT_UP());
		$this->register("hh", BannerPatternType::HALF_HORIZONTAL());
		$this->register("hhb", BannerPatternType::HALF_HORIZONTAL_BOTTOM());
		$this->register("vh", BannerPatternType::HALF_VERTICAL());
		$this->register("vhr", BannerPatternType::HALF_VERTICAL_RIGHT());
		$this->register("moj", BannerPatternType::MOJANG());
		$this->register("mr", BannerPatternType::RHOMBUS());
		$this->register("sku", BannerPatternType::SKULL());
		$this->register("ss", BannerPatternType::SMALL_STRIPES());
		$this->register("bl", BannerPatternType::SQUARE_BOTTOM_LEFT());
		$this->register("br", BannerPatternType::SQUARE_BOTTOM_RIGHT());
		$this->register("tl", BannerPatternType::SQUARE_TOP_LEFT());
		$this->register("tr", BannerPatternType::SQUARE_TOP_RIGHT());
		$this->register("sc", BannerPatternType::STRAIGHT_CROSS());
		$this->register("bs", BannerPatternType::STRIPE_BOTTOM());
		$this->register("cs", BannerPatternType::STRIPE_CENTER());
		$this->register("dls", BannerPatternType::STRIPE_DOWNLEFT());
		$this->register("drs", BannerPatternType::STRIPE_DOWNRIGHT());
		$this->register("ls", BannerPatternType::STRIPE_LEFT());
		$this->register("ms", BannerPatternType::STRIPE_MIDDLE());
		$this->register("rs", BannerPatternType::STRIPE_RIGHT());
		$this->register("ts", BannerPatternType::STRIPE_TOP());
		$this->register("bt", BannerPatternType::TRIANGLE_BOTTOM());
		$this->register("tt", BannerPatternType::TRIANGLE_TOP());
		$this->register("bts", BannerPatternType::TRIANGLES_BOTTOM());
		$this->register("tts", BannerPatternType::TRIANGLES_TOP());
	}

	public function register(string $stringId, BannerPatternType $type) : void{
		$this->idToEnum[$stringId] = $type;
		$this->enumToId[$type->id()] = $stringId;
	}

	public function fromId(string $id) : ?BannerPatternType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(BannerPatternType $type) : string{
		if(!array_key_exists($type->id(), $this->enumToId)){
			throw new \InvalidArgumentException("Missing mapping for banner pattern type " . $type->name());
		}
		return $this->enumToId[$type->id()];
	}
}
