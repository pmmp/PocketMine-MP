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
use pocketmine\world\sound\BottleEmptySound;

class Potion extends Item implements ConsumableItem{

	private PotionType $potionType = PotionType::WATER;

	protected function describeState(RuntimeDataDescriber $w) : void{
		$w->enum($this->potionType);
	}

	public function getType() : PotionType{ return $this->potionType; }

	/**
	 * @return $this
	 */
	public function setType(PotionType $type) : self{
		$this->potionType = $type;
		return $this;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onConsume(Living $consumer) : void{
		$consumer->broadcastSound(new BottleEmptySound());
	}

	public function getAdditionalEffects() : array{
		//TODO: check CustomPotionEffects NBT
		return $this->potionType->getEffects();
	}

	public function getResidue() : Item{
		return VanillaItems::GLASS_BOTTLE();
	}

	public function canStartUsingItem(Player $player) : bool{
		return true;
	}
}
