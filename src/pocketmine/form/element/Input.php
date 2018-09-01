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

/**
 * Element which accepts text input. The text-box can have a default value, and may also have a text hint when there is
 * no text in the box.
 */
class Input extends CustomFormElement{

	/** @var string */
	private $hint;
	/** @var string */
	private $default;
	/** @var string|null */
	private $value;

	/**
	 * @param string $text
	 * @param string $hintText
	 * @param string $defaultText
	 */
	public function __construct(string $text, string $hintText = "", string $defaultText = ""){
		parent::__construct($text);
		$this->hint = $hintText;
		$this->default = $defaultText;
	}

	public function getType() : string{
		return "input";
	}

	/**
	 * @return string|null
	 */
	public function getValue() : ?string{
		return $this->value;
	}

	/**
	 * @param string $value
	 *
	 * @throws \TypeError
	 */
	public function setValue($value) : void{
		if(!is_string($value)){
			throw new \TypeError("Expected string, got " . gettype($value));
		}

		$this->value = $value;
	}

	/**
	 * Returns the text shown in the text-box when the box is not focused and there is no text in it.
	 * @return string
	 */
	public function getHintText() : string{
		return $this->hint;
	}

	/**
	 * Returns the text which will be in the text-box by default.
	 * @return string
	 */
	public function getDefaultText() : string{
		return $this->default;
	}


	public function serializeElementData() : array{
		return [
			"placeholder" => $this->hint,
			"default" => $this->default
		];
	}

}