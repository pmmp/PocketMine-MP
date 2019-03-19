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

use function is_bool;

/**
 * Represents a UI on/off switch. The switch may have a default value.
 */
class Toggle extends CustomFormElement{
	/** @var bool */
	private $default;

	public function __construct(string $name, string $text, bool $defaultValue = false){
		parent::__construct($name, $text);
		$this->default = $defaultValue;
	}

	public function getType() : string{
		return "toggle";
	}

	/**
	 * @return bool
	 */
	public function getDefaultValue() : bool{
		return $this->default;
	}

	/**
	 * @param bool $value
	 *
	 * @throws FormValidationException
	 */
	public function validateValue($value) : void{
		if(!is_bool($value)){
			throw new FormValidationException("Expected bool, got " . gettype($value));
		}
	}

	protected function serializeElementData() : array{
		return [
			"default" => $this->default
		];
	}
}
