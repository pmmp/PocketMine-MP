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
use pocketmine\entity\object\AreaEffectCloud;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

/**
 * Called when a area effect cloud applies it's effects. Happens once
 * every {@link AreaEffectCloud::getWaiting()} is reached and there are affected entities.
 *
 * @phpstan-extends EntityEvent<AreaEffectCloud>
 */
class AreaEffectCloudApplyEvent extends EntityEvent implements Cancellable{
	use CancellableTrait;

	/**
	 * @param Living[] $affectedEntities
	 */
	public function __construct(
		AreaEffectCloud $entity,
		protected array $affectedEntities
	){
		$this->entity = $entity;
	}

	/**
	 * @return AreaEffectCloud
	 */
	public function getEntity(){
		return $this->entity;
	}

	/**
	 * Returns the affected entities.
	 *
	 * @return Living[]
	 */
	public function getAffectedEntities() : array{
		return $this->affectedEntities;
	}
}
