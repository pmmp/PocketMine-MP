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

namespace pocketmine\form\element;

class Slider extends CustomFormElement{

	/** @var float */
	private $min;
	/** @var float */
	private $max;
	/** @var float */
	private $step = 1.0;
	/** @var float */
	private $default;
	/** @var float|null */
	private $value;

	public function __construct(string $text, float $min, float $max, float $step = 1.0, ?float $default = null){
		parent::__construct($text);

		if($this->min > $this->max){
			throw new \InvalidArgumentException("Slider min value should be less than max value");
		}
		$this->min = $min;
		$this->max = $max;

		if($default !== null){
			if($default > $this->max or $default < $this->min){
				throw new \InvalidArgumentException("Default must be in range $this->min ... $this->max");
			}
			$this->default = $default;
		}else{
			$this->default = $this->min;
		}

		if($step <= 0){
			throw new \InvalidArgumentException("Step must be greater than zero");
		}
		$this->step = $step;
	}

	public function getType() : string{
		return "slider";
	}

	/**
	 * @return float|null
	 */
	public function getValue() : ?float{
		return $this->value;
	}

	/**
	 * @param float $value
	 *
	 * @throws \TypeError
	 */
	public function setValue($value) : void{
		if(!is_float($value) and !is_int($value)){
			throw new \TypeError("Expected float, got " . gettype($value));
		}

		$this->value = $value;
	}

	/**
	 * @return float
	 */
	public function getMin() : float{
		return $this->min;
	}

	/**
	 * @return float
	 */
	public function getMax() : float{
		return $this->max;
	}

	/**
	 * @return float
	 */
	public function getStep() : float{
		return $this->step;
	}

	/**
	 * @return float
	 */
	public function getDefault() : float{
		return $this->default;
	}


	public function serializeElementData() : array{
		return [
			"min" => $this->min,
			"max" => $this->max,
			"default" => $this->default,
			"step" => $this->step
		];
	}
}