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

use pocketmine\item\enchantment\VanillaEnchantments;

abstract class Tool extends Durable{

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getMiningEfficiency(bool $isCorrectTool) : float{
		$efficiency = 1;
		if($isCorrectTool){
			$efficiency = $this->getBaseMiningEfficiency();
			if(($enchantmentLevel = $this->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY())) > 0){
				$efficiency += ($enchantmentLevel ** 2 + 1);
			}
		}

		return $efficiency;
	}

	protected function getBaseMiningEfficiency() : float{
		return 1;
	}
}
