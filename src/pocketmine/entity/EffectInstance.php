<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\utils\Color;

class EffectInstance{
    /** @var Effect */
    private $effectType;

    /** @var int */
    private $duration;

    /** @var int */
    private $amplifier;

    /** @var bool */
    private $visible;

    /** @var bool */
    private $ambient;

    /** @var Color */
    private $color;

    /**
     * @param Effect     $effectType
     * @param int|null   $duration Passing null will use the effect type's default duration
     * @param int        $amplifier
     * @param bool       $visible
     * @param bool       $ambient
     * @param null|Color $overrideColor
     */
    public function __construct(Effect $effectType, ?int $duration = null, int $amplifier = 0, bool $visible = true, bool $ambient = false, ?Color $overrideColor = null){
        $this->effectType = $effectType;
        $this->setDuration($duration ?? $effectType->getDefaultDuration());
        $this->amplifier = $amplifier;
        $this->visible = $visible;
        $this->ambient = $ambient;
        $this->color = $overrideColor ?? $effectType->getColor();
    }

    public function getId() : int{
        return $this->effectType->getId();
    }

    /**
     * @return Effect
     */
    public function getType() : Effect{
        return $this->effectType;
    }

    /**
     * @return int
     */
    public function getDuration() : int{
        return $this->duration;
    }

    /**
     * @param int $duration
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setDuration(int $duration) : EffectInstance{
        if($duration < 0 or $duration > INT32_MAX){
            throw new \InvalidArgumentException("Effect duration must be in range 0 - " . INT32_MAX . ", got $duration");
        }
        $this->duration = $duration;

        return $this;
    }

    /**
     * Decreases the duration by the given number of ticks, without dropping below zero.
     *
     * @param int $ticks
     *
     * @return $this
     */
    public function decreaseDuration(int $ticks) : EffectInstance{
        $this->duration = max(0, $this->duration - $ticks);

        return $this;
    }

    /**
     * Returns whether the duration has run out.
     *
     * @return bool
     */
    public function hasExpired() : bool{
        return $this->duration <= 0;
    }

    /**
     * @return int
     */
    public function getAmplifier() : int{
        return $this->amplifier;
    }

    /**
     * Returns the level of this effect, which is always one higher than the amplifier.
     *
     * @return int
     */
    public function getEffectLevel() : int{
        return $this->amplifier + 1;
    }

    /**
     * @param int $amplifier
     *
     * @return $this
     */
    public function setAmplifier(int $amplifier) : EffectInstance{
        $this->amplifier = $amplifier;

        return $this;
    }

    /**
     * Returns whether this effect will produce some visible effect, such as bubbles or particles.
     *
     * @return bool
     */
    public function isVisible() : bool{
        return $this->visible;
    }

    /**
     * @param bool $visible
     *
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
     *
     * @return bool
     */
    public function isAmbient() : bool{
        return $this->ambient;
    }

    /**
     * @param bool $ambient
     *
     * @return $this
     */
    public function setAmbient(bool $ambient = true) : EffectInstance{
        $this->ambient = $ambient;

        return $this;
    }

    /**
     * Returns the particle colour of this effect instance. This can be overridden on a per-EffectInstance basis, so it
     * is not reflective of the default colour of the effect.
     *
     * @return Color
     */
    public function getColor() : Color{
        return clone $this->color;
    }

    /**
     * Sets the colour of this EffectInstance.
     *
     * @param Color $color
     *
     * @return EffectInstance
     */
    public function setColor(Color $color) : EffectInstance{
        $this->color = clone $color;

        return $this;
    }

    /**
     * Resets the colour of this EffectInstance to the default specified by its type.
     *
     * @return EffectInstance
     */
    public function resetColor() : EffectInstance{
        $this->color = $this->effectType->getColor();

        return $this;
    }
}