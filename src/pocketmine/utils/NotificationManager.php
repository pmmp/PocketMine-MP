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

use Logger;
use LogLevel;
use pocketmine\Server;
use function count;
use function fclose;
use function fgets;
use function fopen;
use function fwrite;
use function is_file;
use function trim;
use const PHP_EOL;

final class NotificationManager{
	/** @var string */
	private $path;

	/** @var bool */
	private $changed = false;
	/** @var true[] */
	private $set = [];

	private $queue = [];

	public function __construct(string $path){
		$this->path = $path;
		if(is_file($this->path)){
			$fh = fopen($this->path, "rb");
			while(($line = fgets($fh)) !== false){
				$this->set[trim($line)] = true;
			}
			fclose($fh);
		}
	}

	public function save() : void{
		$fh = fopen($this->path, "wb");
		foreach($this->set as $id => $_){
			fwrite($fh, $id . PHP_EOL);
		}
		fclose($fh);
	}


	public function post(string $id, string $message, string $logLevel = LogLevel::NOTICE) : void{
		if(!isset($this->set[$id])){
			$this->queue[$id] = [$logLevel, $message];
		}
	}

	public function print(Logger $logger) : void{
		if(empty($this->queue)){
			return;
		}

		$logger->notice(Server::getInstance()->getLanguage()->translateString("pocketmine.notification.header", [count($this->queue)]));
		foreach($this->queue as [$level, $message]){
			$logger->log($level, $message);
		}
		$logger->notice(Server::getInstance()->getLanguage()->translateString("pocketmine.notification.footer"));
	}

	public function markRead() : int{
		$cnt = count($this->set);
		foreach($this->queue as $id => $_){
			$this->set[$id] = true;
		}
		$this->queue = [];
		return $cnt;
	}
}
