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

use function fclose;
use function fgets;
use function fopen;
use function fwrite;
use function is_resource;
use function strtolower;
use function trim;

class BanList{
	/** @var BanEntry[] */
	private array $list = [];

	private string $file;

	private bool $enabled = true;

	public function __construct(string $file){
		$this->file = $file;
	}

	public function isEnabled() : bool{
		return $this->enabled;
	}

	public function setEnabled(bool $flag) : void{
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

	public function add(BanEntry $entry) : void{
		$this->list[$entry->getName()] = $entry;
		$this->save();
	}

	public function addBanFromString(string $banEntry) : ?BanEntry{
		$parts = explode("|", trim($banEntry));

		$ban = $this->addBan($parts[0], $parts[1] ?? null, $parts[2] ?? null, $parts[3] ?? null, $parts[4] ?? null, false);

		return $ban->isExpired() ? null : $ban;
	}

	public function addBan(string $target, ?string $source, ?string $reason = null, ?string $creationDate = null, ?string $expirationDate = null, bool $save = true) : BanEntry{
		$creation = null;
		if($creationDate && BanEntry::isValidDateString($creationDate)){
			$creation = strtotime($creationDate);
		}

		$expires = null;
		if($expirationDate && BanEntry::isValidDateString($expirationDate)){
			$expires = strtotime($expirationDate);
		}

		$entry = new BanEntry($target, $source, $reason, $creation, $expires);

		$this->list[$entry->getName()] = $entry;

		if($save){
			$this->save();
		}

		return $entry;
	}

	public function remove(string $name) : void{
		$name = strtolower($name);
		if(isset($this->list[$name])){
			unset($this->list[$name]);
			$this->save();
		}
	}

	public function removeExpired() : void{
		foreach($this->list as $name => $entry){
			if($entry->isExpired()){
				$this->remove($name);
			}
		}
	}

	public function load() : void{
		$this->list = [];
		$fp = @fopen($this->file, "r");
		if(is_resource($fp)){
			while(($line = fgets($fp)) !== false){
				if($line[0] !== "#"){
					$entry = $this->addBanFromString($line);
					if($entry !== null){
						$this->list[$entry->getName()] = $entry;
					}else{
						$logger = \GlobalLogger::get();
						$logger->critical("Failed to parse ban entry from string \"" . trim($line) . "\"");
					}

				}
			}
			fclose($fp);
		}else{
			\GlobalLogger::get()->error("Could not load ban list");
		}
	}

	public function save(bool $writeHeader = true) : void{
		$fp = @fopen($this->file, "w");
		$str = "";

		if(is_resource($fp)){
			if($writeHeader){
				$str .= "# player | source | reason | creation date | expiration date \n\n";
			}

			foreach($this->list as $entry){
				$str .= $entry->toString() . "\n";
			}

			fwrite($fp, $str);

			fclose($fp);
		}else{
			\GlobalLogger::get()->error("Could not save ban list");
		}
	}
}
