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
use pocketmine\command\defaults\CommandAliasCommand;
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
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function array_filter;
use function array_map;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function is_string;
use function str_contains;
use function strcasecmp;
use function strtolower;
use function trim;

class SimpleCommandMap implements CommandMap{

	/**
	 * @var CommandMapEntry[]
	 * @phpstan-var array<string, CommandMapEntry>
	 */
	private array $uniqueCommands = [];

	private CommandAliasMap $aliasMap;

	public function __construct(private Server $server){
		$this->aliasMap = new CommandAliasMap();
		$this->setDefaultCommands();
	}

	private function setDefaultCommands() : void{
		$pmPrefix = "pocketmine";
		$this->register($pmPrefix, new BanCommand("ban"));
		$this->register($pmPrefix, new BanIpCommand("ban-ip"));
		$this->register($pmPrefix, new BanListCommand("banlist"));
		$this->register($pmPrefix, new ClearCommand("clear"));
		$this->register($pmPrefix, new CommandAliasCommand("cmdalias"));
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

		//TODO: inconsistency here with casing?
		$preferredAlias = trim($command->getName());
		$namespace = strtolower(trim($namespace));
		$commandId = "$namespace:$preferredAlias";
		if(isset($this->uniqueCommands[$commandId])){
			throw new \InvalidArgumentException("A command with ID $commandId has already been registered");
		}

		$this->aliasMap->bindAlias($commandId, $preferredAlias, override: false);
		foreach($otherAliases as $alias){
			$this->aliasMap->bindAlias($commandId, $alias, override: false);
		}

		$entry = new CommandMapEntry($namespace, $command);
		$this->uniqueCommands[$commandId] = $entry;

		return $entry;
	}

	public function unregister(Command $command) : bool{
		//ewwwww, command doesn't contain its own namespace :(
		//I suppose the same instance can be registered multiple times with different namespaces now too...
		foreach(Utils::stringifyKeys($this->uniqueCommands) as $commandId => $commandEntry){
			if($commandEntry->command === $command){
				unset($this->uniqueCommands[$commandId]);
				$this->aliasMap->unbindAliasesForCommand($commandId);
			}
		}

		return true;
	}

	public function dispatch(CommandSender $sender, string $commandLine) : bool{
		$args = CommandStringHelper::parseQuoteAware($commandLine);

		$sentCommandLabel = array_shift($args);
		if($sentCommandLabel !== null && ($target = $this->getEntry($sentCommandLabel, $sender->getCommandAliasMap())) !== null){
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
				$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage($sentCommandLabel))));
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
		$this->aliasMap = new CommandAliasMap();
		$this->uniqueCommands = [];
		$this->setDefaultCommands();
	}

	public function getEntry(string $name, ?CommandAliasMap $senderAliasMap = null) : CommandMapEntry|array|null{
		if(isset($this->uniqueCommands[$name])){ //direct command ID reference
			return $this->uniqueCommands[$name];
		}
		$commandId = $senderAliasMap?->resolveAlias($name) ?? $this->aliasMap->resolveAlias($name);
		if(is_string($commandId)){
			return $this->uniqueCommands[$commandId] ?? null;
		}
		if(is_array($commandId)){
			//the user's command map may refer to commands that are no longer registered, so we need to filter these
			//from the result set
			//we don't deconflict if there's only 1 command left because we don't want re-running a command to randomly
			//have a different result if the global command map was modified - the user can explicitly rebind the
			//alias in this case
			return array_filter(array_map(
				fn(string $c) => $this->uniqueCommands[$c] ?? null,
				$commandId
			), is_object(...));
		}
		return null;
	}

	/**
	 * @return CommandMapEntry[]
	 * @phpstan-return array<string, CommandMapEntry>
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
			if(count($targets) > 0){
				$aliasInstance = new FormattedCommandAlias($lowerAlias, $targets);
				$entry = new CommandMapEntry("pocketmine-config-defined", $aliasInstance);
				$this->aliasMap->bindAlias($entry->getNamespacedName(), $lowerAlias, override: true);
				$this->uniqueCommands[$entry->getNamespacedName()] = $entry;
			}else{
				//no targets blackholes the alias - this allows config to delete unwanted aliases
				$this->aliasMap->unbindAlias($lowerAlias);
			}
		}
	}

	public function getAliasMap() : CommandAliasMap{ return $this->aliasMap; }
}
