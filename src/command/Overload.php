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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\Utils;
use function count;
use function implode;

class Overload{
	/** @var \Closure|null */
	protected $commandHandler = null;
	/** @var Parameter[] */
	protected $parameters = [];

	public function __construct(?\Closure $commandHandler = null){
		if($commandHandler !== null){
			Utils::validateCallableSignature(function(CommandSender $sender, array $args){}, $commandHandler);
		}
		$this->commandHandler = $commandHandler;
	}

	public function addParameter(Parameter $parameter) : self{
		if(count($this->parameters) !== 0){
			foreach($this->parameters as $oldParameter){
				if($parameter->getName() === $oldParameter->getName()){
					throw new CommandException("Cannot register multiple parameters with the same name");
				}
			}
		}
		$parameter->setOverload($this);
		$this->parameters[] = $parameter;
		return $this;
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

	/**
	 * @param string[] $args
	 */
	public function canParse(CommandSender $sender, array $args) : bool{
		$argsCount = count($args);
		$parameterCount = count($this->parameters);

		if($argsCount < $parameterCount){
			throw new InvalidCommandSyntaxException();
		}
		$offset = 0;
		$parsed = false;
		foreach($this->getParameters() as $parameter){
			if($offset > $parameterCount){
				break;
			}
			if($parameter->getLength() === PHP_INT_MAX){
				$parsed = true;
				break;
			}
			$argument = implode(" ", array_slice($args, $offset, $parameter->getLength()));
			if(!$parameter->canParse($sender, $argument)){
				break;
			}
			if(!$parameter->isOptional){
				$offset += $parameter->getLength();
			}
			$parsed = true;
		}
		return $parsed;
	}

	public function getCommandHandler() : ?\Closure{
		return $this->commandHandler;
	}

	public function setCommandHandler(?\Closure $handler) : self{
		if($handler !== null){
			Utils::validateCallableSignature(function(CommandSender $sender, array $args){}, $handler);
		}
		$this->commandHandler = $handler;
		return $this;
	}
}