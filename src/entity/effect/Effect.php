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

namespace pocketmine\entity\effect;

use pocketmine\color\Color;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\lang\Translatable;
use pocketmine\utils\NotCloneable;
use pocketmine\utils\NotSerializable;

class Effect{
	use NotCloneable;
	use NotSerializable;

	/**
	 * @param Translatable|string $name Translation key used for effect name
	 * @param Color               $color Color of bubbles given by this effect
	 * @param bool                $bad Whether the effect is harmful
	 * @param bool                $hasBubbles Whether the effect has potion bubbles. Some do not (e.g. Instant Damage has its own particles instead of bubbles)
	 */
	public function __construct(
		protected Translatable|string $name,
		protected Color $color,
		protected bool $bad = false,
		protected bool $hasBubbles = true
	){}

	/**
	 * Returns the translation key used to translate this effect's name.
	 */
	public function getName() : Translatable|string{
		return $this->name;
	}

	/**
	 * Returns a Color object representing this effect's particle colour.
	 */
	public function getColor() : Color{
		return $this->color;
	}

	/**
	 * Returns whether this effect is harmful.
	 * TODO: implement inverse effect results for undead mobs
	 */
	public function isBad() : bool{
		return $this->bad;
	}

	/**
	 * Returns the default duration (in ticks) this effect will apply for if a duration is not specified.
	 */
	public function getDefaultDuration() : int{
		return 600;
	}

	/**
	 * Returns whether this effect will give the subject potion bubbles.
	 */
	public function hasBubbles() : bool{
		return $this->hasBubbles;
	}

	/**
	 * Returns whether the effect will do something on the current tick.
	 */
	public function canTick(EffectInstance $instance) : bool{
		return false;
	}

	/**
	 * Applies effect results to an entity. This will not be called unless canTick() returns true.
	 */
	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void{

	}

	/**
	 * Applies effects to the entity when the effect is first added.
	 */
	public function add(Living $entity, EffectInstance $instance) : void{

	}

	/**
	 * Removes the effect from the entity, resetting any changed values back to their original defaults.
	 */
	public function remove(Living $entity, EffectInstance $instance) : void{

	}
}
