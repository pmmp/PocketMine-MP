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

namespace pocketmine\event\player;

use pocketmine\event\Event;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\Player;
use function is_a;

/**
 * Allows the creation of players overriding the base Player class
 */
class PlayerCreationEvent extends Event{

	/** @var NetworkSession */
	private $session;

	/**
	 * @var string
	 * @phpstan-var class-string<Player>
	 */
	private $baseClass = Player::class;
	/**
	 * @var string
	 * @phpstan-var class-string<Player>
	 */
	private $playerClass = Player::class;

	public function __construct(NetworkSession $session){
		$this->session = $session;
	}

	public function getNetworkSession() : NetworkSession{
		return $this->session;
	}

	public function getAddress() : string{
		return $this->session->getIp();
	}

	public function getPort() : int{
		return $this->session->getPort();
	}

	/**
	 * @return string
	 * @phpstan-return class-string<Player>
	 */
	public function getBaseClass(){
		return $this->baseClass;
	}

	/**
	 * @param string $class
	 * @phpstan-param class-string<Player> $class
	 */
	public function setBaseClass($class) : void{
		if(!is_a($class, $this->baseClass, true)){
			throw new \RuntimeException("Base class $class must extend " . $this->baseClass);
		}

		$this->baseClass = $class;
	}

	/**
	 * @return string
	 * @phpstan-return class-string<Player>
	 */
	public function getPlayerClass(){
		return $this->playerClass;
	}

	/**
	 * @param string $class
	 * @phpstan-param class-string<Player> $class
	 */
	public function setPlayerClass($class) : void{
		if(!is_a($class, $this->baseClass, true)){
			throw new \RuntimeException("Class $class must extend " . $this->baseClass);
		}

		$this->playerClass = $class;
	}
}
