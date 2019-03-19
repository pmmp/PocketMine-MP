<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */
declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\utils\Color;

class MapDecoration{

	public const TYPE_PLAYER = 0;
	public const TYPE_FRAME = 1;
	public const TYPE_RED_MARKER = 2;
	public const TYPE_BLUE_MARKER = 3;
	public const TYPE_TARGET_X = 4;
	public const TYPE_TARGET_POINT = 5;
	public const TYPE_PLAYER_OFF_MAP = 6;
	public const TYPE_PLAYER_OFF_LIMITS = 7;
	public const TYPE_MANSION = 8;
	public const TYPE_MONUMENT = 9;
	// more ???

	/** @var int */
	public $icon;
	/** @var int */
	public $rot;
	/** @var int */
	public $xOffset;
	/** @var int */
	public $yOffset;
	/** @var string */
	public $label;
	/** @var Color */
	public $color;
}