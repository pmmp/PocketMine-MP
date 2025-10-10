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

use function array_filter;
use function array_key_first;
use function array_keys;
use function array_values;
use function count;
use function is_array;

final class CommandAliasMap{

	/**
	 * @var string[]
	 * @phpstan-var array<string, string|list<string>>
	 */
	private array $aliasToCommandMap = [];

	/**
	 * @var string[][]
	 * @phpstan-var array<string, array<string, true>>
	 */
	private array $commandToAliasesMap = [];

	private function mapSingleAlias(string $commandId, string $alias) : bool{
		$existing = $this->aliasToCommandMap[$alias] ?? null;
		if($existing !== null){
			if(!is_array($existing)){
				//old command can't use this alias anymore, since it's conflicted
				unset($this->commandToAliasesMap[$existing][$alias]);
				$existing = [$existing];
			}
			$existing[] = $commandId;
			$this->aliasToCommandMap[$alias] = $existing;
			return false;
		}

		$this->commandToAliasesMap[$commandId][$alias] = true;
		$this->aliasToCommandMap[$alias] = $commandId;
		return true;
	}

	public function bindAlias(string $commandId, string $newAlias, bool $override) : void{
		if($override){
			//explicit alias registration overrides everything else, including conflicts
			$this->unbindAlias($newAlias);
		}

		$this->mapSingleAlias($commandId, $newAlias);
	}

	public function unbindAlias(string $alias) : bool{
		$commandIds = $this->aliasToCommandMap[$alias] ?? null;
		if($commandIds === null){
			return false;
		}
		unset($this->aliasToCommandMap[$alias]);
		if(!is_array($commandIds)){
			//this should only be set if the alias wasn't conflicted
			unset($this->commandToAliasesMap[$commandIds][$alias]);
		}

		return true;
	}

	public function unbindAliasesForCommand(string $commandId) : void{
		foreach($this->getAliases($commandId) as $alias){
			$aliasMap = $this->aliasToCommandMap[$alias] ?? null;

			if($aliasMap === $commandId){
				unset($this->aliasToCommandMap[$alias]);
			}elseif(is_array($aliasMap)){
				//this may leave 1 command remaining - we purposely don't deconflict it here because successive command
				//invocations should be predictable. Leave the conflict and let the user override it if they want.
				$replacement = array_filter($aliasMap, fn(string $cid) => $cid !== $commandId);
				if(count($replacement) === 0){
					unset($this->aliasToCommandMap[$alias]);
				}else{
					$this->aliasToCommandMap[$alias] = array_values($replacement);
				}
			}else{
				throw new \LogicException("Alias map state corrupted");
			}
		}
	}

	/**
	 * Returns all (non-conflicted) aliases for the command.
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getAliases(string $commandId) : array{
		return array_keys($this->commandToAliasesMap[$commandId] ?? []);
	}

	/**
	 * Returns the ID of the command bound to the given alias.
	 * If there are conflicting commands bound, an array of all the bound command IDs will be returned.
	 * If the alias is not bound, null will be returned.
	 *
	 * @return string|string[]|null
	 * @phpstan-return string|list<string>|null
	 */
	public function resolveAlias(string $alias) : string|array|null{
		return $this->aliasToCommandMap[$alias] ?? null;
	}

	/**
	 * Returns a list of all the names a command could be invoked by.
	 * Aliases from this map override the fallback map.
	 * The command ID is also included, since that can always be used to invoke a command.
	 *
	 * @return string[]
	 * @phpstan-return non-empty-list<string>
	 */
	public function getMergedAliases(string $commandId, CommandAliasMap $fallbackMap) : array{
		$localAliases = $this->getAliases($commandId);

		foreach($fallbackMap->getAliases($commandId) as $globalAlias){
			$userMappedCommandId = $this->resolveAlias($globalAlias);
			if($userMappedCommandId === null){
				//only include if this map doesn't have this alias at all
				$localAliases[] = $globalAlias;
			}
		}

		$localAliases[] = $commandId;

		return $localAliases;
	}

	/**
	 * Returns a preferred alias for the given command ID. Used for displaying errors and help tips.
	 * For example, the user might not have /help bound, but might have a different alias for it that's more convenient
	 * than /pocketmine:help.
	 */
	public function getPreferredAlias(string $commandId, CommandAliasMap $fallbackMap) : string{
		$aliasList = $this->getMergedAliases($commandId, $fallbackMap);
		return $aliasList[array_key_first($aliasList)];
	}

	/**
	 * @return string[]|string[][]
	 * @phpstan-return array<string, string|list<string>>
	 */
	public function getAllAliases() : array{
		return $this->aliasToCommandMap;
	}
}
