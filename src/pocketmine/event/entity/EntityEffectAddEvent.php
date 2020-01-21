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

use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;

/**
 * Called when an effect is added to an Entity.
 */
class EntityEffectAddEvent extends EntityEffectEvent{
	/** @var EffectInstance|null */
	private $oldEffect;

	public function __construct(Entity $entity, EffectInstance $effect, EffectInstance $oldEffect = null){
		parent::__construct($entity, $effect);
		$this->oldEffect = $oldEffect;
	}

	/**
	 * Returns whether the effect addition will replace an existing effect already applied to the entity.
	 */
	public function willModify() : bool{
		return $this->hasOldEffect();
	}

	public function hasOldEffect() : bool{
		return $this->oldEffect instanceof EffectInstance;
	}

	public function getOldEffect() : ?EffectInstance{
		return $this->oldEffect;
	}
}
