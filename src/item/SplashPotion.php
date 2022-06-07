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

use pocketmine\entity\Location;
use pocketmine\entity\projectile\SplashPotion as SplashPotionEntity;
use pocketmine\entity\projectile\Throwable;
use pocketmine\player\Player;

class SplashPotion extends ProjectileItem{

	private PotionType $potionType;

	public function __construct(ItemIdentifier $identifier, string $name, PotionType $potionType){
		parent::__construct($identifier, $name);
		$this->potionType = $potionType;
	}

	public function getType() : PotionType{ return $this->potionType; }

	public function getMaxStackSize() : int{
		return 1;
	}

	protected function createEntity(Location $location, Player $thrower) : Throwable{
		return new SplashPotionEntity($location, $thrower, $this->potionType);
	}

	public function getThrowForce() : float{
		return 0.5;
	}
}
