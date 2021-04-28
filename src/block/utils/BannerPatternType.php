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

namespace pocketmine\block\utils;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static BannerPatternType BORDER()
 * @method static BannerPatternType BRICKS()
 * @method static BannerPatternType CIRCLE()
 * @method static BannerPatternType CREEPER()
 * @method static BannerPatternType CROSS()
 * @method static BannerPatternType CURLY_BORDER()
 * @method static BannerPatternType DIAGONAL_LEFT()
 * @method static BannerPatternType DIAGONAL_RIGHT()
 * @method static BannerPatternType DIAGONAL_UP_LEFT()
 * @method static BannerPatternType DIAGONAL_UP_RIGHT()
 * @method static BannerPatternType FLOWER()
 * @method static BannerPatternType GRADIENT()
 * @method static BannerPatternType GRADIENT_UP()
 * @method static BannerPatternType HALF_HORIZONTAL()
 * @method static BannerPatternType HALF_HORIZONTAL_BOTTOM()
 * @method static BannerPatternType HALF_VERTICAL()
 * @method static BannerPatternType HALF_VERTICAL_RIGHT()
 * @method static BannerPatternType MOJANG()
 * @method static BannerPatternType RHOMBUS()
 * @method static BannerPatternType SKULL()
 * @method static BannerPatternType SMALL_STRIPES()
 * @method static BannerPatternType SQUARE_BOTTOM_LEFT()
 * @method static BannerPatternType SQUARE_BOTTOM_RIGHT()
 * @method static BannerPatternType SQUARE_TOP_LEFT()
 * @method static BannerPatternType SQUARE_TOP_RIGHT()
 * @method static BannerPatternType STRAIGHT_CROSS()
 * @method static BannerPatternType STRIPE_BOTTOM()
 * @method static BannerPatternType STRIPE_CENTER()
 * @method static BannerPatternType STRIPE_DOWNLEFT()
 * @method static BannerPatternType STRIPE_DOWNRIGHT()
 * @method static BannerPatternType STRIPE_LEFT()
 * @method static BannerPatternType STRIPE_MIDDLE()
 * @method static BannerPatternType STRIPE_RIGHT()
 * @method static BannerPatternType STRIPE_TOP()
 * @method static BannerPatternType TRIANGLES_BOTTOM()
 * @method static BannerPatternType TRIANGLES_TOP()
 * @method static BannerPatternType TRIANGLE_BOTTOM()
 * @method static BannerPatternType TRIANGLE_TOP()
 */
final class BannerPatternType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("border", "bo"),
			new self("bricks", "bri"),
			new self("circle", "mc"),
			new self("creeper", "cre"),
			new self("cross", "cr"),
			new self("curly_border", "cbo"),
			new self("diagonal_left", "lud"),
			new self("diagonal_right", "rd"),
			new self("diagonal_up_left", "ld"),
			new self("diagonal_up_right", "rud"),
			new self("flower", "flo"),
			new self("gradient", "gra"),
			new self("gradient_up", "gru"),
			new self("half_horizontal", "hh"),
			new self("half_horizontal_bottom", "hhb"),
			new self("half_vertical", "vh"),
			new self("half_vertical_right", "vhr"),
			new self("mojang", "moj"),
			new self("rhombus", "mr"),
			new self("skull", "sku"),
			new self("small_stripes", "ss"),
			new self("square_bottom_left", "bl"),
			new self("square_bottom_right", "br"),
			new self("square_top_left", "tl"),
			new self("square_top_right", "tr"),
			new self("straight_cross", "sc"),
			new self("stripe_bottom", "bs"),
			new self("stripe_center", "cs"),
			new self("stripe_downleft", "dls"),
			new self("stripe_downright", "drs"),
			new self("stripe_left", "ls"),
			new self("stripe_middle", "ms"),
			new self("stripe_right", "rs"),
			new self("stripe_top", "ts"),
			new self("triangle_bottom", "bt"),
			new self("triangle_top", "tt"),
			new self("triangles_bottom", "bts"),
			new self("triangles_top", "tts")
		);
	}

	private string $patternId;

	private function __construct(string $name, string $patternId){
		$this->Enum___construct($name);
		$this->patternId = $patternId;
	}

	public function getPatternId() : string{ return $this->patternId; }
}
