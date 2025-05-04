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
use pocketmine\command\defaults\VersionCommand;
use pocketmine\command\defaults\WhitelistCommand;
use pocketmine\command\defaults\XpCommand;
use pocketmine\command\utils\CommandStringHelper;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function array_filter;
use function array_shift;
use function array_values;
use function count;
use function implode;
use function spl_object_id;
use function str_contains;
use function strcasecmp;
use function strtolower;
use function trim;

class SimpleCommandMap implements CommandMap{

	/**
	 * @var Command[]
	 * @phpstan-var array<string, Command>
	 */
	protected array $aliasToCommandMap = [];

	/**
	 * @var CommandMapEntry[]
	 * @phpstan-var array<int, CommandMapEntry>
	 */
	private array $uniqueCommands = [];

	public function __construct(private Server $server){
		$this->setDefaultCommands();
	}

	private function setDefaultCommands() : void{
		$pmPrefix = "pocketmine";
		$this->register($pmPrefix, new BanCommand(), "ban");
		$this->register($pmPrefix, new BanIpCommand(), "ban-ip");
		$this->register($pmPrefix, new BanListCommand(), "banlist");
		$this->register($pmPrefix, new ClearCommand(), "clear");
		$this->register($pmPrefix, new DefaultGamemodeCommand(), "defaultgamemode");
		$this->register($pmPrefix, new DeopCommand(), "deop");
		$this->register($pmPrefix, new DifficultyCommand(), "difficulty");
		$this->register($pmPrefix, new DumpMemoryCommand(), "dumpmemory");
		$this->register($pmPrefix, new EffectCommand(), "effect");
		$this->register($pmPrefix, new EnchantCommand(), "enchant");
		$this->register($pmPrefix, new GamemodeCommand(), "gamemode");
		$this->register($pmPrefix, new GarbageCollectorCommand(), "gc");
		$this->register($pmPrefix, new GiveCommand(), "give");
		$this->register($pmPrefix, new HelpCommand(), "help", ["?"]);
		$this->register($pmPrefix, new KickCommand(), "kick");
		$this->register($pmPrefix, new KillCommand(), "kill", ["suicide"]);
		$this->register($pmPrefix, new ListCommand(), "list");
		$this->register($pmPrefix, new MeCommand(), "me");
		$this->register($pmPrefix, new OpCommand(), "op");
		$this->register($pmPrefix, new PardonCommand(), "pardon", ["unban"]);
		$this->register($pmPrefix, new PardonIpCommand(), "pardon-ip", ["unban-ip"]);
		$this->register($pmPrefix, new ParticleCommand(), "particle");
		$this->register($pmPrefix, new PluginsCommand(), "plugins", ["pl"]);
		$this->register($pmPrefix, new SaveCommand(), "save-all");
		$this->register($pmPrefix, new SaveOffCommand(), "save-off");
		$this->register($pmPrefix, new SaveOnCommand(), "save-on");
		$this->register($pmPrefix, new SayCommand(), "say");
		$this->register($pmPrefix, new SeedCommand(), "seed");
		$this->register($pmPrefix, new SetWorldSpawnCommand(), "setworldspawn");
		$this->register($pmPrefix, new SpawnpointCommand(), "spawnpoint");
		$this->register($pmPrefix, new StatusCommand(), "status");
		$this->register($pmPrefix, new StopCommand(), "stop");
		$this->register($pmPrefix, new TeleportCommand(), "tp", ["teleport"]);
		$this->register($pmPrefix, new TellCommand(), "tell", ["w", "msg"]);
		$this->register($pmPrefix, new TimeCommand(), "time");
		$this->register($pmPrefix, new TimingsCommand(), "timings");
		$this->register($pmPrefix, new TitleCommand(), "title");
		$this->register($pmPrefix, new TransferServerCommand(), "transferserver");
		$this->register($pmPrefix, new VersionCommand(), "version", ["ver", "about"]);
		$this->register($pmPrefix, new WhitelistCommand(), "whitelist");
		$this->register($pmPrefix, new XpCommand(), "xp");
	}

