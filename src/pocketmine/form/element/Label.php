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
 * Element which displays some text on a form.
 */
class Label extends CustomFormElement{

	public function getType() : string{
		return "label";
	}

	public function getValue(){
		return null;
	}

	public function setValue($value) : void{
		assert($value === null);
	}

	public function serializeElementData() : array{
		return [];
	}

}