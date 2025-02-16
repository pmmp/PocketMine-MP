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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\lang\Translatable;
use function strlen;

/**
 * Info for an item group in the creative inventory menu.
 * All items have a group.
 *
 * If a name and icon is specified, the game will display a collapsible group.
 * Otherwise, the item will be displayed in the chosen category directly.
 */
final class CreativeGroup{
	private function __construct(
		public readonly CreativeCategory $category,
		public readonly Translatable|string $name,
		public readonly ?Item $icon
	){
		//NOOP
	}

	public static function anonymous(CreativeCategory $category) : self{
		return new self($category, "", null);
	}

	public static function named(CreativeCategory $category, Translatable|string $name, Item $icon) : self{
		$nameLength = $name instanceof Translatable ? strlen($name->getText()) : strlen($name);
		if($nameLength === 0){
			throw new \InvalidArgumentException("Creative group name cannot be empty");
		}

		return new self($category, $name, $icon);
	}
}
