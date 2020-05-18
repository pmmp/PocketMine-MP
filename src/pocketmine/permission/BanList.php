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

namespace pocketmine\permission;

use pocketmine\Server;
use pocketmine\utils\MainLogger;
use function fclose;
use function fgets;
use function fopen;
use function fwrite;
use function is_resource;
use function strftime;
use function strtolower;
use function time;

class BanList{

	/** @var BanEntry[] */
	private $list = [];

	/** @var string */
	private $file;

	/** @var bool */
	private $enabled = true;

	public function __construct(string $file){
		$this->file = $file;
	}

	public function isEnabled() : bool{
		return $this->enabled;
	}

	/**
	 * @return void
	 */
	public function setEnabled(bool $flag){
		$this->enabled = $flag;
	}

	public function getEntry(string $name) : ?BanEntry{
		$this->removeExpired();

		return $this->list[strtolower($name)] ?? null;
	}

	/**
	 * @return BanEntry[]
	 */
	public function getEntries() : array{
		$this->removeExpired();

		return $this->list;
	}

	public function isBanned(string $name) : bool{
		$name = strtolower($name);
		if(!$this->isEnabled()){
			return false;
		}else{
			$this->removeExpired();

			return isset($this->list[$name]);
		}
	}

	/**
	 * @return void
	 */
	public function add(BanEntry $entry){
		$this->list[$entry->getName()] = $entry;
		$this->save();
	}

	public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry{
		$entry = new BanEntry($target);
		$entry->setSource($source ?? $entry->getSource());
		$entry->setExpires($expires);
		$entry->setReason($reason ?? $entry->getReason());

		$this->list[$entry->getName()] = $entry;
		$this->save();

		return $entry;
	}

	/**
	 * @return void
	 */
	public function remove(string $name){
		$name = strtolower($name);
		if(isset($this->list[$name])){
			unset($this->list[$name]);
			$this->save();
		}
	}

	/**
	 * @return void
	 */
	public function removeExpired(){
		foreach($this->list as $name => $entry){
			if($entry->hasExpired()){
				unset($this->list[$name]);
			}
		}
	}

	/**
	 * @return void
	 */
	public function load(){
		$this->list = [];
		$fp = @fopen($this->file, "r");
		if(is_resource($fp)){
			while(($line = fgets($fp)) !== false){
				if($line[0] !== "#"){
					try{
						$entry = BanEntry::fromString($line);
						if($entry instanceof BanEntry){
							$this->list[$entry->getName()] = $entry;
						}
					}catch(\Throwable $e){
						$logger = MainLogger::getLogger();
						$logger->critical("Failed to parse ban entry from string \"$line\": " . $e->getMessage());
						$logger->logException($e);
					}
				}
			}
			fclose($fp);
		}else{
			MainLogger::getLogger()->error("Could not load ban list");
		}
	}

	/**
	 * @return void
	 */
	public function save(bool $writeHeader = true){
		$this->removeExpired();
		$fp = @fopen($this->file, "w");
		if(is_resource($fp)){
			if($writeHeader){
				fwrite($fp, "# Updated " . strftime("%x %H:%M", time()) . " by " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion() . "\n");
				fwrite($fp, "# victim name | ban date | banned by | banned until | reason\n\n");
			}

			foreach($this->list as $entry){
				fwrite($fp, $entry->getString() . "\n");
			}
			fclose($fp);
		}else{
			MainLogger::getLogger()->error("Could not save ban list");
		}
	}
}
