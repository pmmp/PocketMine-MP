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

namespace pocketmine\command;

use pocketmine\command\parameter\defaults\TextParameter;
use pocketmine\command\parameter\Parameter;
use pocketmine\command\utils\CommandException;
use function array_shift;
use function count;
use function implode;

class Overload{
	/** @var Command */
	protected $command;

	/** @var Parameter[] */
	protected $parameters = [];

	public function __construct(Command $command){
		$this->command = $command;
	}

	public function addParameter(Parameter $parameter) : self{
		if(count($this->parameters) !== 0){
			foreach($this->parameters as $oldParameter){
				if($parameter->getName() === $oldParameter->getName()){
					throw new CommandException("Cannot register multiple parameters with the same name");
				}
			}
		}
		$this->parameters[] = $parameter;
		return $this;
	}

	public function getCommand() : Command{
		return $this->command;
	}

	/**
	 * @return Parameter[]
	 */
	public function getParameters() : array{
		if(count($this->parameters) === 0){
			return [new TextParameter("args", true)]; //Prevents parameters from being sent with empty values
		}
		$parameters = [];
		foreach($this->parameters as $position => $parameter){
			$parameter->prepare();
			$parameters[] = $parameter;
		}
		return $parameters;
	}

	public function canParse(CommandSender $sender, array $args) : bool{
		$argsCount = count($args);

		if($argsCount < count($this->parameters)){
			return false;
		}
		$offset = 0;
		foreach($this->getParameters() as $parameter){
			if($parameter->getLength() === PHP_INT_MAX){
				return true;
			}
			$argument = implode(" ", array_slice($args, $offset, $parameter->getLength()));
			if(!$parameter->canParse($sender, $argument)){
				return false;
			}
			if(!$parameter->isOptional){
				$offset += $parameter->getLength();
			}
		}
		return true;
	}

	public function parse(CommandSender $sender, array $args) : array{
		$results = [];
		foreach($this->parameters as $parameter){
			$results[$parameter->getName()] = $parameter->parse($sender, ($parameter instanceof TextParameter ? implode(" ", $args) : array_shift($args)));
		}
		return $results;
	}
}