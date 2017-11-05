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


use pocketmine\network\mcpe\NetworkSession;

class EntityEventPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::ENTITY_EVENT_PACKET;

	const HURT_ANIMATION = 2;
	const DEATH_ANIMATION = 3;
	const ARM_SWING = 4;

	const TAME_FAIL = 6;
	const TAME_SUCCESS = 7;
	const SHAKE_WET = 8;
	const USE_ITEM = 9;
	const EAT_GRASS_ANIMATION = 10;
	const FISH_HOOK_BUBBLE = 11;
	const FISH_HOOK_POSITION = 12;
	const FISH_HOOK_HOOK = 13;
	const FISH_HOOK_TEASE = 14;
	const SQUID_INK_CLOUD = 15;
	const ZOMBIE_VILLAGER_CURE = 16;

	const RESPAWN = 18;
	const IRON_GOLEM_OFFER_FLOWER = 19;
	const IRON_GOLEM_WITHDRAW_FLOWER = 20;
	const LOVE_PARTICLES = 21; //breeding

	const WITCH_SPELL_PARTICLES = 24;
	const FIREWORK_PARTICLES = 25;

	const SILVERFISH_SPAWN_ANIMATION = 27;

	const WITCH_DRINK_POTION = 29;
	const WITCH_THROW_POTION = 30;
	const MINECART_TNT_PRIME_FUSE = 31;

	const PLAYER_ADD_XP_LEVELS = 34;
	const ELDER_GUARDIAN_CURSE = 35;
	const AGENT_ARM_SWING = 36;
	const ENDER_DRAGON_DEATH = 37;
	const DUST_PARTICLES = 38; //not sure what this is

	const EATING_ITEM = 57;

	const BABY_ANIMAL_FEED = 60; //green particles, like bonemeal on crops
	const DEATH_SMOKE_CLOUD = 61;
	const COMPLETE_TRADE = 62;
	const REMOVE_LEASH = 63; //data 1 = cut leash

	const CONSUME_TOTEM = 65;
	const PLAYER_CHECK_TREASURE_HUNTER_ACHIEVEMENT = 66; //mojang...
	const ENTITY_SPAWN = 67; //used for MinecraftEventing stuff, not needed
	const DRAGON_PUKE = 68; //they call this puke particles
	const ITEM_ENTITY_MERGE = 69;

	//TODO: add more events

	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $event;
	/** @var int */
	public $data = 0;

	protected function decodePayload(){
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->event = $this->getByte();
		$this->data = $this->getVarInt();
	}

	protected function encodePayload(){
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putByte($this->event);
		$this->putVarInt($this->data);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleEntityEvent($this);
	}

}