	public function register(string $fallbackPrefix, Command $command, string $preferredAlias, array $otherAliases = []) : CommandMapEntry{
		if(count($command->getPermissions()) === 0){
			throw new \InvalidArgumentException("Commands must have a permission set");
		}

		$preferredAlias = trim($preferredAlias);
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registeredAliases = [];
		//primary labels take precedence over any existing registrations
		$this->mapAlias($preferredAlias, $command, $registeredAliases);
		$this->mapAlias($fallbackPrefix . ":" . $preferredAlias, $command, $registeredAliases);

		foreach($otherAliases as $alias){
			$this->mapAlias($fallbackPrefix . ":" . $alias, $command, $registeredAliases);
			if(!isset($this->aliasToCommandMap[$alias])){
				$this->mapAlias($alias, $command, $registeredAliases);
			}
		}

		$entry = new CommandMapEntry($command, $registeredAliases);
		$this->uniqueCommands[spl_object_id($command)] = $entry;

		return $entry;
	}

	/**
	 * @param string[] &$registeredAliases
	 * @phpstan-param list<string> &$registeredAliases
	 * @phpstan-param-out non-empty-list<string> $registeredAliases
	 */
	private function mapAlias(string $alias, Command $command, array &$registeredAliases) : void{
		$this->unmapAlias($alias);
		$this->aliasToCommandMap[$alias] = $command;
		$registeredAliases[] = $alias;
	}

	private function unmapAlias(string $alias) : void{
		$oldCommand = $this->aliasToCommandMap[$alias] ?? null;
		if($oldCommand !== null){
			unset($this->aliasToCommandMap[$alias]);
			$oldCommandKey = spl_object_id($oldCommand);
			$oldCommandEntry = $this->uniqueCommands[$oldCommandKey];
			$filteredAliases = array_values(array_filter($oldCommandEntry->aliases, fn(string $oldAlias) => $oldAlias !== $alias));
			if(count($filteredAliases) > 0){
				$this->uniqueCommands[$oldCommandKey] = new CommandMapEntry($oldCommand, $filteredAliases);
			}else{
				unset($this->uniqueCommands[$oldCommandKey]);
			}
		}
	}

	public function unregister(Command $command) : bool{
		$entry = $this->uniqueCommands[spl_object_id($command)] ?? null;
		if($entry !== null){
			unset($this->uniqueCommands[spl_object_id($command)]);
			foreach($entry->aliases as $alias){
				unset($this->aliasToCommandMap[$alias]);
			}
		}

		return true;
	}

	public function dispatch(CommandSender $sender, string $commandLine) : bool{
		$args = CommandStringHelper::parseQuoteAware($commandLine);

		$sentCommandLabel = array_shift($args);
		if($sentCommandLabel !== null && ($target = $this->getEntry($sentCommandLabel)) !== null){
			//TODO: using labels for command dispatch is problematic - what if the label changes?
			//maybe this should use command class instead?
			$timings = Timings::getCommandDispatchTimings($target->getPreferredAlias());
			$timings->startTiming();

			try{
				if($target->command->testPermission($sentCommandLabel, $sender)){
					$target->command->execute($sender, $sentCommandLabel, $args);
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
		$this->aliasToCommandMap = [];
		$this->uniqueCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand(string $name) : ?Command{
		return $this->aliasToCommandMap[$name] ?? null;
	}

	/**
	 * @return Command[]
	 * @phpstan-return array<string, Command>
	 */
	public function getAliasToCommandMap() : array{
		return $this->aliasToCommandMap;
	}

	/**
	 * @return CommandMapEntry[]
	 * @phpstan-return array<int, CommandMapEntry>
	 */
	public function getUniqueCommands() : array{
		return $this->uniqueCommands;
	}

	public function getEntry(string $name) : ?CommandMapEntry{
		$command = $this->getCommand($name);
		return $command !== null ?
			$this->uniqueCommands[spl_object_id($command)] ?? throw new AssumptionFailedError("This should never be unset") :
			null;
	}

	public function registerServerAliases() : void{
		$values = $this->server->getCommandAliases();

		foreach(Utils::stringifyKeys($values) as $alias => $commandStrings){
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
			$this->unmapAlias($lowerAlias);
			if(count($targets) > 0){
				$aliasInstance = new FormattedCommandAlias($targets);
				$registeredAliases = [];
				$this->mapAlias($lowerAlias, $aliasInstance, $registeredAliases);
				$this->uniqueCommands[spl_object_id($aliasInstance)] = new CommandMapEntry($aliasInstance, $registeredAliases);
			}
		}
	}
}
