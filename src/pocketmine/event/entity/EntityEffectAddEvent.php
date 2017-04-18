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

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;

class EntityEffectAddEvent extends EntityEffectEvent{
	public static $handlerList = null;

	/** @var bool */
	private $modify;
	/** @var Effect */
	private $oldEffect;

	public function __construct(Entity $entity, Effect $effect, $modify, $oldEffect){
		parent::__construct($entity, $effect);
		$this->modify = $modify;
		$this->oldEffect = $oldEffect;
	}

	public function willModify() : bool{
		return $this->modify;
	}

	public function setWillModify(bool $modify){
		$this->modify = $modify;
	}

	public function hasOldEffect() : bool{
		return $this->oldEffect instanceof Effect;
	}

	/**
	 * @return Effect|null
	 */
	public function getOldEffect(){
		return $this->oldEffect;
	}


}