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

use pocketmine\lang\Language;
use pocketmine\lang\TranslationContainer;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\Server;
use function explode;
use function trim;
use const PHP_INT_MAX;

class ConsoleCommandSender implements CommandSender{
	use PermissibleDelegateTrait;

	/** @var Server */
	private $server;
	/** @var int|null */
	protected $lineHeight = null;
	/** @var Language */
	private $language;

	public function __construct(Server $server, Language $language){
		$this->server = $server;
		$this->perm = new PermissibleBase([DefaultPermissions::ROOT_CONSOLE => true]);
		$this->language = $language;
	}

	public function getServer() : Server{
		return $this->server;
	}

	public function getLanguage() : Language{
		return $this->language;
	}

	/**
	 * @param TranslationContainer|string $message
	 */
	public function sendMessage($message) : void{
		$server = $this->getServer();
		if($message instanceof TranslationContainer){
			$message = $this->getLanguage()->translate($message);
		}else{
			$message = $this->getLanguage()->translateString($message);
		}

		foreach(explode("\n", trim($message)) as $line){
			$server->getLogger()->info($line);
		}
	}

	public function getName() : string{
		return "CONSOLE";
	}

	public function getScreenLineHeight() : int{
		return $this->lineHeight ?? PHP_INT_MAX;
	}

	public function setScreenLineHeight(?int $height) : void{
		if($height !== null and $height < 1){
			throw new \InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}

	public function __destruct(){
		//permission subscriptions need to be cleaned up explicitly
		$this->perm->destroyCycles();
	}
}
