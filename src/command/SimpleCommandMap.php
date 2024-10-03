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

use pocketmine\command\defaults\BanCommand;
use pocketmine\command\defaults\BanIpCommand;
use pocketmine\command\defaults\BanListCommand;
use pocketmine\command\defaults\ClearCommand;
use pocketmine\command\defaults\DefaultGamemodeCommand;
use pocketmine\command\defaults\DeopCommand;
use pocketmine\command\defaults\DifficultyCommand;
use pocketmine\command\defaults\DumpMemoryCommand;
use pocketmine\command\defaults\EffectCommand;
use pocketmine\command\defaults\EnchantCommand;
use pocketmine\command\defaults\ExperienceCommand;
use pocketmine\command\defaults\GamemodeCommand;
use pocketmine\command\defaults\GarbageCollectorCommand;
use pocketmine\command\defaults\GiveCommand;
use pocketmine\command\defaults\HelpCommand;
use pocketmine\command\defaults\KickCommand;
use pocketmine\command\defaults\KillCommand;
use pocketmine\command\defaults\ListCommand;
use pocketmine\command\defaults\MeCommand;
use pocketmine\command\defaults\OpCommand;
use pocketmine\command\defaults\PardonCommand;
use pocketmine\command\defaults\PardonIpCommand;
use pocketmine\command\defaults\ParticleCommand;
use pocketmine\command\defaults\PluginsCommand;
use pocketmine\command\defaults\SaveCommand;
use pocketmine\command\defaults\SaveOffCommand;
use pocketmine\command\defaults\SaveOnCommand;
use pocketmine\command\defaults\SayCommand;
use pocketmine\command\defaults\SeedCommand;
use pocketmine\command\defaults\SetWorldSpawnCommand;
use pocketmine\command\defaults\SpawnpointCommand;
use pocketmine\command\defaults\StatusCommand;
use pocketmine\command\defaults\StopCommand;
use pocketmine\command\defaults\TeleportCommand;
use pocketmine\command\defaults\TellCommand;
use pocketmine\command\defaults\TimeCommand;
use pocketmine\command\defaults\TimingsCommand;
use pocketmine\command\defaults\TitleCommand;
use pocketmine\command\defaults\TransferServerCommand;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\defaults\VersionCommand;
use pocketmine\command\defaults\WhitelistCommand;
use pocketmine\command\utils\CommandStringHelper;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use function array_shift;
use function count;
use function implode;
use function str_contains;
use function strcasecmp;
use function strtolower;
use function trim;

class SimpleCommandMap implements CommandMap{

	/** @var Command[] */
	protected array $knownCommands = [];

	public function __construct(private Server $server){
		$this->setDefaultCommands();
	}

	private function setDefaultCommands() : void{
		$this->registerAll("pocketmine", [
			new BanCommand(),
			new BanIpCommand(),
			new BanListCommand(),
			new ClearCommand(),
			new DefaultGamemodeCommand(),
			new DeopCommand(),
			new DifficultyCommand(),
			new DumpMemoryCommand(),
			new EffectCommand(),
			new EnchantCommand(),
			new ExperienceCommand(),
			new GamemodeCommand(),
			new GarbageCollectorCommand(),
			new GiveCommand(),
			new HelpCommand(),
			new KickCommand(),
			new KillCommand(),
			new ListCommand(),
			new MeCommand(),
			new OpCommand(),
			new PardonCommand(),
			new PardonIpCommand(),
			new ParticleCommand(),
			new PluginsCommand(),
			new SaveCommand(),
			new SaveOffCommand(),
			new SaveOnCommand(),
			new SayCommand(),
			new SeedCommand(),
			new SetWorldSpawnCommand(),
			new SpawnpointCommand(),
			new StatusCommand(),
			new StopCommand(),
			new TeleportCommand(),
			new TellCommand(),
			new TimeCommand(),
			new TimingsCommand(),
			new TitleCommand(),
			new TransferServerCommand(),
			new VersionCommand(),
			new WhitelistCommand()
		]);
	}

	public function registerAll(string $fallbackPrefix, array $commands) : void{
		foreach($commands as $command){
			$this->register($fallbackPrefix, $command);
		}
	}

