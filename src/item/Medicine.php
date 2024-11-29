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

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\Living;
use pocketmine\player\Player;

class Medicine extends Item implements ConsumableItem{

	private MedicineType $medicineType = MedicineType::EYE_DROPS;

	protected function describeState(RuntimeDataDescriber $w) : void{
		$w->enum($this->medicineType);
	}

	public function getType() : MedicineType{ return $this->medicineType; }

	/**
	 * @return $this
	 */
	public function setType(MedicineType $type) : self{
		$this->medicineType = $type;
		return $this;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onConsume(Living $consumer) : void{
		$consumer->getEffects()->remove($this->getType()->getCuredEffect());
	}

	public function getAdditionalEffects() : array{
		return [];
	}

	public function getResidue() : Item{
		return VanillaItems::GLASS_BOTTLE();
	}

	public function canStartUsingItem(Player $player) : bool{
		return $player->getEffects()->has($this->getType()->getCuredEffect());
	}
}
