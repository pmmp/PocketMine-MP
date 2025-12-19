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

use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\RawParameter;
use pocketmine\command\utils\CommandException;
use pocketmine\command\utils\CommandStringHelper;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\permission\PermissionManager;
use function explode;
use const PHP_INT_MAX;

/**
 * Offers a quick & dirty upgrade path for old code that can't be quickly migrated to the new overload system in PM6.
 * @deprecated
 */
abstract class LegacyCommand extends Command{

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	private array $permission = [];

	public function __construct(
		string $namespace,
		string $name,
		Translatable|string $description = "",
		Translatable|string|null $usageMessage = null,
	){
		parent::__construct($namespace, $name, OverloadBuilder::single(
			[new RawParameter("args", "args")],
			DefaultPermissionNames::GROUP_USER,
			$this->handler(...),
			acceptsAliasUsed: true,
			customUsageMessage: $usageMessage
		), $description);
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getPermissions() : array{
		return $this->permission;
	}

	/**
	 * @param string[] $permissions
	 * @phpstan-param list<string> $permissions
	 */
	public function setPermissions(array $permissions) : void{
		$permissionManager = PermissionManager::getInstance();
		foreach($permissions as $perm){
			if($permissionManager->getPermission($perm) === null){
				throw new \InvalidArgumentException("Cannot use non-existing permission \"$perm\"");
			}
		}
		$this->permission = $permissions;
	}

	public function setPermission(?string $permission) : void{
		$this->setPermissions($permission === null ? [] : explode(";", $permission, limit: PHP_INT_MAX));
	}

	/**
	 * @param string        $context    usually the command name, but may include extra args if useful (e.g. for subcommands)
	 * @param CommandSender $target     the target to check the permission for
	 * @param string|null   $permission the permission to check, if null, will check if the target has any of the command's permissions
	 */
	public function testPermission(string $context, CommandSender $target, ?string $permission = null) : bool{
		if($this->testPermissionSilent($target, $permission)){
			return true;
		}

		$this->sendBadPermissionMessage($context, $target, $permission !== null ? [$permission] : $this->permission);

		return false;
	}

	public function testPermissionSilent(CommandSender $target, ?string $permission = null) : bool{
		$list = $permission !== null ? [$permission] : $this->permission;
		foreach($list as $p){
			if($target->hasPermission($p)){
				return true;
			}
		}

		return false;
	}

	private function handler(CommandSender $sender, string $aliasUsed, string $rawArgs = "") : void{
		if(!$this->testPermission($aliasUsed, $sender)){
			return;
		}
		$args = CommandStringHelper::parseQuoteAware($rawArgs);
		$this->execute($sender, $aliasUsed, $args);
	}

	/**
	 * @param string[] $args
	 * @phpstan-param list<string> $args
	 *
	 * @return mixed
	 * @throws CommandException
	 */
	abstract protected function execute(CommandSender $sender, string $commandLabel, array $args);
}
