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

use pocketmine\entity\Attribute;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use function array_search;
use function count;

class AddActorPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::ADD_ACTOR_PACKET;

	/*
	 * Really really really really really nasty hack, to preserve backwards compatibility.
	 * We can't transition to string IDs within 3.x because the network IDs (the integer ones) are exposed
	 * to the API in some places (for god's sake shoghi).
	 *
	 * TODO: remove this on 4.0
	 */
	public const LEGACY_ID_MAP_BC = [
		EntityIds::NPC => "minecraft:npc",
		EntityIds::PLAYER => "minecraft:player",
		EntityIds::WITHER_SKELETON => "minecraft:wither_skeleton",
		EntityIds::HUSK => "minecraft:husk",
		EntityIds::STRAY => "minecraft:stray",
		EntityIds::WITCH => "minecraft:witch",
		EntityIds::ZOMBIE_VILLAGER => "minecraft:zombie_villager",
		EntityIds::BLAZE => "minecraft:blaze",
		EntityIds::MAGMA_CUBE => "minecraft:magma_cube",
		EntityIds::GHAST => "minecraft:ghast",
		EntityIds::CAVE_SPIDER => "minecraft:cave_spider",
		EntityIds::SILVERFISH => "minecraft:silverfish",
		EntityIds::ENDERMAN => "minecraft:enderman",
		EntityIds::SLIME => "minecraft:slime",
		EntityIds::ZOMBIE_PIGMAN => "minecraft:zombie_pigman",
		EntityIds::SPIDER => "minecraft:spider",
		EntityIds::SKELETON => "minecraft:skeleton",
		EntityIds::CREEPER => "minecraft:creeper",
		EntityIds::ZOMBIE => "minecraft:zombie",
		EntityIds::SKELETON_HORSE => "minecraft:skeleton_horse",
		EntityIds::MULE => "minecraft:mule",
		EntityIds::DONKEY => "minecraft:donkey",
		EntityIds::DOLPHIN => "minecraft:dolphin",
		EntityIds::TROPICALFISH => "minecraft:tropicalfish",
		EntityIds::WOLF => "minecraft:wolf",
		EntityIds::SQUID => "minecraft:squid",
		EntityIds::DROWNED => "minecraft:drowned",
		EntityIds::SHEEP => "minecraft:sheep",
		EntityIds::MOOSHROOM => "minecraft:mooshroom",
		EntityIds::PANDA => "minecraft:panda",
		EntityIds::SALMON => "minecraft:salmon",
		EntityIds::PIG => "minecraft:pig",
		EntityIds::VILLAGER => "minecraft:villager",
		EntityIds::COD => "minecraft:cod",
		EntityIds::PUFFERFISH => "minecraft:pufferfish",
		EntityIds::COW => "minecraft:cow",
		EntityIds::CHICKEN => "minecraft:chicken",
		EntityIds::BALLOON => "minecraft:balloon",
		EntityIds::LLAMA => "minecraft:llama",
		EntityIds::IRON_GOLEM => "minecraft:iron_golem",
		EntityIds::RABBIT => "minecraft:rabbit",
		EntityIds::SNOW_GOLEM => "minecraft:snow_golem",
		EntityIds::BAT => "minecraft:bat",
		EntityIds::OCELOT => "minecraft:ocelot",
		EntityIds::HORSE => "minecraft:horse",
		EntityIds::CAT => "minecraft:cat",
		EntityIds::POLAR_BEAR => "minecraft:polar_bear",
		EntityIds::ZOMBIE_HORSE => "minecraft:zombie_horse",
		EntityIds::TURTLE => "minecraft:turtle",
		EntityIds::PARROT => "minecraft:parrot",
		EntityIds::GUARDIAN => "minecraft:guardian",
		EntityIds::ELDER_GUARDIAN => "minecraft:elder_guardian",
		EntityIds::VINDICATOR => "minecraft:vindicator",
		EntityIds::WITHER => "minecraft:wither",
		EntityIds::ENDER_DRAGON => "minecraft:ender_dragon",
		EntityIds::SHULKER => "minecraft:shulker",
		EntityIds::ENDERMITE => "minecraft:endermite",
		EntityIds::MINECART => "minecraft:minecart",
		EntityIds::HOPPER_MINECART => "minecraft:hopper_minecart",
		EntityIds::TNT_MINECART => "minecraft:tnt_minecart",
		EntityIds::CHEST_MINECART => "minecraft:chest_minecart",
		EntityIds::COMMAND_BLOCK_MINECART => "minecraft:command_block_minecart",
		EntityIds::ARMOR_STAND => "minecraft:armor_stand",
		EntityIds::ITEM => "minecraft:item",
		EntityIds::TNT => "minecraft:tnt",
		EntityIds::FALLING_BLOCK => "minecraft:falling_block",
		EntityIds::XP_BOTTLE => "minecraft:xp_bottle",
		EntityIds::XP_ORB => "minecraft:xp_orb",
		EntityIds::EYE_OF_ENDER_SIGNAL => "minecraft:eye_of_ender_signal",
		EntityIds::ENDER_CRYSTAL => "minecraft:ender_crystal",
		EntityIds::SHULKER_BULLET => "minecraft:shulker_bullet",
		EntityIds::FISHING_HOOK => "minecraft:fishing_hook",
		EntityIds::DRAGON_FIREBALL => "minecraft:dragon_fireball",
		EntityIds::ARROW => "minecraft:arrow",
		EntityIds::SNOWBALL => "minecraft:snowball",
		EntityIds::EGG => "minecraft:egg",
		EntityIds::PAINTING => "minecraft:painting",
		EntityIds::THROWN_TRIDENT => "minecraft:thrown_trident",
		EntityIds::FIREBALL => "minecraft:fireball",
		EntityIds::SPLASH_POTION => "minecraft:splash_potion",
		EntityIds::ENDER_PEARL => "minecraft:ender_pearl",
		EntityIds::LEASH_KNOT => "minecraft:leash_knot",
		EntityIds::WITHER_SKULL => "minecraft:wither_skull",
		EntityIds::WITHER_SKULL_DANGEROUS => "minecraft:wither_skull_dangerous",
		EntityIds::BOAT => "minecraft:boat",
		EntityIds::LIGHTNING_BOLT => "minecraft:lightning_bolt",
		EntityIds::SMALL_FIREBALL => "minecraft:small_fireball",
		EntityIds::LLAMA_SPIT => "minecraft:llama_spit",
		EntityIds::AREA_EFFECT_CLOUD => "minecraft:area_effect_cloud",
		EntityIds::LINGERING_POTION => "minecraft:lingering_potion",
		EntityIds::FIREWORKS_ROCKET => "minecraft:fireworks_rocket",
		EntityIds::EVOCATION_FANG => "minecraft:evocation_fang",
		EntityIds::EVOCATION_ILLAGER => "minecraft:evocation_illager",
		EntityIds::VEX => "minecraft:vex",
		EntityIds::AGENT => "minecraft:agent",
		EntityIds::ICE_BOMB => "minecraft:ice_bomb",
		EntityIds::PHANTOM => "minecraft:phantom",
		EntityIds::TRIPOD_CAMERA => "minecraft:tripod_camera"
	];

	/** @var int|null */
	public $entityUniqueId = null; //TODO
	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $type;
	/** @var Vector3 */
	public $position;
	/** @var Vector3|null */
	public $motion;
	/** @var float */
	public $pitch = 0.0;
	/** @var float */
	public $yaw = 0.0;
	/** @var float */
	public $headYaw = 0.0;

	/** @var Attribute[] */
	public $attributes = [];
	/**
	 * @var mixed[][]
	 * @phpstan-var array<int, array{0: int, 1: mixed}>
	 */
	public $metadata = [];
	/** @var EntityLink[] */
	public $links = [];

	protected function decodePayload(){
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->type = array_search($t = $this->getString(), self::LEGACY_ID_MAP_BC, true);
		if($this->type === false){
			throw new \UnexpectedValueException("Can't map ID $t to legacy ID");
		}
		$this->position = $this->getVector3();
		$this->motion = $this->getVector3();
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		$this->headYaw = $this->getLFloat();

		$attrCount = $this->getUnsignedVarInt();
		for($i = 0; $i < $attrCount; ++$i){
			$name = $this->getString();
			$min = $this->getLFloat();
			$current = $this->getLFloat();
			$max = $this->getLFloat();
			$attr = Attribute::getAttributeByName($name);

			if($attr !== null){
				$attr->setMinValue($min);
				$attr->setMaxValue($max);
				$attr->setValue($current);
				$this->attributes[] = $attr;
			}else{
				throw new \UnexpectedValueException("Unknown attribute type \"$name\"");
			}
		}

		$this->metadata = $this->getEntityMetadata();
		$linkCount = $this->getUnsignedVarInt();
		for($i = 0; $i < $linkCount; ++$i){
			$this->links[] = $this->getEntityLink();
		}
	}

	protected function encodePayload(){
		$this->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		if(!isset(self::LEGACY_ID_MAP_BC[$this->type])){
			throw new \InvalidArgumentException("Unknown entity numeric ID $this->type");
		}
		$this->putString(self::LEGACY_ID_MAP_BC[$this->type]);
		$this->putVector3($this->position);
		$this->putVector3Nullable($this->motion);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putLFloat($this->headYaw);

		$this->putUnsignedVarInt(count($this->attributes));
		foreach($this->attributes as $attribute){
			$this->putString($attribute->getName());
			$this->putLFloat($attribute->getMinValue());
			$this->putLFloat($attribute->getValue());
			$this->putLFloat($attribute->getMaxValue());
		}

		$this->putEntityMetadata($this->metadata);
		$this->putUnsignedVarInt(count($this->links));
		foreach($this->links as $link){
			$this->putEntityLink($link);
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAddActor($this);
	}
}
