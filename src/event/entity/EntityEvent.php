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

/**
 * Entity related Events, like spawn, inventory, attack...
 */
namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Event;

/**
 * @phpstan-template TEntity of Entity
 */
abstract class EntityEvent extends Event{
	/**
	 * @var Entity
	 * @phpstan-var TEntity
	 */
	protected $entity;

	/**
	 * @return Entity
	 * @phpstan-return TEntity
	 */
	public function getEntity(){
		return $this->entity;
	}
}
