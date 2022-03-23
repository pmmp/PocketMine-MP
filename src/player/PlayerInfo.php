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
use Ramsey\Uuid\UuidInterface;

/**
 * Encapsulates data needed to create a player.
 */
class PlayerInfo{

	/** @var string */
	private $username;
	/** @var string */
	private $deviceId;
	/** @var UuidInterface */
	private $uuid;
	/** @var Skin */
	private $skin;
	/** @var string */
	private $locale;
	/**
	 * @var array
	 * @phpstan-var array<string, mixed>
	 */
	private array $extraData;

	/**
	 * @param string        $username
	 * @param string        $deviceId
	 * @param UuidInterface $uuid
	 * @param Skin          $skin
	 * @param string        $locale
	 * @param array         $extraData
	 */
	public function __construct(string $username, string $deviceId, UuidInterface $uuid, Skin $skin, string $locale, array $extraData = []){
		$this->username = TextFormat::clean($username);
		$this->deviceId = $deviceId;
		$this->uuid = $uuid;
		$this->skin = $skin;
		$this->locale = $locale;
		$this->extraData = $extraData;
	}

	/**
	 * @return string
	 */
	public function getDeviceId() : string{
		return $this->deviceId;
	}

	public function getUsername() : string{
		return $this->username;
	}

	public function getUuid() : UuidInterface{
		return $this->uuid;
	}

	public function getSkin() : Skin{
		return $this->skin;
	}

	public function getLocale() : string{
		return $this->locale;
	}

	/**
	 * @return string[]
	 */
	public function getLocaleArray() : array{
		return explode("_",$this->locale);
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public function getExtraData() : array{
		return $this->extraData;
	}
}
