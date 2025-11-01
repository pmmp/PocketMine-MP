<?php

declare(strict_types=1);

namespace pocketmine\command\traits;


use pocketmine\command\args\BaseArgument;
use pocketmine\command\args\TextArgument;
use pocketmine\command\BaseCommand;
use pocketmine\command\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use function array_slice;
use function count;
use function implode;
use function is_array;
use function usort;

trait ArgumentableTrait{
	/** @var BaseArgument[][] */
	private array $argumentList = []; // [argumentPosition => [...possible BaseArgument(s)]]
	/** @var bool[] */
	private array $requiredArgumentCount = [];

	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	abstract protected function prepare() : void;

	/**
	 * @param int          $position
	 * @param BaseArgument $argument
	 *
	 * @throws ArgumentOrderException
	 */
	public function registerArgument(int $position, BaseArgument $argument): void {
		if($position < 0) {
			throw new ArgumentOrderException("You cannot register arguments at negative positions");
		}
		if($position > 0 && !isset($this->argumentList[$position - 1])) {
			throw new ArgumentOrderException("There were no arguments before $position");
		}
		foreach($this->argumentList[$position - 1] ?? [] as $arg) {
			if($arg instanceof TextArgument) {
				throw new ArgumentOrderException("No other arguments can be registered after a TextArgument");
			}
			if($arg->isOptional() && !$argument->isOptional()){
				throw new ArgumentOrderException("You cannot register a required argument after an optional argument");
			}
		}
		$this->argumentList[$position][] = $argument;
		if(!$argument->isOptional()) {
			$this->requiredArgumentCount[$position] = true;
		}
	}

	public function parseArguments(array $rawArgs, CommandSender $sender): array {
		$return = [
			"arguments" => [],
			"errors" => []
		];
		// try parsing arguments
		$required = count($this->requiredArgumentCount);
		if(!$this->hasArguments() && count($rawArgs) > 0) { // doesnt take args but sender gives args anyways
			$return["errors"][] = [
				"code" => BaseCommand::ERR_NO_ARGUMENTS,
				"data" => []
			];
		}
		$offset = 0;
		if(count($rawArgs) > 0) {
			foreach($this->argumentList as $pos => $possibleArguments) {
				// try the one that spans more first... before the others
				usort($possibleArguments, function (BaseArgument $a, BaseArgument $b): int {
					if($a->getSpanLength() === PHP_INT_MAX) { // if it takes unlimited arguments, pull it down
						return 1;
					}

					return -1;
				});
				$parsed = false;
				$optional = true;
				foreach($possibleArguments as $argument) {
					$arg = trim(implode(" ", array_slice($rawArgs, $offset, ($len = $argument->getSpanLength()))));
					if(!$argument->isOptional()) {
						$optional = false;
					}
					if($arg !== "" && $argument->canParse($arg, $sender)) {
						$k = $argument->getName();
						$result = (clone $argument)->parse($arg, $sender);
						if(isset($return["arguments"][$k]) && !is_array($return["arguments"][$k])) {
							$old = $return["arguments"][$k];
							unset($return["arguments"][$k]);
							$return["arguments"][$k] = [$old];
							$return["arguments"][$k][] = $result;
						} else {
							$return["arguments"][$k] = $result;
						}
						if(!$optional) {
							$required--;
						}
						$offset += $len;
						$parsed = true;
						break;
					}
					if($offset > count($rawArgs)) {
						break; // we've reached the end of the argument list the user passed
					}
				}
				if(!$parsed && !($optional && empty($arg))) { // we tried every other possible argument type, none was satisfied
					$return["errors"][] = [
						"code" => BaseCommand::ERR_INVALID_ARG_VALUE,
						"data" => [
							"value" => $rawArgs[$offset] ?? "",
							"position" => $pos + 1
						]
					];

					return $return; // let's break it here.
				}
			}
		}
		if($offset < count($rawArgs)) { // this means that the arguments our user sent is more than the needed amount
			$return["errors"][] = [
				"code" => BaseCommand::ERR_TOO_MANY_ARGUMENTS,
				"data" => []
			];
		}
		if($required > 0) {// We still have more unfilled required arguments
			$return["errors"][] = [
				"code" => BaseCommand::ERR_INSUFFICIENT_ARGUMENTS,
				"data" => []
			];
		}

		return $return;
	}

	public function generateUsageMessage(): string {
		$msg = $this->getName() . " ";
		$args = [];
		foreach($this->argumentList as $arguments){
			$hasOptional = false;
			$names = [];
			foreach($arguments as $argument){
				$names[] = $argument->getName() . ":" . $argument->getTypeName();
				if($argument->isOptional()){
					$hasOptional = true;
				}
			}
			$names = implode("|", $names);
			if($hasOptional){
				$args[] = "[" . $names . "]";
			} else {
				$args[] = "<" . $names . ">";
			}
		}
		$msg .= implode(" ", $args);

		return $msg;
	}

	public function hasArguments(): bool {
		return !empty($this->argumentList);
	}

	public function hasRequiredArguments(): bool {
		foreach($this->argumentList as $arguments) {
			foreach($arguments as $argument) {
				if(!$argument->isOptional()) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return BaseArgument[][]
	 */
	public function getArgumentList(): array {
		return $this->argumentList;
	}
}
