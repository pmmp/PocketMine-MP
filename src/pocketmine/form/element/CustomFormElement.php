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
 * Base class for UI elements which can be placed on custom forms.
 */
abstract class CustomFormElement implements \JsonSerializable{
	/** @var string */
	private $text;

	public function __construct(string $text){
		$this->text = $text;
	}

	/**
	 * Returns the type of element.
	 * @return string
	 */
	abstract public function getType() : string;

	/**
	 * Returns the element's label. Usually this is used to explain to the user what a control does.
	 * @return string
	 */
	public function getText() : string{
		return $this->text;
	}

	/**
	 * Returns the value of the component after it's been set by a form response from a player.
	 * @return mixed
	 */
	abstract public function getValue();

	/**
	 * Sets the component's value to the specified argument. This function should do appropriate type checking and throw
	 * whatever errors necessary if the type of value is not as expected.
	 *
	 * @param mixed $value
	 * @throws \TypeError
	 */
	abstract public function setValue($value) : void;

	/**
	 * Returns an array of properties which can be serialized to JSON for sending.
	 *
	 * @return array
	 */
	final public function jsonSerialize() : array{
		$data = [
			"type" => $this->getType(),
			"text" => $this->getText()
		];

		return array_merge($data, $this->serializeElementData());
	}

	/**
	 * Returns an array of extra data needed to serialize this element to JSON for showing to a player on a form.
	 * @return array
	 */
	abstract public function serializeElementData() : array;
}