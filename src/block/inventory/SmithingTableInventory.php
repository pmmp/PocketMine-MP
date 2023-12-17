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

namespace pocketmine\block\inventory;

use InvalidArgumentException;
use pocketmine\crafting\SmithingRecipe;
use pocketmine\crafting\SmithingTransformRecipe;
use pocketmine\crafting\SmithingTrimRecipe;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTrim;
use pocketmine\item\ArmorTrimMaterial;
use pocketmine\item\ArmorTrimPattern;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\cache\TrimDataHelper;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\Server;
use pocketmine\world\Position;
use function var_dump;

final class SmithingTableInventory extends SimpleInventory implements BlockInventory, TemporaryInventory{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;

	public const SLOT_ADDITION = 1;

	public const SLOT_TEMPLATE = 2;

	public function __construct(Position $holder){
		$this->holder = $holder;
		parent::__construct(4);
	}

	public function getInput() : Item{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getAddition() : Item{
		return $this->getItem(self::SLOT_ADDITION);
	}

	public function getTemplate() : Item{
		return $this->getItem(self::SLOT_TEMPLATE);
	}

	public function getOutput() : ?Item{
		$craftingManager = Server::getInstance()->getCraftingManager();
		$recipe = $craftingManager->matchSmithingRecipe($this->getInput(), $this->getAddition(), $this->getTemplate());
		return $recipe?->constructOutput($this->getInput(), $this->getAddition(), $this->getTemplate());
	}
}
