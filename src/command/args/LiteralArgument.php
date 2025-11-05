<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;

class LiteralArgument extends BaseArgument
{
    private string $literal;

    public function __construct(string $name, string $literal, bool $optional = false)
    {
        parent::__construct($name, $optional);
        $this->literal = $literal;
        // Use the parameter name as literal for clients if possible
        $this->parameterData = CommandParameter::standard($name, $this->getNetworkType(), 0, $this->isOptional());
        // expose this literal as a soft-enum so clients can show the fixed token value
        try {
            $reflection = new \ReflectionClass($this->parameterData);
            $enumProp = $reflection->getProperty('enum');
            $enumProp->setAccessible(true);
            $enumProp->setValue($this->parameterData, new CommandSoftEnum($name, [$this->literal]));
        } catch (\Throwable $e) {
            // ignore if reflection fails
        }
    }

    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }

    public function canParse(string $testString, CommandSender $sender): bool
    {
        return strcasecmp($testString, $this->literal) === 0;
    }

    public function parse(string $argument, CommandSender $sender): mixed
    {
        // enforce literal
        if ($this->canParse($argument, $sender)) return $this->literal;
        throw new \InvalidArgumentException("Expected literal '{$this->literal}' but got '{$argument}'");
    }

    public function getTypeName(): string
    {
        return 'literal';
    }
}
