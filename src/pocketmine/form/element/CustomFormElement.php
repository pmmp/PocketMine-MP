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

/**
 * Base class for UI elements which can be placed on custom forms.
 */
abstract class CustomFormElement implements \JsonSerializable{
	/** @var string */
	private $name;
	/** @var string */
	private $text;

	public function __construct(string $name, string $text){
		$this->name = $name;
		$this->text = $text;
	}

	/**
	 * Returns the type of element.
	 * @return string
	 */
	abstract public function getType() : string;

	/**
	 * Returns the element's name. This is used to identify the element in code.
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * Returns the element's label. Usually this is used to explain to the user what a control does.
	 * @return string
	 */
	public function getText() : string{
		return $this->text;
	}

	/**
	 * Validates that the given value is of the correct type and fits the constraints for the component. This function
	 * should do appropriate type checking and throw whatever errors necessary if the value is not valid.
	 *
	 * @param mixed $value
	 * @throws FormValidationException
	 */
	abstract public function validateValue($value) : void;

	/**
	 * Returns an array of properties which can be serialized to JSON for sending.
	 *
	 * @return array
	 */
	final public function jsonSerialize() : array{
		$ret = $this->serializeElementData();
		$ret["type"] = $this->getType();
		$ret["text"] = $this->getText();

		return $ret;
	}

	/**
	 * Returns an array of extra data needed to serialize this element to JSON for showing to a player on a form.
	 * @return array
	 */
	abstract protected function serializeElementData() : array;
}
