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

namespace pocketmine\form\element;

use pocketmine\form\FormValidationException;

use function array_values;
use function is_int;

abstract class BaseSelector extends CustomFormElement{
	/** @var int */
	protected $defaultOptionIndex;
	/** @var string[] */
	protected $options;

	/**
	 * @param string   $name
	 * @param string   $text
	 * @param string[] $options
	 * @param int      $defaultOptionIndex
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $name, string $text, array $options, int $defaultOptionIndex = 0){
		parent::__construct($name, $text);
		$this->options = array_values($options);

		if(!isset($this->options[$defaultOptionIndex])){
			throw new \InvalidArgumentException("No option at index $defaultOptionIndex, cannot set as default");
		}
		$this->defaultOptionIndex = $defaultOptionIndex;
	}

	/**
	 * @param int $value
	 *
	 * @throws FormValidationException
	 */
	public function validateValue($value) : void{
		if(!is_int($value)){
			throw new FormValidationException("Expected int, got " . gettype($value));
		}
		if(!isset($this->options[$value])){
			throw new FormValidationException("Option $value does not exist");
		}
	}

	/**
	 * Returns the text of the option at the specified index, or null if it doesn't exist.
	 *
	 * @param int $index
	 *
	 * @return string|null
	 */
	public function getOption(int $index) : ?string{
		return $this->options[$index] ?? null;
	}

	/**
	 * @return int
	 */
	public function getDefaultOptionIndex() : int{
		return $this->defaultOptionIndex;
	}

	/**
	 * @return string
	 */
	public function getDefaultOption() : string{
		return $this->options[$this->defaultOptionIndex];
	}

	/**
	 * @return string[]
	 */
	public function getOptions() : array{
		return $this->options;
	}
}
