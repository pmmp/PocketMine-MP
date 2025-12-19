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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function array_filter;
use function array_map;
use function array_shift;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function ltrim;
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
		$this->register(BanCommand::create($pmPrefix, "ban"));
		$this->register(BanIpCommand::create($pmPrefix, "ban-ip"));
		$this->register(BanListCommand::create($pmPrefix, "banlist"));
		$this->register(ClearCommand::create($pmPrefix, "clear"));
		$this->register(CommandAliasCommand::create($pmPrefix, "cmdalias"));
		$this->register(DefaultGamemodeCommand::create($pmPrefix, "defaultgamemode"));
		$this->register(DeopCommand::create($pmPrefix, "deop"));
		$this->register(DifficultyCommand::create($pmPrefix, "difficulty"));
		$this->register(DumpMemoryCommand::create($pmPrefix, "dumpmemory"));
		$this->register(EffectCommand::create($pmPrefix, "effect"));
		$this->register(EnchantCommand::create($pmPrefix, "enchant"));
		$this->register(GamemodeCommand::create($pmPrefix, "gamemode"));
		$this->register(GarbageCollectorCommand::create($pmPrefix, "gc"));
		$this->register(GiveCommand::create($pmPrefix, "give"));
		$this->register(HelpCommand::create($pmPrefix, "help"), ["?"]);
		$this->register(KickCommand::create($pmPrefix, "kick"));
		$this->register(KillCommand::create($pmPrefix, "kill"), ["suicide"]);
		$this->register(ListCommand::create($pmPrefix, "list"));
		$this->register(MeCommand::create($pmPrefix, "me"));
		$this->register(OpCommand::create($pmPrefix, "op"));
		$this->register(PardonCommand::create($pmPrefix, "pardon"), ["unban"]);
		$this->register(PardonIpCommand::create($pmPrefix, "pardon-ip"), ["unban-ip"]);
		$this->register(ParticleCommand::create($pmPrefix, "particle"));
		$this->register(PluginsCommand::create($pmPrefix, "plugins"), ["pl"]);
		$this->register(SaveCommand::create($pmPrefix, "save-all"));
		$this->register(SaveOffCommand::create($pmPrefix, "save-off"));
		$this->register(SaveOnCommand::create($pmPrefix, "save-on"));
		$this->register(SayCommand::create($pmPrefix, "say"));
		$this->register(SeedCommand::create($pmPrefix, "seed"));
		$this->register(SetWorldSpawnCommand::create($pmPrefix, "setworldspawn"));
		$this->register(SpawnpointCommand::create($pmPrefix, "spawnpoint"));
		$this->register(StatusCommand::create($pmPrefix, "status"));
		$this->register(StopCommand::create($pmPrefix, "stop"));
		$this->register(TeleportCommand::create($pmPrefix, "tp"), ["teleport"]);
		$this->register(TellCommand::create($pmPrefix, "tell"), ["w", "msg"]);
		$this->register(TimeCommand::create($pmPrefix, "time"));
		$this->register(TimingsCommand::create($pmPrefix, "timings"));
		$this->register(TitleCommand::create($pmPrefix, "title"));
		$this->register(TransferServerCommand::create($pmPrefix, "transferserver"));
		$this->register(VersionCommand::create($pmPrefix, "version"), ["ver", "about"]);
		$this->register(WhitelistCommand::create($pmPrefix, "whitelist"));
		$this->register(XpCommand::create($pmPrefix, "xp"));
	}

	public function register(Command $command, array $otherAliases = []) : void{
		if($command instanceof LegacyCommand && count($command->getPermissions()) === 0){
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
		$parts = explode(" ", ltrim($commandLine), limit: 2);
		[$sentCommandLabel, $rawArgs] = count($parts) === 2 ? $parts : [$parts[0], ""];

		if(($target = $this->getCommand($sentCommandLabel, $sender->getCommandAliasMap())) !== null){
			if(is_array($target)){
				self::handleConflicted($sender, $sentCommandLabel, $target, $this->aliasMap);
				return true;
			}
			$timings = Timings::getCommandDispatchTimings($target->getId());
			$timings->startTiming();

			try{
				$target->executeOverloaded($sender, $sentCommandLabel, $rawArgs);
			}finally{
				$timings->stopTiming();
			}
			return true;
		}

		//Don't love hardcoding the command ID here, but it seems like the only way for now
		$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound(
			$sentCommandLabel,
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
			if(count($c->getPermittedOverloads($sender)) > 0){
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
				//TODO: HACK HACK HACK - We really should declare permissions for each custom command declared
				//Previously we just weren't declaring a permission at all, but that's no longer possible with the new overload system
				$aliasInstance = FormattedCommandAlias::create("pocketmine-config-defined", $lowerAlias, DefaultPermissionNames::GROUP_USER, $targets);

				$this->register($aliasInstance);
				$this->aliasMap->bindAlias($aliasInstance->getId(), $lowerAlias, override: true);
			}else{
				//no targets blackholes the alias - this allows config to delete unwanted aliases
				$this->aliasMap->unbindAlias($lowerAlias);
			}
		}
	}

	public function getAliasMap() : CommandAliasMap{ return $this->aliasMap; }
}
