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

namespace pocketmine\network\mcpe\protocol\types;

class CommandParameter{

	/** @var string */
	public $paramName;
	/** @var int */
	public $paramType;
	/** @var bool */
	public $isOptional;
	/** @var int */
	public $flag;
	/** @var int */
	public $byte1 = 0; //unknown, always zero except for in /gamerule command
	/** @var CommandEnum|null */
	public $enum;
	/** @var string|null */
	public $postfix;

	public function __construct(string $name, int $type, bool $optional = true, $extraData = null){
		$this->paramName = $name;
		$this->paramType = $type;
		$this->isOptional = $optional;
		if($extraData instanceof CommandEnum){
			$this->enum = $extraData;
		}elseif(is_string($extraData)){
			$this->postfix = $extraData;
		}
	}
}