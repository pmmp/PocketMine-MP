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

namespace pocketmine\event\server;

use pocketmine\updater\AutoUpdater;

/**
 * Called when the AutoUpdater receives notification of an available PocketMine-MP update.
 * Plugins may use this event to perform actions when an update notification is received.
 */
class UpdateNotifyEvent extends ServerEvent{
	/** @var AutoUpdater */
	private $updater;

	public function __construct(AutoUpdater $updater){
		$this->updater = $updater;
	}

	public function getUpdater() : AutoUpdater{
		return $this->updater;
	}
}
