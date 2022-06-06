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

namespace pocketmine\world\particle;

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;

abstract class ProtocolParticle implements Particle{

	protected int $particleProtocol;

	public function setParticleProtocol(int $particleProtocol) : void{
		$this->particleProtocol = $particleProtocol;
	}

	public static function getParticleProtocol(int $protocolId) : int{
		if($protocolId <= ProtocolInfo::PROTOCOL_1_17_0){
			return ProtocolInfo::PROTOCOL_1_17_0;
		}

		return ProtocolInfo::CURRENT_PROTOCOL;
	}

	/**
	 * @param Player[] $players
	 *
	 * @return Player[][]
	 */
	public static function sortByProtocol(array $players) : array{
		$sortPlayers = [];

		foreach($players as $player){
			$particleProtocol = self::getParticleProtocol($player->getNetworkSession()->getProtocolId());

			if(isset($sortPlayers[$particleProtocol])){
				$sortPlayers[$particleProtocol][] = $player;
			}else{
				$sortPlayers[$particleProtocol] = [$player];
			}
		}

		return $sortPlayers;
	}
}
