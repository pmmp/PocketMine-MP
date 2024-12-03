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

namespace pocketmine\block;

use pocketmine\block\utils\PillarRotationTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function mt_rand;

class CreakingHeart extends Opaque{
	use PillarRotationTrait;

	protected bool $active = false;
	protected bool $natural = false;

	public function isActive() : bool{ return $this->active; }

	/** @return $this */
	public function setActive(bool $active) : self{
		$this->active = $active;
		return $this;
	}

	public function isNatural() : bool{ return $this->natural; }

	/** @return $this */
	public function setNatural(bool $natural) : self{
		$this->natural = $natural;
		return $this;
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
		if($this->natural){
			$this->position->getWorld()->dropExperience($this->position, mt_rand(20, 24));
			return true;
		}
		return parent::onBreak($item, $player, $returnedItems);
	}

	//TODO: ambient sounds
}
