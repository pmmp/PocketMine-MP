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
use function spl_object_id;

final class DyeColorIdMap{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<DyeColor> */
	use IntSaveIdMapTrait {
		register as registerInt;
	}

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
		foreach(DyeColor::cases() as $case){
			[$colorId, $dyeItemId] = match($case){
				DyeColor::WHITE => [0, ItemTypeNames::WHITE_DYE],
				DyeColor::ORANGE => [1, ItemTypeNames::ORANGE_DYE],
				DyeColor::MAGENTA => [2, ItemTypeNames::MAGENTA_DYE],
				DyeColor::LIGHT_BLUE => [3, ItemTypeNames::LIGHT_BLUE_DYE],
				DyeColor::YELLOW => [4, ItemTypeNames::YELLOW_DYE],
				DyeColor::LIME => [5, ItemTypeNames::LIME_DYE],
				DyeColor::PINK => [6, ItemTypeNames::PINK_DYE],
				DyeColor::GRAY => [7, ItemTypeNames::GRAY_DYE],
				DyeColor::LIGHT_GRAY => [8, ItemTypeNames::LIGHT_GRAY_DYE],
				DyeColor::CYAN => [9, ItemTypeNames::CYAN_DYE],
				DyeColor::PURPLE => [10, ItemTypeNames::PURPLE_DYE],
				DyeColor::BLUE => [11, ItemTypeNames::BLUE_DYE],
				DyeColor::BROWN => [12, ItemTypeNames::BROWN_DYE],
				DyeColor::GREEN => [13, ItemTypeNames::GREEN_DYE],
				DyeColor::RED => [14, ItemTypeNames::RED_DYE],
				DyeColor::BLACK => [15, ItemTypeNames::BLACK_DYE],
			};

			$this->register($colorId, $dyeItemId, $case);
		}
	}

	private function register(int $id, string $itemId, DyeColor $color) : void{
		$this->registerInt($id, $color);
		$this->itemIdToEnum[$itemId] = $color;
		$this->enumToItemId[spl_object_id($color)] = $itemId;
	}

	public function toInvertedId(DyeColor $color) : int{
		return ~$this->toId($color) & 0xf;
	}

	public function toItemId(DyeColor $color) : string{
		return $this->enumToItemId[spl_object_id($color)];
	}

	public function fromInvertedId(int $id) : ?DyeColor{
		return $this->fromId(~$id & 0xf);
	}

	public function fromItemId(string $itemId) : ?DyeColor{
		return $this->itemIdToEnum[$itemId] ?? null;
	}
}
