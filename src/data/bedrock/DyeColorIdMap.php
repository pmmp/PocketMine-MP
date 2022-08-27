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

use pocketmine\block\utils\DyeColor;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\utils\SingletonTrait;

final class DyeColorIdMap{
	use SingletonTrait;

	/**
	 * @var DyeColor[]
	 * @phpstan-var array<int, DyeColor>
	 */
	private array $idToEnum = [];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	/**
	 * @var DyeColor[]
	 * @phpstan-var array<string, DyeColor>
	 */
	private array $itemIdToEnum = [];

	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $enumToItemId = [];

	private function __construct(){
		$this->register(0, ItemTypeNames::WHITE_DYE, DyeColor::WHITE());
		$this->register(1, ItemTypeNames::ORANGE_DYE, DyeColor::ORANGE());
		$this->register(2, ItemTypeNames::MAGENTA_DYE, DyeColor::MAGENTA());
		$this->register(3, ItemTypeNames::LIGHT_BLUE_DYE, DyeColor::LIGHT_BLUE());
		$this->register(4, ItemTypeNames::YELLOW_DYE, DyeColor::YELLOW());
		$this->register(5, ItemTypeNames::LIME_DYE, DyeColor::LIME());
		$this->register(6, ItemTypeNames::PINK_DYE, DyeColor::PINK());
		$this->register(7, ItemTypeNames::GRAY_DYE, DyeColor::GRAY());
		$this->register(8, ItemTypeNames::LIGHT_GRAY_DYE, DyeColor::LIGHT_GRAY());
		$this->register(9, ItemTypeNames::CYAN_DYE, DyeColor::CYAN());
		$this->register(10, ItemTypeNames::PURPLE_DYE, DyeColor::PURPLE());
		$this->register(11, ItemTypeNames::BLUE_DYE, DyeColor::BLUE());
		$this->register(12, ItemTypeNames::BROWN_DYE, DyeColor::BROWN());
		$this->register(13, ItemTypeNames::GREEN_DYE, DyeColor::GREEN());
		$this->register(14, ItemTypeNames::RED_DYE, DyeColor::RED());
		$this->register(15, ItemTypeNames::BLACK_DYE, DyeColor::BLACK());
	}

	private function register(int $id, string $itemId, DyeColor $color) : void{
		$this->idToEnum[$id] = $color;
		$this->enumToId[$color->id()] = $id;
		$this->itemIdToEnum[$itemId] = $color;
		$this->enumToItemId[$color->id()] = $itemId;
	}

	public function toId(DyeColor $color) : int{
		return $this->enumToId[$color->id()]; //TODO: is it possible for this to be missing?
	}

	public function toInvertedId(DyeColor $color) : int{
		return ~$this->toId($color) & 0xf;
	}

	public function toItemId(DyeColor $color) : string{
		return $this->enumToItemId[$color->id()];
	}

	public function fromId(int $id) : ?DyeColor{
		return $this->idToEnum[$id] ?? null;
	}

	public function fromInvertedId(int $id) : ?DyeColor{
		return $this->fromId(~$id & 0xf);
	}

	public function fromItemId(string $itemId) : ?DyeColor{
		return $this->itemIdToEnum[$itemId] ?? null;
	}
}
