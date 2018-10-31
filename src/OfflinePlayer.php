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

use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\Plugin;

class OfflinePlayer implements IPlayer, Metadatable{

	/** @var string */
	private $name;
	/** @var Server */
	private $server;
	/** @var CompoundTag|null */
	private $namedtag = null;

	/**
	 * @param Server $server
	 * @param string $name
	 */
	public function __construct(Server $server, string $name){
		$this->server = $server;
		$this->name = $name;
		if($this->server->hasOfflinePlayerData($this->name)){
			$this->namedtag = $this->server->getOfflinePlayerData($this->name);
		}
	}

	public function isOnline() : bool{
		return $this->getPlayer() !== null;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getServer(){
		return $this->server;
	}

	public function isOp() : bool{
		return $this->server->isOp($this->name);
	}

	public function setOp(bool $value){
		if($value === $this->isOp()){
			return;
		}

		if($value){
			$this->server->addOp($this->name);
		}else{
			$this->server->removeOp($this->name);
		}
	}

	public function isBanned() : bool{
		return $this->server->getNameBans()->isBanned($this->name);
	}

	public function setBanned(bool $value){
		if($value){
			$this->server->getNameBans()->addBan($this->name, null, null, null);
		}else{
			$this->server->getNameBans()->remove($this->name);
		}
	}

	public function isWhitelisted() : bool{
		return $this->server->isWhitelisted($this->name);
	}

	public function setWhitelisted(bool $value){
		if($value){
			$this->server->addWhitelist($this->name);
		}else{
			$this->server->removeWhitelist($this->name);
		}
	}

	public function getPlayer(){
		return $this->server->getPlayerExact($this->name);
	}

	public function getFirstPlayed(){
		return $this->namedtag instanceof CompoundTag ? $this->namedtag->getLong("firstPlayed", 0, true) : null;
	}

	public function getLastPlayed(){
		return $this->namedtag instanceof CompoundTag ? $this->namedtag->getLong("lastPlayed", 0, true) : null;
	}

	public function hasPlayedBefore() : bool{
		return $this->namedtag instanceof CompoundTag;
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
	}
}
