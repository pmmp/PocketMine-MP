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

class Dropdown extends CustomFormElement{
	/** @var int */
	protected $defaultOptionIndex;
	/** @var int|null */
	protected $selectedOption;
	/** @var string[] */
	protected $options;

	/**
	 * @param string   $text
	 * @param string[] $steps
	 * @param int      $defaultStepIndex
	 */
	public function __construct(string $text, array $steps, int $defaultStepIndex = 0){
		parent::__construct($text);
		$this->options = $steps;

		if(!isset($this->options[$defaultStepIndex])){
			throw new \InvalidArgumentException("No option at index $defaultStepIndex, cannot set as default");
		}
		$this->defaultOptionIndex = $defaultStepIndex;
	}

	public function getType() : string{
		return "dropdown";
	}

	/**
	 * @return int|null
	 */
	public function getValue() : ?int{
		return $this->selectedOption;
	}

	/**
	 * @param int $value
	 *
	 * @throws \TypeError
	 */
	public function setValue($value) : void{
		if(!is_int($value)){
			throw new \TypeError("Expected int, got " . gettype($value));
		}

		$this->selectedOption = $value;
	}

	/**
	 * Returns the text of the step at the specified index, or null if it doesn't exist.
	 *
	 * @param int $index
	 *
	 * @return string|null
	 */
	public function getStep(int $index) : ?string{
		return $this->options[$index] ?? null;
	}

	/**
	 * Returns the text of the selected option.
	 * @return string
	 */
	public function getSelectedOption() : string{
		$index = $this->getValue();
		if($index === null){
			throw new \InvalidStateException("No option selected, maybe the form hasn't been submitted yet");
		}

		$option = $this->getStep($index);

		if($option !== null){
			return $option;
		}

		throw new \InvalidStateException("No option found at index $index");
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


	public function serializeElementData() : array{
		return [
			"options" => $this->options,
			"default" => $this->defaultOptionIndex
		];
	}
}