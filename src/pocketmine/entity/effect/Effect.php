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

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\utils\Color;

class Effect{

	/** @var int */
	protected $id;
	/** @var string */
	protected $name;
	/** @var Color */
	protected $color;
	/** @var bool */
	protected $bad;
	/** @var bool */
	protected $hasBubbles;

	/**
	 * @param int    $id Effect ID as per Minecraft PE
	 * @param string $name Translation key used for effect name
	 * @param Color  $color
	 * @param bool   $isBad Whether the effect is harmful
	 * @param bool   $hasBubbles Whether the effect has potion bubbles. Some do not (e.g. Instant Damage has its own particles instead of bubbles)
	 */
	public function __construct(int $id, string $name, Color $color, bool $isBad = false, bool $hasBubbles = true){
		$this->id = $id;
		$this->name = $name;
		$this->color = $color;
		$this->bad = $isBad;
		$this->hasBubbles = $hasBubbles;
	}

	/**
	 * Returns the effect ID as per Minecraft PE
	 * @return int
	 */
	public function getId() : int{
		return $this->id;
	}

	/**
	 * Returns the translation key used to translate this effect's name.
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * Returns a Color object representing this effect's particle colour.
	 * @return Color
	 */
	public function getColor() : Color{
		return $this->color;
	}

	/**
	 * Returns whether this effect is harmful.
	 * TODO: implement inverse effect results for undead mobs
	 *
	 * @return bool
	 */
	public function isBad() : bool{
		return $this->bad;
	}

	/**
	 * Returns the default duration this effect will apply for if a duration is not specified.
	 * @return int
	 */
	public function getDefaultDuration() : int{
		return 600;
	}

	/**
	 * Returns whether this effect will give the subject potion bubbles.
	 * @return bool
	 */
	public function hasBubbles() : bool{
		return $this->hasBubbles;
	}

	/**
	 * Returns whether the effect will do something on the current tick.
	 *
	 * @param EffectInstance $instance
	 *
	 * @return bool
	 */
	public function canTick(EffectInstance $instance) : bool{
		return false;
	}

	/**
	 * Applies effect results to an entity. This will not be called unless canTick() returns true.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 * @param float          $potency
	 * @param null|Entity    $source
	 */
	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void{

	}

	/**
	 * Applies effects to the entity when the effect is first added.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 */
	public function add(Living $entity, EffectInstance $instance) : void{

	}

	/**
	 * Removes the effect from the entity, resetting any changed values back to their original defaults.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 */
	public function remove(Living $entity, EffectInstance $instance) : void{

	}
}