	public function register(string $fallbackPrefix, Command $command, ?string $label = null) : bool{
		if(count($command->getPermissions()) === 0){
			throw new \InvalidArgumentException("Commands must have a permission set");
		}

		if($label === null){
			$label = $command->getLabel();
		}
		$label = trim($label);
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registered = $this->registerAlias($command, false, $fallbackPrefix, $label);

		$aliases = $command->getAliases();
		foreach($aliases as $index => $alias){
			if(!$this->registerAlias($command, true, $fallbackPrefix, $alias)){
				unset($aliases[$index]);
			}
		}
		$command->setAliases($aliases);

		if(!$registered){
			$command->setLabel($fallbackPrefix . ":" . $label);
		}

		$command->register($this);

		return $registered;
	}

	public function unregister(Command $command) : bool{
		foreach($this->knownCommands as $lbl => $cmd){
			if($cmd === $command){
				unset($this->knownCommands[$lbl]);
			}
		}

		$command->unregister($this);

		return true;
	}

	private function registerAlias(Command $command, bool $isAlias, string $fallbackPrefix, string $label) : bool{
		$this->knownCommands[$fallbackPrefix . ":" . $label] = $command;
		if(($command instanceof VanillaCommand || $isAlias) && isset($this->knownCommands[$label])){
			return false;
		}

		if(isset($this->knownCommands[$label]) && $this->knownCommands[$label]->getLabel() === $label){
			return false;
		}

		if(!$isAlias){
			$command->setLabel($label);
		}

		$this->knownCommands[$label] = $command;

		return true;
	}

	public function dispatch(CommandSender $sender, string $commandLine) : bool{
		$args = CommandStringHelper::parseQuoteAware($commandLine);

		$sentCommandLabel = array_shift($args);
		if($sentCommandLabel !== null && ($target = $this->getCommand($sentCommandLabel)) !== null){
			$timings = Timings::getCommandDispatchTimings($target->getLabel());
			$timings->startTiming();

			try{
				if($target->testPermission($sender)){
					$target->execute($sender, $sentCommandLabel, $args);
				}
			}catch(InvalidCommandSyntaxException $e){
				$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage())));
			}finally{
				$timings->stopTiming();
			}
			return true;
		}

		$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound($sentCommandLabel ?? "", "/help")->prefix(TextFormat::RED));
		return false;
	}

	public function clearCommands() : void{
		foreach($this->knownCommands as $command){
			$command->unregister($this);
		}
		$this->knownCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand(string $name) : ?Command{
		return $this->knownCommands[$name] ?? null;
	}

	/**
	 * @return Command[]
	 */
	public function getCommands() : array{
		return $this->knownCommands;
	}

	public function registerServerAliases() : void{
		$values = $this->server->getCommandAliases();

		foreach($values as $alias => $commandStrings){
			if(str_contains($alias, ":")){
				$this->server->getLogger()->warning($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_command_alias_illegal($alias)));
				continue;
			}

			$targets = [];
			$bad = [];
			$recursive = [];

			foreach($commandStrings as $commandString){
				$args = CommandStringHelper::parseQuoteAware($commandString);
				$commandName = array_shift($args) ?? "";
				$command = $this->getCommand($commandName);

				if($command === null){
					$bad[] = $commandString;
				}elseif(strcasecmp($commandName, $alias) === 0){
					$recursive[] = $commandString;
				}else{
					$targets[] = $commandString;
				}
			}

			if(count($recursive) > 0){
				$this->server->getLogger()->warning($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_command_alias_recursive($alias, implode(", ", $recursive))));
				continue;
			}

			if(count($bad) > 0){
				$this->server->getLogger()->warning($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_command_alias_notFound($alias, implode(", ", $bad))));
				continue;
			}

			//These registered commands have absolute priority
			$lowerAlias = strtolower($alias);
			if(count($targets) > 0){
				$this->knownCommands[$lowerAlias] = new FormattedCommandAlias($lowerAlias, $targets);
			}else{
				unset($this->knownCommands[$lowerAlias]);
			}

		}
	}
}
