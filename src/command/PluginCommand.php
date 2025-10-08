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

use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\Translatable;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

final class PluginCommand extends Command implements PluginOwned{
	public function __construct(
		string $namespace,
		string $name,
		private Plugin $owner,
		private CommandExecutor $executor,
		Translatable|string $description = "",
		Translatable|string|null $usageMessage = null
	){
		parent::__construct($namespace, $name, $description, $usageMessage);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){

		if(!$this->owner->isEnabled()){
			return false;
		}

		if(!$this->executor->onCommand($sender, $this, $commandLabel, $args)){
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}

	public function getOwningPlugin() : Plugin{
		return $this->owner;
	}

	public function getExecutor() : CommandExecutor{
		return $this->executor;
	}

	public function setExecutor(CommandExecutor $executor) : void{
		$this->executor = $executor;
	}
}
