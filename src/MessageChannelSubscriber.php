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

namespace pocketmine;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\Permissible;

/**
 * This interface can be implemented in order to receive messages from the server's global broadcast channels.
 */
interface MessageChannelSubscriber{

	/**
	 * Called when a message is broadcasted on any channel that this receiver is subscribed to.
	 *
	 * @see Server::subscribeToBroadcastChannel()
	 */
	public function onMessage(string $channelId, CommandSender $source, Translatable|string $message) : void;

	/**
	 * Used to check if the subscriber is allowed to receive messages from channels with permission restrictions.
	 * If this function returns null, the subscriber will receive all messages regardless of permissions.
	 */
	public function getPermissible() : ?Permissible;
}
