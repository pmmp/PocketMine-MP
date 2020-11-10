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
use pocketmine\uuid\UUID;

/**
 * Encapsulates player info specific to players who are authenticated with XBOX Live.
 */
final class XboxLivePlayerInfo extends PlayerInfo{

	/** @var string */
	private $xuid;

	public function __construct(string $xuid, string $username, UUID $uuid, Skin $skin, string $locale, array $extraData = []){
		parent::__construct($username, $uuid, $skin, $locale, $extraData);
		$this->xuid = $xuid;
	}

	public function getXuid() : string{
		return $this->xuid;
	}

	/**
	 * Returns a new PlayerInfo with XBL player info stripped. This is used to ensure that non-XBL players can't spoof
	 * XBL data.
	 */
	public function withoutXboxData() : PlayerInfo{
		return new PlayerInfo(
			$this->getUsername(),
			$this->getUuid(),
			$this->getSkin(),
			$this->getLocale(),
			$this->getExtraData()
		);
	}
}
