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

use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Color;

class MapDecoration{

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

	public function toNBT(string $name) : ListTag{
		return new ListTag($name, [
			new IntTag("icon", $this->icon), new IntTag("rot", $this->rot), new IntTag("xOffset", $this->xOffset),
			new IntTag("yOffset", $this->yOffset), new StringTag("label", $this->label),
			new IntTag("color", $this->color->toABGR())
		]);
	}

	public static function fromNBT(ListTag $nbt) : MapDecoration{
		$d = new MapDecoration();
		foreach($nbt->getValue() as $item){
			$d->{$item->getName()} = $item->getName() === "color" ? Color::fromABGR($item->getValue()) : $item->getValue();
		}

		return $d;
	}

}