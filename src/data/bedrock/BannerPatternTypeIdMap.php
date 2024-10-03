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
use function spl_object_id;

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
		foreach(BannerPatternType::cases() as $case){
			$this->register(match($case){
				BannerPatternType::BORDER => "bo",
				BannerPatternType::BRICKS => "bri",
				BannerPatternType::CIRCLE => "mc",
				BannerPatternType::CREEPER => "cre",
				BannerPatternType::CROSS => "cr",
				BannerPatternType::CURLY_BORDER => "cbo",
				BannerPatternType::DIAGONAL_LEFT => "lud",
				BannerPatternType::DIAGONAL_RIGHT => "rd",
				BannerPatternType::DIAGONAL_UP_LEFT => "ld",
				BannerPatternType::DIAGONAL_UP_RIGHT => "rud",
				BannerPatternType::FLOWER => "flo",
				BannerPatternType::FLOW => "flw",
				BannerPatternType::GLOBE => "glb",
				BannerPatternType::GRADIENT => "gra",
				BannerPatternType::GRADIENT_UP => "gru",
				BannerPatternType::GUSTER => "gus",
				BannerPatternType::HALF_HORIZONTAL => "hh",
				BannerPatternType::HALF_HORIZONTAL_BOTTOM => "hhb",
				BannerPatternType::HALF_VERTICAL => "vh",
				BannerPatternType::HALF_VERTICAL_RIGHT => "vhr",
				BannerPatternType::MOJANG => "moj",
				BannerPatternType::PIGLIN => "pig",
				BannerPatternType::RHOMBUS => "mr",
				BannerPatternType::SKULL => "sku",
				BannerPatternType::SMALL_STRIPES => "ss",
				BannerPatternType::SQUARE_BOTTOM_LEFT => "bl",
				BannerPatternType::SQUARE_BOTTOM_RIGHT => "br",
				BannerPatternType::SQUARE_TOP_LEFT => "tl",
				BannerPatternType::SQUARE_TOP_RIGHT => "tr",
				BannerPatternType::STRAIGHT_CROSS => "sc",
				BannerPatternType::STRIPE_BOTTOM => "bs",
				BannerPatternType::STRIPE_CENTER => "cs",
				BannerPatternType::STRIPE_DOWNLEFT => "dls",
				BannerPatternType::STRIPE_DOWNRIGHT => "drs",
				BannerPatternType::STRIPE_LEFT => "ls",
				BannerPatternType::STRIPE_MIDDLE => "ms",
				BannerPatternType::STRIPE_RIGHT => "rs",
				BannerPatternType::STRIPE_TOP => "ts",
				BannerPatternType::TRIANGLE_BOTTOM => "bt",
				BannerPatternType::TRIANGLE_TOP => "tt",
				BannerPatternType::TRIANGLES_BOTTOM => "bts",
				BannerPatternType::TRIANGLES_TOP => "tts",
			}, $case);
		}
	}

	public function register(string $stringId, BannerPatternType $type) : void{
		$this->idToEnum[$stringId] = $type;
		$this->enumToId[spl_object_id($type)] = $stringId;
	}

	public function fromId(string $id) : ?BannerPatternType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(BannerPatternType $type) : string{
		$k = spl_object_id($type);
		if(!array_key_exists($k, $this->enumToId)){
			throw new \InvalidArgumentException("Missing mapping for banner pattern type " . $type->name);
		}
		return $this->enumToId[$k];
	}
}
