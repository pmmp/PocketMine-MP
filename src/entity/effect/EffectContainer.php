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

use pocketmine\utils\ObjectSet;
use function abs;
use function spl_object_id;

class EffectContainer{
	/** @var EffectInstance[] */
	protected array $effects = [];

	/**
	 * @var \Closure[]|ObjectSet
	 * @phpstan-var ObjectSet<\Closure(EffectInstance, bool $replacesOldEffect) : void>
	 */
	protected ObjectSet $effectAddHooks;
	/**
	 * @var \Closure[]|ObjectSet
	 * @phpstan-var ObjectSet<\Closure(EffectInstance) : void>
	 */
	protected ObjectSet $effectRemoveHooks;

	public function __construct(){
		$this->effectAddHooks = new ObjectSet();
		$this->effectRemoveHooks = new ObjectSet();
	}

	/**
	 * Returns an array of Effects currently active.
	 * @return EffectInstance[]
	 */
	public function all() : array{
		return $this->effects;
	}

	/**
	 * Removes all effects.
	 */
	public function clear() : void{
		foreach($this->effects as $effect){
			$this->remove($effect->getType());
		}
	}

	/**
	 * Removes the effect with the specified ID.
	 */
	public function remove(Effect $effectType) : void{
		$index = spl_object_id($effectType);
		if(isset($this->effects[$index])){
			$effect = $this->effects[$index];

			unset($this->effects[$index]);
			foreach($this->effectRemoveHooks as $hook){
				$hook($effect);
			}
		}
	}

	/**
	 * Returns the effect instance active with the specified ID, or null if does not have the
	 * effect.
	 */
	public function get(Effect $effect) : ?EffectInstance{
		return $this->effects[spl_object_id($effect)] ?? null;
	}

	/**
	 * Returns whether the specified effect is active.
	 */
	public function has(Effect $effect) : bool{
		return isset($this->effects[spl_object_id($effect)]);
	}

	/**
	 * In the following cases it will return true:
	 * If a weaker effect of the same type is already applied.
	 * If a weaker or equal-strength effect is already applied but has a shorter duration.
	 *
	 * @return bool whether the effect can been applied.
	 */
	public function canAdd(EffectInstance $effect) : bool{
		$index = spl_object_id($effect->getType());
		if(isset($this->effects[$index])){
			$oldEffect = $this->effects[$index];
			if(
				abs($effect->getAmplifier()) < $oldEffect->getAmplifier()
				|| (abs($effect->getAmplifier()) === abs($oldEffect->getAmplifier()) && $effect->getDuration() < $oldEffect->getDuration())
			){
				return false;
			}
		}
		return true;
	}

	/**
	 * Adds an effect.
	 *
	 * @return bool whether the effect has been successfully applied.
	 */
	public function add(EffectInstance $effect, bool $force = false) : bool{
		if($force || $this->canAdd($effect)){
			$index = spl_object_id($effect->getType());
			foreach($this->effectAddHooks as $hook){
				$hook($effect, isset($this->effects[$index]));
			}

			$this->effects[$index] = $effect;
			return true;
		}

		return false;
	}

	/**
	 * @return \Closure[]|ObjectSet
	 * @phpstan-return ObjectSet<\Closure(EffectInstance, bool $replacesOldEffect) : void>
	 */
	public function getEffectAddHooks() : ObjectSet{
		return $this->effectAddHooks;
	}

	/**
	 * @return \Closure[]|ObjectSet
	 * @phpstan-return ObjectSet<\Closure(EffectInstance) : void>
	 */
	public function getEffectRemoveHooks() : ObjectSet{
		return $this->effectRemoveHooks;
	}
}
