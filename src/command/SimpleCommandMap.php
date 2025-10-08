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
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function implode;
use function is_array;
use function spl_object_id;
use function str_contains;
use function strcasecmp;
use function strtolower;
use function trim;

class SimpleCommandMap implements CommandMap{

	/**
	 * @var Command[]
	 * @phpstan-var array<string, Command|array<int, Command>>
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
		$this->register($pmPrefix, new BanCommand("ban"));
		$this->register($pmPrefix, new BanIpCommand("ban-ip"));
		$this->register($pmPrefix, new BanListCommand("banlist"));
		$this->register($pmPrefix, new ClearCommand("clear"));
		$this->register($pmPrefix, new DefaultGamemodeCommand("defaultgamemode"));
		$this->register($pmPrefix, new DeopCommand("deop"));
		$this->register($pmPrefix, new DifficultyCommand("difficulty"));
		$this->register($pmPrefix, new DumpMemoryCommand("dumpmemory"));
		$this->register($pmPrefix, new EffectCommand("effect"));
		$this->register($pmPrefix, new EnchantCommand("enchant"));
		$this->register($pmPrefix, new GamemodeCommand("gamemode"));
		$this->register($pmPrefix, new GarbageCollectorCommand("gc"));
		$this->register($pmPrefix, new GiveCommand("give"));
		$this->register($pmPrefix, new HelpCommand("help"), ["?"]);
		$this->register($pmPrefix, new KickCommand("kick"));
		$this->register($pmPrefix, new KillCommand("kill"), ["suicide"]);
		$this->register($pmPrefix, new ListCommand("list"));
		$this->register($pmPrefix, new MeCommand("me"));
		$this->register($pmPrefix, new OpCommand("op"));
		$this->register($pmPrefix, new PardonCommand("pardon"), ["unban"]);
		$this->register($pmPrefix, new PardonIpCommand("pardon-ip"), ["unban-ip"]);
		$this->register($pmPrefix, new ParticleCommand("particle"));
		$this->register($pmPrefix, new PluginsCommand("plugins"), ["pl"]);
		$this->register($pmPrefix, new SaveCommand("save-all"));
		$this->register($pmPrefix, new SaveOffCommand("save-off"));
		$this->register($pmPrefix, new SaveOnCommand("save-on"));
		$this->register($pmPrefix, new SayCommand("say"));
		$this->register($pmPrefix, new SeedCommand("seed"));
		$this->register($pmPrefix, new SetWorldSpawnCommand("setworldspawn"));
		$this->register($pmPrefix, new SpawnpointCommand("spawnpoint"));
		$this->register($pmPrefix, new StatusCommand("status"));
		$this->register($pmPrefix, new StopCommand("stop"));
		$this->register($pmPrefix, new TeleportCommand("tp"), ["teleport"]);
		$this->register($pmPrefix, new TellCommand("tell"), ["w", "msg"]);
		$this->register($pmPrefix, new TimeCommand("time"));
		$this->register($pmPrefix, new TimingsCommand("timings"));
		$this->register($pmPrefix, new TitleCommand("title"));
		$this->register($pmPrefix, new TransferServerCommand("transferserver"));
		$this->register($pmPrefix, new VersionCommand("version"), ["ver", "about"]);
		$this->register($pmPrefix, new WhitelistCommand("whitelist"));
		$this->register($pmPrefix, new XpCommand("xp"));
	}

	public function register(string $namespace, Command $command, array $otherAliases = []) : CommandMapEntry{
		if(count($command->getPermissions()) === 0){
			throw new \InvalidArgumentException("Commands must have a permission set");
		}
		if(isset($this->uniqueCommands[spl_object_id($command)])){
			throw new \InvalidArgumentException("This Command object has already been registered");
		}

		//TODO: inconsistency here with casing?
		$preferredAlias = trim($command->getName());
		$namespace = strtolower(trim($namespace));

		$registeredAliases = [];

		//prefixed alias must always succeed in registration - namespace should prevent conflicts
		$prefixedAlias = $namespace . ":" . $preferredAlias;
		if(isset($this->aliasToCommandMap[$prefixedAlias])){
			throw new \InvalidArgumentException("\"$prefixedAlias\" conflicts with another command, please choose a different command name or namespace");
		}
		$dummy = [];
		$this->mapAlias($prefixedAlias, $command, $dummy);

		$this->mapAlias($preferredAlias, $command, $registeredAliases);
		foreach($otherAliases as $alias){
			$this->mapAlias($alias, $command, $registeredAliases);
		}

		//this should always be last on the list
		$registeredAliases[] = $prefixedAlias;
		$entry = new CommandMapEntry($namespace, $command, $registeredAliases);
		$this->uniqueCommands[spl_object_id($command)] = $entry;

		return $entry;
	}

	/**
	 * @param string[] &$registeredAliases
	 * @phpstan-param list<string> &$registeredAliases
	 * @phpstan-param-out non-empty-list<string> $registeredAliases
	 */
	private function mapAlias(string $alias, Command $command, array &$registeredAliases) : void{
		$existing = $this->aliasToCommandMap[$alias] ?? null;
		if($existing !== null){
			if(!is_array($existing)){
				//previously non-conflicted - remove the alias from this command and make it conflicted
				//commands that are already conflicted shouldn't need alias removal
				$this->unregisterAlias($alias);
				$existing = [spl_object_id($existing) => $existing];
			}
			$existing[spl_object_id($command)] = $command;
			$this->aliasToCommandMap[$alias] = $existing;
		}else{
			$this->aliasToCommandMap[$alias] = $command;
			$registeredAliases[] = $alias;
		}
	}

