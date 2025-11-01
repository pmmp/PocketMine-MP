<?php

declare(strict_types=1);

namespace pocketmine\command;

use pocketmine\command\traits\ArgumentableTrait;
use pocketmine\command\traits\IArgumentable;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;

abstract class CommandoCommand extends VanillaCommand implements IArgumentable {
    use ArgumentableTrait;

    /** @var CommandSender|null */
    protected ?CommandSender $currentSender = null;

    public function __construct(string $name, string $description = "", string $usage = "", array $aliases = []) {
        parent::__construct($name, $description, $usage, $aliases);
        $this->prepare();
    }

    /**
     * Implement this to register arguments and sub-behaviour
     */
    abstract protected function prepare(): void;

    /**
     * Implement the command logic here. Arguments will be parsed according to registered arguments.
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args parsed, typed arguments
     */
    abstract public function onRun(CommandSender $sender, string $aliasUsed, array $args): void;

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$this->testPermission($sender)) {
            return false;
        }

        $this->currentSender = $sender ?? null; // some trait methods expect a current sender property when parsing

        try {
            $dat = $this->parseArguments($args, $sender);
        } catch (InvalidCommandSyntaxException $e) {
            $sender->sendMessage($e->getMessage());
            return false;
        }

        if (!empty($dat["errors"])) {
            foreach ($dat["errors"] as $error) {
                // The trait parsing uses error structures similar to BaseCommand
                $sender->sendMessage(isset($error["message"]) ? $error["message"] : "Invalid argument");
            }
            return false;
        }

        $parsed = $dat["arguments"] ?? [];
        $this->onRun($sender, $commandLabel, $parsed);
        return true;
    }
}
