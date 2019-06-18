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

namespace pocketmine\player;

use pocketmine\entity\Skin;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;

/**
 * Encapsulates data needed to create a player.
 */
class PlayerInfo{

	/** @var string */
	private $username;
	/** @var UUID */
	private $uuid;
	/** @var Skin */
	private $skin;
	/** @var string */
	private $locale;
	/** @var string */
	private $xuid;
	/** @var int */
	private $clientId;

	public function __construct(string $username, UUID $uuid, Skin $skin, string $locale, string $xuid, int $clientId){
		$this->username = TextFormat::clean($username);
		$this->uuid = $uuid;
		$this->skin = $skin;
		$this->locale = $locale;
		$this->xuid = $xuid;
		$this->clientId = $clientId;
	}

	/**
	 * @return string
	 */
	public function getUsername() : string{
		return $this->username;
	}

	/**
	 * @return UUID
	 */
	public function getUuid() : UUID{
		return $this->uuid;
	}

	/**
	 * @return Skin
	 */
	public function getSkin() : Skin{
		return $this->skin;
	}

	/**
	 * @return string
	 */
	public function getLocale() : string{
		return $this->locale;
	}

	/**
	 * @return string
	 */
	public function getXuid() : string{
		return $this->xuid;
	}

	/**
	 * @return int
	 */
	public function getClientId() : int{
		return $this->clientId;
	}
}
