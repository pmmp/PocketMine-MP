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

use pocketmine\lang\TextContainer;
use pocketmine\lang\TranslationContainer;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\TextFormat;
use function explode;
use function str_replace;

abstract class Command{

	/** @var string */
	private $name;

	/** @var string */
	private $nextLabel;

	/** @var string */
	private $label;

	/** @var string[] */
	private $aliases = [];

	/**
	 * @var string[]
	 */
	private $activeAliases = [];

	/** @var CommandMap */
	private $commandMap = null;

	/** @var string */
	protected $description = "";

	/** @var string */
	protected $usageMessage;

	/** @var string|null */
	private $permission = null;

	/** @var string */
	private $permissionMessage = null;

	/** @var TimingsHandler */
	public $timings;

	/**
	 * @param string   $name
	 * @param string   $description
	 * @param string   $usageMessage
	 * @param string[] $aliases
	 */
	public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = []){
		$this->name = $name;
		$this->setLabel($name);
		$this->setDescription($description);
		$this->usageMessage = $usageMessage ?? ("/" . $name);
		$this->setAliases($aliases);
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param string[]      $args
	 *
	 * @return mixed
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args);

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getPermission(){
		return $this->permission;
	}


	/**
	 * @param string|null $permission
	 */
	public function setPermission(string $permission = null){
		$this->permission = $permission;
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function testPermission(CommandSender $target) : bool{
		if($this->testPermissionSilent($target)){
			return true;
		}

		if($this->permissionMessage === null){
			$target->sendMessage($target->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));
		}elseif($this->permissionMessage !== ""){
			$target->sendMessage(str_replace("<permission>", $this->permission, $this->permissionMessage));
		}

		return false;
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function testPermissionSilent(CommandSender $target) : bool{
		if($this->permission === null or $this->permission === ""){
			return true;
		}

		foreach(explode(";", $this->permission) as $permission){
			if($target->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getLabel() : string{
		return $this->label;
	}

	public function setLabel(string $name) : bool{
		$this->nextLabel = $name;
		if(!$this->isRegistered()){
			if($this->timings instanceof TimingsHandler){
				$this->timings->remove();
			}
			$this->timings = new TimingsHandler("** Command: " . $name);
			$this->label = $name;

			return true;
		}

		return false;
	}

	/**
	 * Registers the command into a Command map
	 *
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function register(CommandMap $commandMap) : bool{
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function unregister(CommandMap $commandMap) : bool{
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = null;
			$this->activeAliases = $this->aliases;
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	private function allowChangesFrom(CommandMap $commandMap) : bool{
		return $this->commandMap === null or $this->commandMap === $commandMap;
	}

	/**
	 * @return bool
	 */
	public function isRegistered() : bool{
		return $this->commandMap !== null;
	}

	/**
	 * @return string[]
	 */
	public function getAliases() : array{
		return $this->activeAliases;
	}

	/**
	 * @return string|null
	 */
	public function getPermissionMessage() : ?string{
		return $this->permissionMessage;
	}

	/**
	 * @return string
	 */
	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getUsage() : string{
		return $this->usageMessage;
	}

	/**
	 * @param string[] $aliases
	 */
	public function setAliases(array $aliases){
		$this->aliases = $aliases;
		if(!$this->isRegistered()){
			$this->activeAliases = $aliases;
		}
	}

	/**
	 * @param string $description
	 */
	public function setDescription(string $description){
		$this->description = $description;
	}

	/**
	 * @param string $permissionMessage
	 */
	public function setPermissionMessage(string $permissionMessage){
		$this->permissionMessage = $permissionMessage;
	}

	/**
	 * @param string $usage
	 */
	public function setUsage(string $usage){
		$this->usageMessage = $usage;
	}

	/**
	 * @param CommandSender        $source
	 * @param TextContainer|string $message
	 * @param bool                 $sendToSource
	 */
	public static function broadcastCommandMessage(CommandSender $source, $message, bool $sendToSource = true){
		if($message instanceof TextContainer){
			$m = clone $message;
			$result = "[" . $source->getName() . ": " . ($source->getServer()->getLanguage()->get($m->getText()) !== $m->getText() ? "%" : "") . $m->getText() . "]";

			$users = PermissionManager::getInstance()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . $result;

			$m->setText($result);
			$result = clone $m;
			$m->setText($colored);
			$colored = clone $m;
		}else{
			$users = PermissionManager::getInstance()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$result = new TranslationContainer("chat.type.admin", [$source->getName(), $message]);
			$colored = new TranslationContainer(TextFormat::GRAY . TextFormat::ITALIC . "%chat.type.admin", [$source->getName(), $message]);
		}

		if($sendToSource and !($source instanceof ConsoleCommandSender)){
			$source->sendMessage($message);
		}

		foreach($users as $user){
			if($user instanceof CommandSender){
				if($user instanceof ConsoleCommandSender){
					$user->sendMessage($result);
				}elseif($user !== $source){
					$user->sendMessage($colored);
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString() : string{
		return $this->name;
	}
}
