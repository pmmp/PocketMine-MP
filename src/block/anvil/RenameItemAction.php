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

use pocketmine\item\Item;
use function strlen;

final class RenameItemAction extends AnvilAction{
	private const COST = 1;

	public function canBeApplied() : bool{
		return true;
	}

	public function process(Item $resultItem) : void{
		if($this->customName === null || strlen($this->customName) === 0){
			if($this->base->hasCustomName()){
				$this->xpCost += self::COST;
				$resultItem->clearCustomName();
			}
		}else{
			if($this->base->getCustomName() !== $this->customName){
				$this->xpCost += self::COST;
				$resultItem->setCustomName($this->customName);
			}
		}
	}
}
