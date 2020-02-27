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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\event\AchievementAwardedEventData;
use pocketmine\network\mcpe\protocol\types\event\AgentCommandEventData;
use pocketmine\network\mcpe\protocol\types\event\AgentCreatedEventData;
use pocketmine\network\mcpe\protocol\types\event\BellBlockUsedEventData;
use pocketmine\network\mcpe\protocol\types\event\BossKilledEventData;
use pocketmine\network\mcpe\protocol\types\event\CauldronBlockUsedEventData;
use pocketmine\network\mcpe\protocol\types\event\CauldronUsedEventData;
use pocketmine\network\mcpe\protocol\types\event\CommandExecutedEventData;
use pocketmine\network\mcpe\protocol\types\event\ComposterBlockUsedEventData;
use pocketmine\network\mcpe\protocol\types\event\EntityInteractEventData;
use pocketmine\network\mcpe\protocol\types\event\EventData;
use pocketmine\network\mcpe\protocol\types\event\FishBucketedEventData;
use pocketmine\network\mcpe\protocol\types\event\MobBornEventData;
use pocketmine\network\mcpe\protocol\types\event\MobKilledEventData;
use pocketmine\network\mcpe\protocol\types\event\PatternRemovedEventData;
use pocketmine\network\mcpe\protocol\types\event\PetDiedEventData;
use pocketmine\network\mcpe\protocol\types\event\PlayerDeathEventData;
use pocketmine\network\mcpe\protocol\types\event\PortalBuiltEventData;
use pocketmine\network\mcpe\protocol\types\event\PortalUsedEventData;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

class EventPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::EVENT_PACKET;

	public const TYPE_ACHIEVEMENT_AWARDED = 0;
	public const TYPE_ENTITY_INTERACT = 1;
	public const TYPE_PORTAL_BUILT = 2;
	public const TYPE_PORTAL_USED = 3;
	public const TYPE_MOB_KILLED = 4;
	public const TYPE_CAULDRON_USED = 5;
	public const TYPE_PLAYER_DEATH = 6;
	public const TYPE_BOSS_KILLED = 7;
	public const TYPE_AGENT_COMMAND = 8;
	public const TYPE_AGENT_CREATED = 9;
	public const TYPE_PATTERN_REMOVED = 10; //???
	public const TYPE_COMMANED_EXECUTED = 11;
	public const TYPE_FISH_BUCKETED = 12;
	public const TYPE_MOB_BORN = 13;
	public const TYPE_PET_DIED = 14;
	public const TYPE_CAULDRON_BLOCK_USED = 15;
	public const TYPE_COMPOSTER_BLOCK_USED = 16;
	public const TYPE_BELL_BLOCK_USED = 17;

	/** @var int */
	public $playerRuntimeId;
	/** @var EventData */
	public $eventData;
	/** @var int */
	public $type;

	protected function readEventData(int $eventDataType) : EventData{
		switch($eventDataType){
			case self::TYPE_ACHIEVEMENT_AWARDED: return new AchievementAwardedEventData();
			case self::TYPE_ENTITY_INTERACT: return new EntityInteractEventData();
			case self::TYPE_PORTAL_BUILT: return new PortalBuiltEventData();
			case self::TYPE_PORTAL_USED: return new PortalUsedEventData();
			case self::TYPE_MOB_KILLED: return new MobKilledEventData();
			case self::TYPE_CAULDRON_USED: return new CauldronUsedEventData();
			case self::TYPE_PLAYER_DEATH: return new PlayerDeathEventData();
			case self::TYPE_BOSS_KILLED: return new BossKilledEventData();
			case self::TYPE_AGENT_COMMAND: return new AgentCommandEventData();
			case self::TYPE_AGENT_CREATED: return new AgentCreatedEventData();
			case self::TYPE_PATTERN_REMOVED: return new PatternRemovedEventData();
			case self::TYPE_COMMANED_EXECUTED: return new CommandExecutedEventData();
			case self::TYPE_FISH_BUCKETED: return new FishBucketedEventData();
			case self::TYPE_MOB_BORN: return new MobBornEventData();
			case self::TYPE_PET_DIED: return new PetDiedEventData();
			case self::TYPE_CAULDRON_BLOCK_USED: return new CauldronBlockUsedEventData();
			case self::TYPE_COMPOSTER_BLOCK_USED: return new ComposterBlockUsedEventData();
			case self::TYPE_BELL_BLOCK_USED: return new BellBlockUsedEventData();
			default:
				throw new BadPacketException("Unknown event data type " . $eventDataType);
		}
	}

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->playerRuntimeId = $in->getEntityRuntimeId();
		$this->eventData = $this->readEventData($in->getVarInt());
		$this->type = $in->getByte();

		$this->eventData->read($in);
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putEntityRuntimeId($this->playerRuntimeId);
		$out->putVarInt($this->eventData->id());
		$out->putByte($this->type);

		$this->eventData->write($out);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleEvent($this);
	}
}
