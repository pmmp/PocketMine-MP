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

namespace pocketmine\command\overload;

use pocketmine\command\utils\CommandStringHelper;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use function array_push;
use function array_slice;
use function count;
use function ksort;
use function strlen;
use const SORT_NATURAL;

/**
 * @internal Do not construct this class directly. Use {@link OverloadBuilder instead}.
 */
final class BranchingOverload implements Overload{

	/**
	 * @param Parameter[]|string[] $commonParameters
	 * @param Overload[]           $anonymousChildren
	 * @param Overload[]           $namedChildren
	 *
	 * @phpstan-param list<Parameter<*>|string> $commonParameters
	 * @phpstan-param list<Overload>          $anonymousChildren
	 * @phpstan-param array<string, Overload> $namedChildren
	 */
	public function __construct(
		private array $commonParameters,
		private array $anonymousChildren,
		private array $namedChildren
	){
		if(count($this->anonymousChildren) + count($this->namedChildren) < 2){
			throw new \InvalidArgumentException("Branching overload must have at least 2 child overloads");
		}
		ksort($this->namedChildren, SORT_NATURAL);
	}

	/**
	 * @param mixed[] $parentArgs
	 *
	 * @phpstan-param list<mixed> $parentArgs
	 *
	 * @throws InvalidCommandSyntaxException
	 */
	public function invoke(CommandContext $context, int $offset, array $parentArgs, int $parentParametersParsed) : bool{
		$myParameters = $parentParametersParsed > 0 ? array_slice($this->commonParameters, $parentParametersParsed) : $this->commonParameters;

		$commandLine = $context->getCommandLine();
		try{
			$myArgs = CommandStringHelper::parseArguments($myParameters, $commandLine, $offset);
		}catch(InvalidCommandSyntaxException $e){
			$context->invalidSyntax($this, $offset, $e->getMessage());
			return false;
		}

		if(count($this->namedChildren) > 0){
			//make sure we don't modify the offset until we're sure of success
			$offsetCopy = $offset;
			CommandStringHelper::skipWhitespace($commandLine, $offsetCopy);
			if($offset < strlen($commandLine)){
				$childName = CommandStringHelper::parseQuoteAwareSingle($commandLine, $offsetCopy);
				if(isset($this->namedChildren[$childName])){
					$offset = $offsetCopy;
					return $this->namedChildren[$childName]->invoke(
						$context,
						$offset,
						[...$parentArgs, ...$myArgs],
						$parentParametersParsed + count($myParameters) + 1 //child overload can skip the literal
					);
				}
			}elseif(count($this->anonymousChildren) === 0){
				$context->invalidSyntax($this, $offset, "Expected a subcommand name");
				return false;
			}
		}

		foreach($this->anonymousChildren as $branch){
			if($branch->invoke($context, $offset, [...$parentArgs, ...$myArgs], $parentParametersParsed + count($myParameters))){
				return true;
			}
		}

		//since we don't try to run all the named children, they need to be added to the command context as failed
		//manually, to make sure they show up in usage messages
		foreach($this->namedChildren as $branch){
			$context->invalidSyntax($branch, $offset, "Subcommand name mismatch");
		}

		//children should have updated the command context with proper syntax errors
		return false;
	}

	public function collectExecutors() : array{
		$result = [];
		foreach($this->namedChildren as $childOverload){
			array_push($result, ...$childOverload->collectExecutors());
		}
		foreach($this->anonymousChildren as $childOverload){
			array_push($result, ...$childOverload->collectExecutors());
		}

		return $result;
	}

}
