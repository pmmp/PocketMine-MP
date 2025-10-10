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

/**
 * Command handling related classes
 */
namespace pocketmine\command;

use pocketmine\command\utils\CommandException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;
use pocketmine\utils\BroadcastLoggerForwarder;
use pocketmine\utils\TextFormat;
use function explode;
use function implode;
use function str_replace;
use function strtolower;
use function trim;
use const PHP_INT_MAX;

abstract class Command{
	private readonly string $namespace;
	private readonly string $name;

	/** @var string[] */
	private array $permission = [];
	private Translatable|string|null $permissionMessage = null;

	public function __construct(
		string $namespace,
		string $name,
		private Translatable|string $description = "",
		private Translatable|string|null $usageMessage = null
	){
		if($namespace === ""){
			throw new \InvalidArgumentException("Command namespace cannot be empty (set it to, for example, your plugin's name)");
		}
		if($name === ""){
			throw new \InvalidArgumentException("Command name cannot be empty");
		}
		$this->namespace = strtolower(trim($namespace));
		//TODO: case handling inconsistency preserved from old code
		$this->name = trim($name);
	}

	/**
	 * @param string[] $args
	 * @phpstan-param list<string> $args
	 *
	 * @return mixed
	 * @throws CommandException
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args);

	final public function getNamespace() : string{
		return $this->namespace;
	}

	/**
	 * Returns the local identifier of the command (without namespace or leading slash).
	 * This cannot be changed after creation.
	 */
	final public function getName() : string{
		return $this->name;
	}

	/**
	 * Returns the globally unique ID for the command. This typically looks like namespace:name
	 */
	final public function getId() : string{
		return "$this->namespace:$this->name";
	}

	/**
	 * @return string[]
	 */
	public function getPermissions() : array{
		return $this->permission;
	}

	/**
	 * @param string[] $permissions
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

		$message = $this->permissionMessage ?? KnownTranslationFactory::pocketmine_command_error_permission($context);
		if($message instanceof Translatable){
			$target->sendMessage($message->prefix(TextFormat::RED));
		}elseif($message !== ""){
			$target->sendMessage(str_replace("<permission>", $permission ?? implode(";", $this->permission), $message));
		}

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

	public function getPermissionMessage() : Translatable|string|null{
		return $this->permissionMessage;
	}

	public function getDescription() : Translatable|string{
		return $this->description;
	}

	public function getUsage() : Translatable|string|null{
		return $this->usageMessage;
	}

	public function setDescription(Translatable|string $description) : void{
		$this->description = $description;
	}

	public function setPermissionMessage(Translatable|string $permissionMessage) : void{
		$this->permissionMessage = $permissionMessage;
	}

	public function setUsage(Translatable|string|null $usage) : void{
		$this->usageMessage = $usage;
	}

	public static function broadcastCommandMessage(CommandSender $source, Translatable|string $message, bool $sendToSource = true) : void{
		$users = $source->getServer()->getBroadcastChannelSubscribers(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
		$result = KnownTranslationFactory::chat_type_admin($source->getName(), $message);
		$colored = $result->prefix(TextFormat::GRAY . TextFormat::ITALIC);

		if($sendToSource){
			$source->sendMessage($message);
		}

		foreach($users as $user){
			if($user instanceof BroadcastLoggerForwarder){
				$user->sendMessage($result);
			}elseif($user !== $source){
				$user->sendMessage($colored);
			}
		}
	}
}
