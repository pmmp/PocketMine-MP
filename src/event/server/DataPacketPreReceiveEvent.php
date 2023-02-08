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

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class DataPacketPreReceiveEvent extends ServerEvent{

	public const MOVE_PLAYER_PACKET = ProtocolInfo::MOVE_PLAYER_PACKET;
	public const LEVEL_SOUND_EVENT_PACKET_V1 = ProtocolInfo::LEVEL_SOUND_EVENT_PACKET_V1;
	public const MOB_ARMOR_EQUIPMENT_PACKET = ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET;
	public const SET_ACTOR_MOTION_PACKET = ProtocolInfo::SET_ACTOR_MOTION_PACKET;
	public const ACTOR_PICK_REQUEST_PACKET = ProtocolInfo::ACTOR_PICK_REQUEST_PACKET;
	public const ANIMATE_PACKET = ProtocolInfo::ANIMATE_PACKET;
	public const PLAYER_HOTBAR_PACKET = ProtocolInfo::PLAYER_HOTBAR_PACKET;
	public const CRAFTING_EVENT_PACKET = ProtocolInfo::CRAFTING_EVENT_PACKET;
	public const PLAYER_INPUT_PACKET = ProtocolInfo::PLAYER_INPUT_PACKET;
	public const SPAWN_EXPERIENCE_ORB_PACKET = ProtocolInfo::SPAWN_EXPERIENCE_ORB_PACKET;
	public const MAP_INFO_REQUEST_PACKET = ProtocolInfo::MAP_INFO_REQUEST_PACKET;
	public const BOSS_EVENT_PACKET = ProtocolInfo::BOSS_EVENT_PACKET;
	public const SHOW_CREDITS_PACKET = ProtocolInfo::SHOW_CREDITS_PACKET;
	public const COMMAND_BLOCK_UPDATE_PACKET = ProtocolInfo::COMMAND_BLOCK_UPDATE_PACKET;
	public const SUB_CLIENT_LOGIN_PACKET = ProtocolInfo::SUB_CLIENT_LOGIN_PACKET;
	public const SERVER_SETTINGS_REQUEST_PACKET = ProtocolInfo::SERVER_SETTINGS_REQUEST_PACKET;
	public const LAB_TABLE_PACKET = ProtocolInfo::LAB_TABLE_PACKET;
	public const NETWORK_STACK_LATENCY_PACKET = ProtocolInfo::NETWORK_STACK_LATENCY_PACKET;
	public const LEVEL_SOUND_EVENT_PACKET = ProtocolInfo::LEVEL_SOUND_EVENT_PACKET;

	public const IGNORED_PACKETS = [
		self::MOVE_PLAYER_PACKET,
		self::LEVEL_SOUND_EVENT_PACKET_V1,
		self::MOB_ARMOR_EQUIPMENT_PACKET,
		self::SET_ACTOR_MOTION_PACKET,
		self::ACTOR_PICK_REQUEST_PACKET,
		self::ANIMATE_PACKET,
		self::PLAYER_HOTBAR_PACKET,
		self::CRAFTING_EVENT_PACKET,
		self::PLAYER_INPUT_PACKET,
		self::SPAWN_EXPERIENCE_ORB_PACKET,
		self::MAP_INFO_REQUEST_PACKET,
		self::BOSS_EVENT_PACKET,
		self::SHOW_CREDITS_PACKET,
		self::COMMAND_BLOCK_UPDATE_PACKET,
		self::SUB_CLIENT_LOGIN_PACKET,
		self::SERVER_SETTINGS_REQUEST_PACKET,
		self::LAB_TABLE_PACKET,
		self::NETWORK_STACK_LATENCY_PACKET,
		self::LEVEL_SOUND_EVENT_PACKET
	];

	/**
	 * @param int[] $ignoredPackets
	 */
	public function __construct(
		private NetworkSession $origin,
		private int $packetId,
		private array $ignoredPackets = self::IGNORED_PACKETS,
		private bool $isIgnored = false
	){
	}

	public function getPacketId() : int{
		return $this->packetId;
	}

	public function getOrigin() : NetworkSession{
		return $this->origin;
	}

	/**
	 * @return int[]
	 */
	public function getIgnoredPackets() : array{
		return $this->ignoredPackets;
	}

	/**
	 * @param int[] $ignoredPackets
	 */
	public function setIgnoredPackets(array $ignoredPackets) : void{
		$this->ignoredPackets = $ignoredPackets;
	}

	public function isIgnored() : bool{
		return $this->isIgnored;
	}

	public function setIgnored(bool $value = true) : void{
		$this->isIgnored = $value;
	}
}
