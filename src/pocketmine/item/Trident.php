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

declare(strict_types = 1);

namespace pocketmine\item;

use pocketmine\entity\Entity;
use pocketmine\item\Tool;
use pocketmine\Player;

class Trident extends Tool {

	public const TAG_TRIDENT = "Trident";

	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::TRIDENT, $meta, "Trident");
	}

	public function getMaxDurability(): int{
		return 251;
	}

	public function onReleaseUsing(Player $player): bool{
		if($player->getItemUseDuration() < 10){ // release is half a second I think...
			return false;
		}
		$nbt = Entity::createBaseNBT(
			$player->add(0, $player->getEyeHeight(), 0),
			$player->getDirectionVector()->multiply(4),
			($player->yaw > 180 ? 360 : 0) - $player->yaw,
			-$player->pitch
		);
	 }

		return true;
	}

	public function getMaxStackSize(): int{
		return 1;
	}

	public function onAttackEntity(Entity $victim): bool{
		return $this->applyDamage(1);
	}

	public function getAttackPoints(): int{
		return 8;
}
