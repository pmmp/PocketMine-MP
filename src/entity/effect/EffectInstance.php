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
use pocketmine\utils\Limits;
use function max;

class EffectInstance{
	private Effect $effectType;
	private int $duration;
	private int $amplifier;
	private bool $visible;
	private bool $ambient;
	private Color $color;

	/**
	 * @param int|null $duration Passing null will use the effect type's default duration
	 */
	public function __construct(Effect $effectType, ?int $duration = null, int $amplifier = 0, bool $visible = true, bool $ambient = false, ?Color $overrideColor = null){
		$this->effectType = $effectType;
		$this->setDuration($duration ?? $effectType->getDefaultDuration());
		$this->setAmplifier($amplifier);
		$this->visible = $visible;
		$this->ambient = $ambient;
		$this->color = $overrideColor ?? $effectType->getColor();
	}

	public function getType() : Effect{
		return $this->effectType;
	}

	/**
	 * Returns the number of ticks remaining until the effect expires.
	 */
	public function getDuration() : int{
		return $this->duration;
	}

	/**
	 * Sets the number of ticks remaining until the effect expires.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return $this
	 */
	public function setDuration(int $duration) : EffectInstance{
		if($duration < 0 || $duration > Limits::INT32_MAX){
			throw new \InvalidArgumentException("Effect duration must be in range 0 - " . Limits::INT32_MAX . ", got $duration");
		}
		$this->duration = $duration;

		return $this;
	}

	/**
	 * Decreases the duration by the given number of ticks, without dropping below zero.
	 *
	 * @return $this
	 */
	public function decreaseDuration(int $ticks) : EffectInstance{
		$this->duration = max(0, $this->duration - $ticks);

		return $this;
	}

	/**
	 * Returns whether the duration has run out.
	 */
	public function hasExpired() : bool{
		return $this->duration <= 0;
	}

	public function getAmplifier() : int{
		return $this->amplifier;
	}

	/**
	 * Returns the level of this effect, which is always one higher than the amplifier.
	 */
	public function getEffectLevel() : int{
		return $this->amplifier + 1;
	}

	/**
	 * @return $this
	 */
	public function setAmplifier(int $amplifier) : EffectInstance{
		if($amplifier < 0 || $amplifier > 255){
			throw new \InvalidArgumentException("Amplifier must be in range 0 - 255, got $amplifier");
		}
		$this->amplifier = $amplifier;

		return $this;
	}

	/**
	 * Returns whether this effect will produce some visible effect, such as bubbles or particles.
	 */
	public function isVisible() : bool{
		return $this->visible;
	}

	/**
	 * @return $this
	 */
	public function setVisible(bool $visible = true) : EffectInstance{
		$this->visible = $visible;

		return $this;
	}

	/**
	 * Returns whether the effect originated from the ambient environment.
	 * Ambient effects can originate from things such as a Beacon's area of effect radius.
	 * If this flag is set, the amount of visible particles will be reduced by a factor of 5.
	 */
	public function isAmbient() : bool{
		return $this->ambient;
	}

	/**
	 * @return $this
	 */
	public function setAmbient(bool $ambient = true) : EffectInstance{
		$this->ambient = $ambient;

		return $this;
	}

	/**
	 * Returns the particle colour of this effect instance. This can be overridden on a per-EffectInstance basis, so it
	 * is not reflective of the default colour of the effect.
	 */
	public function getColor() : Color{
		return $this->color;
	}

	/**
	 * Sets the colour of this EffectInstance.
	 */
	public function setColor(Color $color) : EffectInstance{
		$this->color = $color;

		return $this;
	}

	/**
	 * Resets the colour of this EffectInstance to the default specified by its type.
	 */
	public function resetColor() : EffectInstance{
		$this->color = $this->effectType->getColor();

		return $this;
	}
}
