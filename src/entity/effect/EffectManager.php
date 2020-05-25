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

use Ds\Set;
use pocketmine\color\Color;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
use function abs;
use function count;

class EffectManager{

	/** @var Living */
	private $entity;

	/** @var EffectInstance[] */
	protected $effects = [];

	/** @var Color */
	protected $bubbleColor;
	/** @var bool */
	protected $onlyAmbientEffects = false;

	/**
	 * @var \Closure[]|Set
	 * @phpstan-var Set<\Closure(EffectInstance, bool $replacesOldEffect) : void>
	 */
	protected $effectAddHooks;
	/**
	 * @var \Closure[]|Set
	 * @phpstan-var Set<\Closure(EffectInstance) : void>
	 */
	protected $effectRemoveHooks;

	public function __construct(Living $entity){
		$this->entity = $entity;
		$this->bubbleColor = new Color(0, 0, 0, 0);
		$this->effectAddHooks = new Set();
		$this->effectRemoveHooks = new Set();
	}

	/**
	 * Returns an array of Effects currently active on the mob.
	 * @return EffectInstance[]
	 */
	public function all() : array{
		return $this->effects;
	}

	/**
	 * Removes all effects from the mob.
	 */
	public function clear() : void{
		foreach($this->effects as $effect){
			$this->remove($effect->getType());
		}
	}

	/**
	 * Removes the effect with the specified ID from the mob.
	 */
	public function remove(Effect $effectType) : void{
		$index = $effectType->getId();
		if(isset($this->effects[$index])){
			$effect = $this->effects[$index];
			$hasExpired = $effect->hasExpired();
			$ev = new EntityEffectRemoveEvent($this->entity, $effect);
			$ev->call();
			if($ev->isCancelled()){
				if($hasExpired and !$ev->getEffect()->hasExpired()){ //altered duration of an expired effect to make it not get removed
					foreach($this->effectAddHooks as $hook){
						$hook($ev->getEffect(), true);
					}
				}
				return;
			}

			unset($this->effects[$index]);
			$effect->getType()->remove($this->entity, $effect);
			foreach($this->effectRemoveHooks as $hook){
				$hook($effect);
			}

			$this->recalculateEffectColor();
		}
	}

	/**
	 * Returns the effect instance active on this entity with the specified ID, or null if the mob does not have the
	 * effect.
	 */
	public function get(Effect $effect) : ?EffectInstance{
		return $this->effects[$effect->getId()] ?? null;
	}

	/**
	 * Returns whether the specified effect is active on the mob.
	 */
	public function has(Effect $effect) : bool{
		return isset($this->effects[$effect->getId()]);
	}

	/**
	 * Adds an effect to the mob.
	 * If a weaker effect of the same type is already applied, it will be replaced.
	 * If a weaker or equal-strength effect is already applied but has a shorter duration, it will be replaced.
	 *
	 * @return bool whether the effect has been successfully applied.
	 */
	public function add(EffectInstance $effect) : bool{
		$oldEffect = null;
		$cancelled = false;

		$index = $effect->getType()->getId();
		if(isset($this->effects[$index])){
			$oldEffect = $this->effects[$index];
			if(
				abs($effect->getAmplifier()) < $oldEffect->getAmplifier()
				or (abs($effect->getAmplifier()) === abs($oldEffect->getAmplifier()) and $effect->getDuration() < $oldEffect->getDuration())
			){
				$cancelled = true;
			}
		}

		$ev = new EntityEffectAddEvent($this->entity, $effect, $oldEffect);
		$ev->setCancelled($cancelled);

		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		if($oldEffect !== null){
			$oldEffect->getType()->remove($this->entity, $oldEffect);
		}

		$effect->getType()->add($this->entity, $effect);
		foreach($this->effectAddHooks as $hook){
			$hook($effect, $oldEffect !== null);
		}

		$this->effects[$index] = $effect;

		$this->recalculateEffectColor();

		return true;
	}

	/**
	 * Recalculates the mob's potion bubbles colour based on the active effects.
	 */
	protected function recalculateEffectColor() : void{
		/** @var Color[] $colors */
		$colors = [];
		$ambient = true;
		foreach($this->effects as $effect){
			if($effect->isVisible() and $effect->getType()->hasBubbles()){
				$level = $effect->getEffectLevel();
				$color = $effect->getColor();
				for($i = 0; $i < $level; ++$i){
					$colors[] = $color;
				}

				if(!$effect->isAmbient()){
					$ambient = false;
				}
			}
		}

		if(count($colors) > 0){
			$this->bubbleColor = Color::mix(...$colors);
			$this->onlyAmbientEffects = $ambient;
		}else{
			$this->bubbleColor = new Color(0, 0, 0, 0);
			$this->onlyAmbientEffects = false;
		}
	}

	public function getBubbleColor() : Color{
		return $this->bubbleColor;
	}

	public function hasOnlyAmbientEffects() : bool{
		return $this->onlyAmbientEffects;
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

	/**
	 * @return \Closure[]|Set
	 * @phpstan-return Set<\Closure(EffectInstance, bool $replacesOldEffect) : void>
	 */
	public function getEffectAddHooks() : Set{
		return $this->effectAddHooks;
	}

	/**
	 * @return \Closure[]|Set
	 * @phpstan-return Set<\Closure(EffectInstance) : void>
	 */
	public function getEffectRemoveHooks() : Set{
		return $this->effectRemoveHooks;
	}
}
