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

namespace pocketmine\event\entity;

use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

/**
 * @phpstan-extends EntityEvent<Living>
 */
class EntityDeathEvent extends EntityEvent{

	/**
	 * @param Item[] $drops
	 */
	public function __construct(
		Living $entity,
		private array $drops = [],
		private int $xp = 0
	){
		$this->entity = $entity;
	}

	/**
	 * @return Living
	 */
	public function getEntity(){
		return $this->entity;
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array{
		return $this->drops;
	}

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops) : void{
		Utils::validateArrayValueType($drops, function(Item $_) : void{});
		$this->drops = $drops;
	}

	/**
	 * Returns how much experience is dropped due to this entity's death.
	 */
	public function getXpDropAmount() : int{
		return $this->xp;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function setXpDropAmount(int $xp) : void{
		if($xp < 0){
			throw new \InvalidArgumentException("XP drop amount must not be negative");
		}
		$this->xp = $xp;
	}
}
