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

class ArmorTrimMaterial{

	public const AMETHYST = "amethyst";
	public const COPPER = "copper";
	public const DIAMOND = "diamond";
	public const EMERALD = "emerald";
	public const GOLD = "gold";
	public const IRON = "iron";
	public const LAPIS = "lapis";
	public const NETHERITE = "netherite";
	public const QUARTZ = "quartz";
	public const REDSTONE = "redstone";

	public function __construct(
		private string $identifier,
		private string $color,
		private string $itemName,
		private int $typeId
	){}

	public function getIdentifier() : string{
		return $this->identifier;
	}

	public function getColor() : string{
		return $this->color;
	}

	public function getItemName() : string{
		return $this->itemName;
	}

	public function getTypeId() : int{
		return $this->typeId;
	}
}
