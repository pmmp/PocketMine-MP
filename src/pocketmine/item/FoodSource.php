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
use pocketmine\entity\Entity;

interface FoodSource{

	/**
	 * Returns the number of hunger points this food type will give to the eater.
	 *
	 * @return int
	 */
	public function getFoodRestore() : int;

	/**
	 * Returns the amount of saturation which will be given to the eater when the food is eaten.
	 *
	 * @return float
	 */
	public function getSaturationRestore() : float;

	/**
	 * Returns the result of eating the food source.
	 * This may return an Item for eating an item, or a Block for eating things like cake.
	 *
	 * @return Item|Block|mixed
	 */
	public function getResidue();

	/**
	 * Returns effects to be applied to the eater.
	 *
	 * @return Effect[]
	 */
	public function getAdditionalEffects() : array;

	/**
	 * Returns whether the target entity can consume this food item.
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function canBeConsumedBy(Entity $entity) : bool;

	/**
	 * Returns whether the eater must be hungry to eat this item.
	 *
	 * @return bool
	 */
	public function requiresHunger() : bool;
}
