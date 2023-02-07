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

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;

trait ProtocolSingletonTrait{
	/** @var self[] */
	private static $instance = [];

	private static function make(int $protocolId) : self{
		return new self($protocolId);
	}

	public static function getInstance(int $protocolId = ProtocolInfo::CURRENT_PROTOCOL) : self{
		$protocolId = self::convertProtocol($protocolId);

		if(!isset(self::$instance[$protocolId])){
			self::$instance[$protocolId] = self::make($protocolId);
		}
		return self::$instance[$protocolId];
	}

	/**
	 * @return array<int, self>
	 */
	public static function getAll(bool $create = false) : array{
		if($create){
			foreach(ProtocolInfo::ACCEPTED_PROTOCOL as $protocolId){
				self::getInstance($protocolId);
			}
		}

		return self::$instance;
	}

	/**
	 * @param Player[] $players
	 *
	 * @return Player[][]
	 */
	public static function sortByProtocol(array $players) : array{
		$sortPlayers = [];

		foreach($players as $player){
			$protocolId = self::convertProtocol($player->getNetworkSession()->getProtocolId());

			if(isset($sortPlayers[$protocolId])){
				$sortPlayers[$protocolId][] = $player;
			}else{
				$sortPlayers[$protocolId] = [$player];
			}
		}

		return $sortPlayers;
	}

	abstract public static function convertProtocol(int $protocolId) : int;

	public static function setInstance(self $instance, int $protocolId) : void{
		self::$instance[$protocolId] = $instance;
	}

	public static function reset() : void{
		self::$instance = [];
	}
}
