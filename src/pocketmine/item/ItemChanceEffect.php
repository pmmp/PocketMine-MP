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


namespace pocketmine\item;


use pocketmine\entity\Effect;

/**
 * Class used to manage effect properties for consumable items which have a random chance of giving the eater effects.
 */
class ItemChanceEffect{
	private $effect;
	private $chance;

	public function __construct(Effect $effect, float $chance = 1.0){
		$this->effect = $effect;
		$this->chance = max(0, min($chance, 1.0));
	}

	/**
	 * @return Effect
	 */
	public function getEffect() : Effect{
		return $this->effect;
	}

	/**
	 * Returns the percentage chance that this effect will be applied to the eater
	 * @return float
	 */
	public function getChance() : float{
		return $this->chance;
	}

	/**
	 * Returns a random true/false whether to apply an effect to the eater
	 * @return bool
	 */
	public function shouldApply() : bool{
		return lcg_value() <= $this->chance;
	}
}