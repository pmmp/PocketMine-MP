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
use pocketmine\network\mcpe\protocol\types\event\AchievementAwardedEvent;
use pocketmine\network\mcpe\protocol\types\event\AgentCommandEvent;
use pocketmine\network\mcpe\protocol\types\event\AgentCreatedEvent;
use pocketmine\network\mcpe\protocol\types\event\BellBlockUsedEvent;
use pocketmine\network\mcpe\protocol\types\event\BossKilledEvent;
use pocketmine\network\mcpe\protocol\types\event\CauldronBlockUsedEvent;
use pocketmine\network\mcpe\protocol\types\event\CauldronUsedEvent;
use pocketmine\network\mcpe\protocol\types\event\CommandExecutedEvent;
use pocketmine\network\mcpe\protocol\types\event\ComposterBlockUsedEvent;
use pocketmine\network\mcpe\protocol\types\event\EntityInteractEvent;
use pocketmine\network\mcpe\protocol\types\event\EventData;
use pocketmine\network\mcpe\protocol\types\event\FishBucketedEvent;
use pocketmine\network\mcpe\protocol\types\event\MobBornEvent;
use pocketmine\network\mcpe\protocol\types\event\MobKilledEvent;
use pocketmine\network\mcpe\protocol\types\event\PatternRemovedEvent;
use pocketmine\network\mcpe\protocol\types\event\PetDiedEvent;
use pocketmine\network\mcpe\protocol\types\event\PlayerDeathEvent;
use pocketmine\network\mcpe\protocol\types\event\PortalBuiltEvent;
use pocketmine\network\mcpe\protocol\types\event\PortalUsedEvent;

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
			case self::TYPE_ACHIEVEMENT_AWARDED: return new AchievementAwardedEvent();
			case self::TYPE_ENTITY_INTERACT: return new EntityInteractEvent();
			case self::TYPE_PORTAL_BUILT: return new PortalBuiltEvent();
			case self::TYPE_PORTAL_USED: return new PortalUsedEvent();
			case self::TYPE_MOB_KILLED: return new MobKilledEvent();
			case self::TYPE_CAULDRON_USED: return new CauldronUsedEvent();
			case self::TYPE_PLAYER_DEATH: return new PlayerDeathEvent();
			case self::TYPE_BOSS_KILLED: return new BossKilledEvent();
			case self::TYPE_AGENT_COMMAND: return new AgentCommandEvent();
			case self::TYPE_AGENT_CREATED: return new AgentCreatedEvent();
			case self::TYPE_PATTERN_REMOVED: return new PatternRemovedEvent();
			case self::TYPE_COMMANED_EXECUTED: return new CommandExecutedEvent();
			case self::TYPE_FISH_BUCKETED: return new FishBucketedEvent();
			case self::TYPE_MOB_BORN: return new MobBornEvent();
			case self::TYPE_PET_DIED: return new PetDiedEvent();
			case self::TYPE_CAULDRON_BLOCK_USED: return new CauldronBlockUsedEvent();
			case self::TYPE_COMPOSTER_BLOCK_USED: return new ComposterBlockUsedEvent();
			case self::TYPE_BELL_BLOCK_USED: return new BellBlockUsedEvent();
			default:
				throw new BadPacketException("Unknown event data type " . $eventDataType);
		}
	}

	protected function decodePayload() : void{
		$this->playerRuntimeId = $this->getEntityRuntimeId();
		$this->eventData = $this->readEventData($this->getVarInt());
		$this->type = $this->getByte();

		$this->eventData->read($this);
	}

	protected function encodePayload() : void{
		$this->putEntityRuntimeId($this->playerRuntimeId);
		$this->putVarInt($this->eventData::id());
		$this->putByte($this->type);

		$this->eventData->write($this);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleEvent($this);
	}
}
