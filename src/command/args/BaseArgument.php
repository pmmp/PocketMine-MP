<?php

declare(strict_types=1);

namespace pocketmine\command\args;


use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

abstract class BaseArgument {
	/** @var string */
	protected string $name;
	/** @var bool */
	protected bool $optional = false;
	/** @var CommandParameter */
	protected CommandParameter $parameterData;

	public function __construct(string $name, bool $optional = false) {
		$this->name = $name;
		$this->optional = $optional;

		$this->parameterData = CommandParameter::standard($name, $this->getNetworkType(), 0, $this->isOptional());
	}

	abstract public function getNetworkType(): int;

	/**
	 * @param string            $testString
	 * @param CommandSender     $sender
	 *
	 * @return bool
	 */
	abstract public function canParse(string $testString, CommandSender $sender): bool;

	/**
	 * @param string        $argument
	 * @param CommandSender $sender
	 *
	 * @return mixed
	 */
	abstract public function parse(string $argument, CommandSender $sender) : mixed;

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function isOptional(): bool {
		return $this->optional;
	}

	/**
	 * Returns how much command arguments
	 * it takes to build the full argument
	 *
	 * @return int
	 */
	public function getSpanLength(): int {
		return 1;
	}

	abstract public function getTypeName(): string;

	public function getNetworkParameterData():CommandParameter {
		return $this->parameterData;
	}
}