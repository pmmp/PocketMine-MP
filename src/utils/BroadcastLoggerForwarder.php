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

namespace pocketmine\utils;

use pocketmine\ChatBroadcastSubscriber;
use pocketmine\lang\Language;
use pocketmine\lang\Translatable;

/**
 * Forwards any messages it receives via sendMessage() to the given logger. Used for forwarding chat messages and
 * command audit log messages to the server log file.
 */
final class BroadcastLoggerForwarder implements ChatBroadcastSubscriber{

	public function __construct(
		private \Logger $logger,
		private Language $language
	){}

	public function onBroadcast(string $channelId, Translatable|string $message) : void{
		if($message instanceof Translatable){
			$this->logger->info($this->language->translate($message));
		}else{
			$this->logger->info($message);
		}
	}
}
