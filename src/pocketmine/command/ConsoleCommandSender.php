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

use pocketmine\lang\TextContainer;
use pocketmine\permission\PermissibleTrait;
use pocketmine\Server;
use pocketmine\utils\MainLogger;

class ConsoleCommandSender implements CommandSender{
	use PermissibleTrait;

	/** @var int|null */
	protected $lineHeight = null;

	public function __construct(){
		$this->initPermissible();
	}

	/**
	 * @return Server
	 */
	public function getServer(){
		return Server::getInstance();
	}

	/**
	 * @param TextContainer|string $message
	 */
	public function sendMessage($message){
		if($message instanceof TextContainer){
			$message = $this->getServer()->getLanguage()->translate($message);
		}else{
			$message = $this->getServer()->getLanguage()->translateString($message);
		}

		foreach(explode("\n", trim($message)) as $line){
			MainLogger::getLogger()->info($line);
		}
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "CONSOLE";
	}

	/**
	 * @return bool
	 */
	public function isOp() : bool{
		return true;
	}

	/**
	 * @param bool $value
	 */
	public function setOp(bool $value){

	}

	public function getScreenLineHeight() : int{
		return $this->lineHeight ?? PHP_INT_MAX;
	}

	public function setScreenLineHeight(int $height = null){
		if($height !== null and $height < 1){
			throw new \InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}
}
