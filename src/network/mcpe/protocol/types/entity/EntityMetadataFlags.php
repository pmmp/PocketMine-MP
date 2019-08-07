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

namespace pocketmine\network\mcpe\protocol\types\entity;

final class EntityMetadataFlags{

	private function __construct(){
		//NOOP
	}

	public const ONFIRE = 0;
	public const SNEAKING = 1;
	public const RIDING = 2;
	public const SPRINTING = 3;
	public const ACTION = 4;
	public const INVISIBLE = 5;
	public const TEMPTED = 6;
	public const INLOVE = 7;
	public const SADDLED = 8;
	public const POWERED = 9;
	public const IGNITED = 10;
	public const BABY = 11;
	public const CONVERTING = 12;
	public const CRITICAL = 13;
	public const CAN_SHOW_NAMETAG = 14;
	public const ALWAYS_SHOW_NAMETAG = 15;
	public const IMMOBILE = 16, NO_AI = 16;
	public const SILENT = 17;
	public const WALLCLIMBING = 18;
	public const CAN_CLIMB = 19;
	public const SWIMMER = 20;
	public const CAN_FLY = 21;
	public const WALKER = 22;
	public const RESTING = 23;
	public const SITTING = 24;
	public const ANGRY = 25;
	public const INTERESTED = 26;
	public const CHARGED = 27;
	public const TAMED = 28;
	public const ORPHANED = 29;
	public const LEASHED = 30;
	public const SHEARED = 31;
	public const GLIDING = 32;
	public const ELDER = 33;
	public const MOVING = 34;
	public const BREATHING = 35;
	public const CHESTED = 36;
	public const STACKABLE = 37;
	public const SHOWBASE = 38;
	public const REARING = 39;
	public const VIBRATING = 40;
	public const IDLING = 41;
	public const EVOKER_SPELL = 42;
	public const CHARGE_ATTACK = 43;
	public const WASD_CONTROLLED = 44;
	public const CAN_POWER_JUMP = 45;
	public const LINGER = 46;
	public const HAS_COLLISION = 47;
	public const AFFECTED_BY_GRAVITY = 48;
	public const FIRE_IMMUNE = 49;
	public const DANCING = 50;
	public const ENCHANTED = 51;
	public const SHOW_TRIDENT_ROPE = 52; // tridents show an animated rope when enchanted with loyalty after they are thrown and return to their owner. To be combined with DATA_OWNER_EID
	public const CONTAINER_PRIVATE = 53; //inventory is private, doesn't drop contents when killed if true
	public const TRANSFORMING = 54;
	public const SPIN_ATTACK = 55;
	public const SWIMMING = 56;
	public const BRIBED = 57; //dolphins have this set when they go to find treasure for the player
	public const PREGNANT = 58;
	public const LAYING_EGG = 59;
	public const RIDER_CAN_PICK = 60; //???
	public const TRANSITION_SITTING = 61;
	public const EATING = 62;
	public const LAYING_DOWN = 63;
	public const SNEEZING = 64;
	public const TRUSTING = 65;
	public const ROLLING = 66;
	public const SCARED = 67;
	public const IN_SCAFFOLDING = 68;
	public const OVER_SCAFFOLDING = 69;
	public const FALL_THROUGH_SCAFFOLDING = 70;
	public const BLOCKING = 71; //shield
	public const DISABLE_BLOCKING = 72;
	//73 is set when a player is attacked while using shield, unclear on purpose
	//74 related to shield usage, needs further investigation
	public const SLEEPING = 75;
	//76 related to sleeping, unclear usage
	public const TRADE_INTEREST = 77;
	public const DOOR_BREAKER = 78; //...
	public const BREAKING_OBSTRUCTION = 79;
	public const DOOR_OPENER = 80; //...
	public const ILLAGER_CAPTAIN = 81;
	public const STUNNED = 82;
	public const ROARING = 83;
	public const DELAYED_ATTACKING = 84;
	public const AVOIDING_MOBS = 85;
	//86 used by RangedAttackGoal
	//87 used by NearestAttackableTargetGoal
}
