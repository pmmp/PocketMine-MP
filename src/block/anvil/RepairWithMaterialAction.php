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
use function ceil;
use function floor;
use function max;
use function min;

final class RepairWithMaterialAction extends AnvilAction{
	private const COST = 1;

	public function canBeApplied() : bool{
		return $this->base instanceof Durable &&
			$this->base->isValidRepairMaterial($this->material) &&
			$this->base->getDamage() > 0;
	}

	public function process(Item $resultItem) : void{
		assert($resultItem instanceof Durable, "Result item must be durable");
		assert($this->base instanceof Durable, "Base item must be durable");

		$damage = $this->base->getDamage();
		$quarter = min($damage, (int) floor($this->base->getMaxDurability() / 4));
		$numberRepair = min($this->material->getCount(), (int) ceil($damage / $quarter));
		if($numberRepair > 0){
			$this->material->pop($numberRepair);
			$damage -= $quarter * $numberRepair;
		}
		$resultItem->setDamage(max(0, $damage));

		$this->xpCost = $numberRepair * self::COST;
	}
}
