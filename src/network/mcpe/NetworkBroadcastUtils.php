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

namespace pocketmine\network\mcpe;

use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\player\Player;
use pocketmine\timings\Timings;
use function count;
use function spl_object_id;

final class NetworkBroadcastUtils{

	private function __construct(){
		//NOOP
	}

	/**
	 * @param Player[]            $recipients
	 * @param ClientboundPacket[] $packets
	 */
	public static function broadcastPackets(array $recipients, array $packets) : bool{
		if(count($packets) === 0){
			throw new \InvalidArgumentException("Cannot broadcast empty list of packets");
		}

		return Timings::$broadcastPackets->time(function() use ($recipients, $packets) : bool{
			/** @var NetworkSession[] $sessions */
			$sessions = [];
			foreach($recipients as $player){
				if($player->isConnected()){
					$sessions[] = $player->getNetworkSession();
				}
			}
			if(count($sessions) === 0){
				return false;
			}

			/** @var PacketBroadcaster[] $uniqueBroadcasters */
			$uniqueBroadcasters = [];
			/** @var NetworkSession[][] $broadcasterTargets */
			$broadcasterTargets = [];
			foreach($sessions as $recipient){
				$broadcaster = $recipient->getBroadcaster();
				$uniqueBroadcasters[spl_object_id($broadcaster)] = $broadcaster;
				$broadcasterTargets[spl_object_id($broadcaster)][spl_object_id($recipient)] = $recipient;
			}
			foreach($uniqueBroadcasters as $broadcaster){
				$broadcaster->broadcastPackets($broadcasterTargets[spl_object_id($broadcaster)], $packets);
			}

			return true;
		});
	}

	/**
	 * @param Player[] $recipients
	 * @phpstan-param \Closure(EntityEventBroadcaster, array<int, NetworkSession>) : void $callback
	 */
	public static function broadcastEntityEvent(array $recipients, \Closure $callback) : void{
		$uniqueBroadcasters = [];
		$broadcasterTargets = [];

		foreach($recipients as $recipient){
			$session = $recipient->getNetworkSession();
			$broadcaster = $session->getEntityEventBroadcaster();
			$uniqueBroadcasters[spl_object_id($broadcaster)] = $broadcaster;
			$broadcasterTargets[spl_object_id($broadcaster)][spl_object_id($session)] = $session;
		}

		foreach($uniqueBroadcasters as $k => $broadcaster){
			$callback($broadcaster, $broadcasterTargets[$k]);
		}
	}
}
