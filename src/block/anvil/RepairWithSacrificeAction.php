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

namespace pocketmine\block\anvil;

use pocketmine\item\Durable;
use pocketmine\item\Item;
use function assert;
use function min;

final class RepairWithSacrificeAction extends AnvilAction{
	private const COST = 2;

	public function canBeApplied() : bool{
		return $this->base instanceof Durable &&
			$this->material instanceof Durable &&
			$this->base->getTypeId() === $this->material->getTypeId();
	}

	public function process(Item $resultItem) : void{
		assert($resultItem instanceof Durable, "Result item must be durable");
		assert($this->base instanceof Durable, "Base item must be durable");
		assert($this->material instanceof Durable, "Material item must be durable");

		if($this->base->getDamage() !== 0){
			$baseMaxDurability = $this->base->getMaxDurability();
			$baseDurability = $baseMaxDurability - $this->base->getDamage();
			$materialDurability = $this->material->getMaxDurability() - $this->material->getDamage();
			$addDurability = (int) ($baseMaxDurability * 12 / 100);

			$newDurability = min($baseMaxDurability, $baseDurability + $materialDurability + $addDurability);

			$resultItem->setDamage($baseMaxDurability - $newDurability);

			$this->xpCost = self::COST;
		}
	}
}
