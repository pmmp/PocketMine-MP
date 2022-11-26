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
use pocketmine\utils\Utils;
use function is_a;

/**
 * Allows the use of custom Player classes. This enables overriding built-in Player methods to change behaviour that is
 * not possible to alter any other way.
 *
 * You probably don't need this event, and found your way here because you looked at some code in an old plugin that
 * abused it (very common). Instead of using custom player classes, you should consider making session classes instead.
 *
 * @see https://github.com/pmmp/SessionsDemo
 *
 * This event is a power-user feature, and multiple plugins using it at the same time will conflict and break unless
 * they've been designed to work together. This means that it's only usually useful in private plugins.
 *
 * WARNING: This should NOT be used for adding extra functions or properties. This is intended for **overriding existing
 * core behaviour**, and should only be used if you know EXACTLY what you're doing.
 * Custom player classes may break in any update without warning. This event isn't much more than glorified reflection.
 */
class PlayerCreationEvent extends Event{

	/** @phpstan-var class-string<Player> */
	private string $baseClass = Player::class;
	/** @phpstan-var class-string<Player> */
	private string $playerClass = Player::class;

	public function __construct(private NetworkSession $session){}

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
	 * Returns the base class that the final player class must extend.
	 *
	 * @return string
	 * @phpstan-return class-string<Player>
	 */
	public function getBaseClass(){
		return $this->baseClass;
	}

	/**
	 * Sets the class that the final player class must extend.
	 * The new base class must be a subclass of the current base class.
	 * This can (perhaps) be used to limit the options for custom player classes provided by other plugins.
	 *
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
	 * Returns the class that will be instantiated to create the player after the event.
	 *
	 * @return string
	 * @phpstan-return class-string<Player>
	 */
	public function getPlayerClass(){
		return $this->playerClass;
	}

	/**
	 * Sets the class that will be instantiated to create the player after the event. The class must not be abstract,
	 * and must be an instance of the base class.
	 *
	 * @param string $class
	 * @phpstan-param class-string<Player> $class
	 */
	public function setPlayerClass($class) : void{
		Utils::testValidInstance($class, $this->baseClass);
		$this->playerClass = $class;
	}
}