	public function registerAlias(string $existingAlias, string $newAlias) : void{
		$existingCommand = $this->aliasToCommandMap[$existingAlias] ?? null;
		if($existingCommand === null){
			throw new \InvalidArgumentException("No command is currently using the alias \"$existingAlias\", cannot create an alias to it");
		}
		if(is_array($existingCommand)){
			throw new \InvalidArgumentException("Multiple commands are using the alias \"$existingAlias\", don't know which one to target");
		}
		$registration = $this->uniqueCommands[spl_object_id($existingCommand)];
		$newAliases = $registration->aliases;

		//explicit alias registration overrides everything else, including conflicts
		$this->unregisterAlias($newAlias);
		$this->mapAlias($newAlias, $existingCommand, $newAliases);
		$this->uniqueCommands[spl_object_id($existingCommand)] = new CommandMapEntry($registration->namespace, $existingCommand, $newAliases);
	}

	public function unregisterAlias(string $alias) : void{
		$oldCommands = $this->aliasToCommandMap[$alias] ?? null;
		if($oldCommands !== null){
			unset($this->aliasToCommandMap[$alias]);
			foreach(is_array($oldCommands) ? $oldCommands : [$oldCommands] as $oldCommand){
				$oldCommandKey = spl_object_id($oldCommand);
				$oldCommandEntry = $this->uniqueCommands[$oldCommandKey];
				$filteredAliases = array_values(array_filter($oldCommandEntry->aliases, fn(string $oldAlias) => $oldAlias !== $alias));
				if(count($filteredAliases) === 0){
					unset($this->uniqueCommands[$oldCommandKey]);
				}else{
					$this->uniqueCommands[$oldCommandKey] = new CommandMapEntry($oldCommandEntry->namespace, $oldCommandEntry->command, $filteredAliases);
				}
			}
		}
	}

	public function unregister(Command $command) : bool{
		$entry = $this->uniqueCommands[spl_object_id($command)] ?? null;
		if($entry !== null){
			unset($this->uniqueCommands[spl_object_id($command)]);
			foreach($entry->aliases as $alias){
				$commandsUsingAlias = $this->aliasToCommandMap[$alias];
				if(is_array($commandsUsingAlias) && count($commandsUsingAlias) > 0){
					unset($commandsUsingAlias[spl_object_id($command)]);
					//even if there's only 1 command left, we let it stay "conflicted" to avoid surprising behaviour
					//for users - this can still be explicitly rebound using registerAlias()
					$this->aliasToCommandMap[$alias] = $commandsUsingAlias;
				}else{
					unset($this->aliasToCommandMap[$alias]);
				}
			}
		}

		return true;
	}

	public function dispatch(CommandSender $sender, string $commandLine) : bool{
		$args = CommandStringHelper::parseQuoteAware($commandLine);

		$sentCommandLabel = array_shift($args);
		if($sentCommandLabel !== null && ($target = $this->getEntry($sentCommandLabel)) !== null){
			if(is_array($target)){
				self::handleConflicted($sender, $sentCommandLabel, $target);
				return true;
			}
			$timings = Timings::getCommandDispatchTimings($target->getNamespacedName());
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

	/**
	 * TODO: probably need to find a better place to put this
	 * @internal
	 * @param CommandMapEntry[] $conflictedEntries
	 * @phpstan-param array<int, CommandMapEntry> $conflictedEntries
	 */
	public static function handleConflicted(CommandSender $sender, string $alias, array $conflictedEntries) : void{
		$candidates = [];
		foreach($conflictedEntries as $c){
			if($c->command->testPermissionSilent($sender)){
				$candidates[] = "/" . $c->getNamespacedName();
			}
		}
		if(count($candidates) > 0){
			//TODO: l10n
			//there might only be 1 permissible command here, but we still don't auto-select in this case
			//because it might cause surprising behaviour if the user's permissions change between command
			//invocations. Better to force them to use an unambiguous alias in all cases.
			$candidateNames = implode(", ", $candidates);
			$sender->sendMessage(TextFormat::RED . "/$alias is assigned to multiple commands. Use one of these instead: $candidateNames");
		}else{
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_permission($alias)->prefix(TextFormat::RED));
		}
	}

	public function clearCommands() : void{
		$this->aliasToCommandMap = [];
		$this->uniqueCommands = [];
		$this->setDefaultCommands();
	}

	/**
	 * @return CommandMapEntry|CommandMapEntry[]|null
	 * @phpstan-return Command|array<int, Command>|null
	 */
	public function getEntry(string $name) : CommandMapEntry|array|null{
		$command = $this->aliasToCommandMap[$name] ?? null;
		if($command instanceof Command){
			return $this->uniqueCommands[spl_object_id($command)] ?? throw new AssumptionFailedError("This should never be unset");
		}
		if(is_array($command)){
			return array_map(
				fn(Command $c) => $this->uniqueCommands[spl_object_id($c)] ?? throw new AssumptionFailedError("This should never be unset"),
				$command
			);
		}
		return null;
	}

	/**
	 * @return Command[]|Command[][]
	 * @phpstan-return array<string, Command|array<int, Command>>
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
				$command = $this->getEntry($commandName);

				if(!$command instanceof CommandMapEntry){
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
			$this->unregisterAlias($lowerAlias);
			if(count($targets) > 0){
				$aliasInstance = new FormattedCommandAlias($lowerAlias, $targets);
				$registeredAliases = [];
				$this->mapAlias($lowerAlias, $aliasInstance, $registeredAliases);
				$this->uniqueCommands[spl_object_id($aliasInstance)] = new CommandMapEntry("pocketmine-config-defined", $aliasInstance, $registeredAliases);
			}
		}
	}
}
