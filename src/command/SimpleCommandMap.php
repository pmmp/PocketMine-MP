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
	 * @var Command[]
	 * @phpstan-var array<string, Command>
	 */
	private array $uniqueCommands = [];

	private CommandAliasMap $aliasMap;

	public function __construct(private Server $server){
		$this->aliasMap = new CommandAliasMap();
		$this->setDefaultCommands();
	}

	private function setDefaultCommands() : void{
		$pmPrefix = "pocketmine";
		$this->register(new BanCommand($pmPrefix, "ban"));
		$this->register(new BanIpCommand($pmPrefix, "ban-ip"));
		$this->register(new BanListCommand($pmPrefix, "banlist"));
		$this->register(new ClearCommand($pmPrefix, "clear"));
		$this->register(new CommandAliasCommand($pmPrefix, "cmdalias"));
		$this->register(new DefaultGamemodeCommand($pmPrefix, "defaultgamemode"));
		$this->register(new DeopCommand($pmPrefix, "deop"));
		$this->register(new DifficultyCommand($pmPrefix, "difficulty"));
		$this->register(new DumpMemoryCommand($pmPrefix, "dumpmemory"));
		$this->register(new EffectCommand($pmPrefix, "effect"));
		$this->register(new EnchantCommand($pmPrefix, "enchant"));
		$this->register(new GamemodeCommand($pmPrefix, "gamemode"));
		$this->register(new GarbageCollectorCommand($pmPrefix, "gc"));
		$this->register(new GiveCommand($pmPrefix, "give"));
		$this->register(new HelpCommand($pmPrefix, "help"), ["?"]);
		$this->register(new KickCommand($pmPrefix, "kick"));
		$this->register(new KillCommand($pmPrefix, "kill"), ["suicide"]);
		$this->register(new ListCommand($pmPrefix, "list"));
		$this->register(new MeCommand($pmPrefix, "me"));
		$this->register(new OpCommand($pmPrefix, "op"));
		$this->register(new PardonCommand($pmPrefix, "pardon"), ["unban"]);
		$this->register(new PardonIpCommand($pmPrefix, "pardon-ip"), ["unban-ip"]);
		$this->register(new ParticleCommand($pmPrefix, "particle"));
		$this->register(new PluginsCommand($pmPrefix, "plugins"), ["pl"]);
		$this->register(new SaveCommand($pmPrefix, "save-all"));
		$this->register(new SaveOffCommand($pmPrefix, "save-off"));
		$this->register(new SaveOnCommand($pmPrefix, "save-on"));
		$this->register(new SayCommand($pmPrefix, "say"));
		$this->register(new SeedCommand($pmPrefix, "seed"));
		$this->register(new SetWorldSpawnCommand($pmPrefix, "setworldspawn"));
		$this->register(new SpawnpointCommand($pmPrefix, "spawnpoint"));
		$this->register(new StatusCommand($pmPrefix, "status"));
		$this->register(new StopCommand($pmPrefix, "stop"));
		$this->register(new TeleportCommand($pmPrefix, "tp"), ["teleport"]);
		$this->register(new TellCommand($pmPrefix, "tell"), ["w", "msg"]);
		$this->register(new TimeCommand($pmPrefix, "time"));
		$this->register(new TimingsCommand($pmPrefix, "timings"));
		$this->register(new TitleCommand($pmPrefix, "title"));
		$this->register(new TransferServerCommand($pmPrefix, "transferserver"));
		$this->register(new VersionCommand($pmPrefix, "version"), ["ver", "about"]);
		$this->register(new WhitelistCommand($pmPrefix, "whitelist"));
		$this->register(new XpCommand($pmPrefix, "xp"));
	}

	public function register(Command $command, array $otherAliases = []) : void{
		if(count($command->getPermissions()) === 0){
			throw new \InvalidArgumentException("Commands must have a permission set");
		}

		$commandId = $command->getId();
		if(isset($this->uniqueCommands[$commandId])){
			throw new \InvalidArgumentException("A command with ID $commandId has already been registered");
		}

		$preferredAlias = trim($command->getName());
		$this->aliasMap->bindAlias($commandId, $preferredAlias, override: false);
		foreach($otherAliases as $alias){
			$this->aliasMap->bindAlias($commandId, $alias, override: false);
		}

		$this->uniqueCommands[$commandId] = $command;
	}

	public function unregister(Command $command) : bool{
		unset($this->uniqueCommands[$command->getId()]);
		$this->aliasMap->unbindAliasesForCommand($command->getId());

		return true;
	}

	public function dispatch(CommandSender $sender, string $commandLine) : bool{
		$args = CommandStringHelper::parseQuoteAware($commandLine);

		$sentCommandLabel = array_shift($args);
		if($sentCommandLabel !== null && ($target = $this->getCommand($sentCommandLabel, $sender->getCommandAliasMap())) !== null){
			if(is_array($target)){
				self::handleConflicted($sender, $sentCommandLabel, $target, $this->aliasMap);
				return true;
			}
			$timings = Timings::getCommandDispatchTimings($target->getId());
			$timings->startTiming();

			try{
				if($target->testPermission($sentCommandLabel, $sender)){
					$target->execute($sender, $sentCommandLabel, $args);
				}
			}catch(InvalidCommandSyntaxException $e){
				//TODO: localised command message should use user-provided alias, it shouldn't be hard-baked into the language strings
				$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage() ?? "/$sentCommandLabel")));
			}finally{
				$timings->stopTiming();
			}
			return true;
		}

		//Don't love hardcoding the command ID here, but it seems like the only way for now
		$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound(
			$sentCommandLabel ?? "",
			"/" . $sender->getCommandAliasMap()->getPreferredAlias("pocketmine:help", $this->aliasMap)
		)->prefix(TextFormat::RED));
		return false;
	}

	/**
	 * TODO: probably need to find a better place to put this
	 * @internal
	 * @param Command[] $conflictedEntries
	 * @phpstan-param array<int, Command> $conflictedEntries
	 */
	public static function handleConflicted(CommandSender $sender, string $alias, array $conflictedEntries, CommandAliasMap $fallbackAliasMap) : void{
		$candidates = [];
		$userAliasMap = $sender->getCommandAliasMap();
		foreach($conflictedEntries as $c){
			if($c->testPermissionSilent($sender)){
				$candidates[] = "/" . $c->getId();
			}
		}
		if(count($candidates) > 0){
			//there might only be 1 permissible command here, but we still don't auto-select in this case
			//because it might cause surprising behaviour if the user's permissions change between command
			//invocations. Better to force them to use an unambiguous alias in all cases.
			$candidateNames = implode(", ", $candidates);
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_aliasConflict("/$alias", $candidateNames)->prefix(TextFormat::RED));
			//Don't love hardcoding the command ID here, but it seems like the only way for now
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_aliasConflictTip(
				"/" . $userAliasMap->getPreferredAlias("pocketmine:cmdalias", $fallbackAliasMap)
			)->prefix(TextFormat::RED));
		}else{
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_permission($alias)->prefix(TextFormat::RED));
		}
	}

	public function clearCommands() : void{
		$this->aliasMap = new CommandAliasMap();
		$this->uniqueCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand(string $name, ?CommandAliasMap $senderAliasMap = null) : Command|array|null{
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
	 * @return Command[]
	 * @phpstan-return array<string, Command>
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
				$command = $this->getCommand($commandName);

				if(!$command instanceof Command){
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
				$aliasInstance = new FormattedCommandAlias("pocketmine-config-defined", $lowerAlias, $targets);
				$this->aliasMap->bindAlias($aliasInstance->getId(), $lowerAlias, override: true);
				$this->uniqueCommands[$aliasInstance->getId()] = $aliasInstance;
			}else{
				//no targets blackholes the alias - this allows config to delete unwanted aliases
				$this->aliasMap->unbindAlias($lowerAlias);
			}
		}
	}

	public function getAliasMap() : CommandAliasMap{ return $this->aliasMap; }
}
