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

use pocketmine\block\Banner;

/**
 * Contains information about a pattern layer on a banner.
 * @see Banner
 */
class BannerPattern{
	public const BORDER = "bo";
	public const BRICKS = "bri";
	public const CIRCLE = "mc";
	public const CREEPER = "cre";
	public const CROSS = "cr";
	public const CURLY_BORDER = "cbo";
	public const DIAGONAL_LEFT = "lud";
	public const DIAGONAL_RIGHT = "rd";
	public const DIAGONAL_UP_LEFT = "ld";
	public const DIAGONAL_UP_RIGHT = "rud";
	public const FLOWER = "flo";
	public const GRADIENT = "gra";
	public const GRADIENT_UP = "gru";
	public const HALF_HORIZONTAL = "hh";
	public const HALF_HORIZONTAL_BOTTOM = "hhb";
	public const HALF_VERTICAL = "vh";
	public const HALF_VERTICAL_RIGHT = "vhr";
	public const MOJANG = "moj";
	public const RHOMBUS = "mr";
	public const SKULL = "sku";
	public const SMALL_STRIPES = "ss";
	public const SQUARE_BOTTOM_LEFT = "bl";
	public const SQUARE_BOTTOM_RIGHT = "br";
	public const SQUARE_TOP_LEFT = "tl";
	public const SQUARE_TOP_RIGHT = "tr";
	public const STRAIGHT_CROSS = "sc";
	public const STRIPE_BOTTOM = "bs";
	public const STRIPE_CENTER = "cs";
	public const STRIPE_DOWNLEFT = "dls";
	public const STRIPE_DOWNRIGHT = "drs";
	public const STRIPE_LEFT = "ls";
	public const STRIPE_MIDDLE = "ms";
	public const STRIPE_RIGHT = "rs";
	public const STRIPE_TOP = "ts";
	public const TRIANGLE_BOTTOM = "bt";
	public const TRIANGLE_TOP = "tt";
	public const TRIANGLES_BOTTOM = "bts";
	public const TRIANGLES_TOP = "tts";

	/** @var string */
	private $id;
	/** @var DyeColor */
	private $color;

	public function __construct(string $id, DyeColor $color){
		$this->id = $id;
		$this->color = $color;
	}

	/**
	 * @return string
	 */
	public function getId() : string{
		return $this->id;
	}

	/**
	 * @return DyeColor
	 */
	public function getColor() : DyeColor{
		return $this->color;
	}
}
