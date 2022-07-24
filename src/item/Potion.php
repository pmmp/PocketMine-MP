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

use pocketmine\entity\Living;
use pocketmine\player\Player;

class Potion extends Item implements ConsumableItem{

	private PotionType $potionType;

	public function __construct(ItemIdentifier $identifier, string $name, PotionType $potionType){
		parent::__construct($identifier, $name);
		$this->potionType = $potionType;
	}

	public function getType() : PotionType{ return $this->potionType; }

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onConsume(Living $consumer) : void{

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
