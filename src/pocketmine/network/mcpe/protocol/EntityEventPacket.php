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


use pocketmine\network\mcpe\handler\SessionHandler;

class EntityEventPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::ENTITY_EVENT_PACKET;

	public const HURT_ANIMATION = 2;
	public const DEATH_ANIMATION = 3;
	public const ARM_SWING = 4;

	public const TAME_FAIL = 6;
	public const TAME_SUCCESS = 7;
	public const SHAKE_WET = 8;
	public const USE_ITEM = 9;
	public const EAT_GRASS_ANIMATION = 10;
	public const FISH_HOOK_BUBBLE = 11;
	public const FISH_HOOK_POSITION = 12;
	public const FISH_HOOK_HOOK = 13;
	public const FISH_HOOK_TEASE = 14;
	public const SQUID_INK_CLOUD = 15;
	public const ZOMBIE_VILLAGER_CURE = 16;

	public const RESPAWN = 18;
	public const IRON_GOLEM_OFFER_FLOWER = 19;
	public const IRON_GOLEM_WITHDRAW_FLOWER = 20;
	public const LOVE_PARTICLES = 21; //breeding

	public const WITCH_SPELL_PARTICLES = 24;
	public const FIREWORK_PARTICLES = 25;

	public const SILVERFISH_SPAWN_ANIMATION = 27;

	public const WITCH_DRINK_POTION = 29;
	public const WITCH_THROW_POTION = 30;
	public const MINECART_TNT_PRIME_FUSE = 31;

	public const PLAYER_ADD_XP_LEVELS = 34;
	public const ELDER_GUARDIAN_CURSE = 35;
	public const AGENT_ARM_SWING = 36;
	public const ENDER_DRAGON_DEATH = 37;
	public const DUST_PARTICLES = 38; //not sure what this is
	public const ARROW_SHAKE = 39;

	public const EATING_ITEM = 57;

	public const BABY_ANIMAL_FEED = 60; //green particles, like bonemeal on crops
	public const DEATH_SMOKE_CLOUD = 61;
	public const COMPLETE_TRADE = 62;
	public const REMOVE_LEASH = 63; //data 1 = cut leash

	public const CONSUME_TOTEM = 65;
	public const PLAYER_CHECK_TREASURE_HUNTER_ACHIEVEMENT = 66; //mojang...
	public const ENTITY_SPAWN = 67; //used for MinecraftEventing stuff, not needed
	public const DRAGON_PUKE = 68; //they call this puke particles
	public const ITEM_ENTITY_MERGE = 69;

	//TODO: add more events

	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $event;
	/** @var int */
	public $data = 0;

	protected function decodePayload() : void{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->event = $this->getByte();
		$this->data = $this->getVarInt();
	}

	protected function encodePayload() : void{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putByte($this->event);
		$this->putVarInt($this->data);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleEntityEvent($this);
	}
}
