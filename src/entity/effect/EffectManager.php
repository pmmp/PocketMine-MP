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

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
use function count;
use function spl_object_id;

class EffectManager extends EffectContainer{

	public function __construct(
		private Living $entity
	){
		parent::__construct();
	}

	/**
	 * Removes the effect with the specified ID from the mob.
	 */
	public function remove(Effect $effectType) : void{
		$index = spl_object_id($effectType);
		if(isset($this->effects[$index])){
			$effect = $this->effects[$index];
			$ev = new EntityEffectRemoveEvent($this->entity, $effect);
			$ev->call();
			if($ev->isCancelled()){
				foreach($this->effectAddHooks as $hook){
					$hook($ev->getEffect(), true);
				}
				return;
			}

			$effect->getType()->remove($this->entity, $effect);
			parent::remove($effectType);
		}
	}

	/**
	 * Adds an effect to the mob.
	 * If a weaker effect of the same type is already applied, it will be replaced.
	 * If a weaker or equal-strength effect is already applied but has a shorter duration, it will be replaced.
	 *
	 * @return bool whether the effect has been successfully applied.
	 */
	public function add(EffectInstance $effect, bool $force = false) : bool{
		$index = spl_object_id($effect->getType());
		$oldEffect = $this->effects[$index] ?? null;

		$ev = new EntityEffectAddEvent($this->entity, $effect, $oldEffect);
		if(!$this->canAdd($effect)){
			$ev->cancel();
		}

		$ev->call();
		if(!$force && $ev->isCancelled()){
			return false;
		}

		if($oldEffect !== null){
			$oldEffect->getType()->remove($this->entity, $oldEffect);
		}

		$effect->getType()->add($this->entity, $effect);

		return parent::add($effect, true);
	}

	public function tick(int $tickDiff = 1) : bool{
		foreach($this->effects as $instance){
			$type = $instance->getType();
			if($type->canTick($instance)){
				$type->applyEffect($this->entity, $instance);
			}
			$instance->decreaseDuration($tickDiff);
			if($instance->hasExpired()){
				$this->remove($instance->getType());
			}
		}

		return count($this->effects) > 0;
	}
}
