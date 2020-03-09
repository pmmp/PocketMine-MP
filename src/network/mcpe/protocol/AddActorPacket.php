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
use pocketmine\math\Vector3;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;
use function array_search;
use function count;

class AddActorPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::ADD_ACTOR_PACKET;

	/*
	 * Really really really really really nasty hack, to preserve backwards compatibility.
	 * We can't transition to string IDs within 3.x because the network IDs (the integer ones) are exposed
	 * to the API in some places (for god's sake shoghi).
	 *
	 * TODO: remove this on 4.0
	 */
	public const LEGACY_ID_MAP_BC = [
		EntityLegacyIds::NPC => "minecraft:npc",
		EntityLegacyIds::PLAYER => "minecraft:player",
		EntityLegacyIds::WITHER_SKELETON => "minecraft:wither_skeleton",
		EntityLegacyIds::HUSK => "minecraft:husk",
		EntityLegacyIds::STRAY => "minecraft:stray",
		EntityLegacyIds::WITCH => "minecraft:witch",
		EntityLegacyIds::ZOMBIE_VILLAGER => "minecraft:zombie_villager",
		EntityLegacyIds::BLAZE => "minecraft:blaze",
		EntityLegacyIds::MAGMA_CUBE => "minecraft:magma_cube",
		EntityLegacyIds::GHAST => "minecraft:ghast",
		EntityLegacyIds::CAVE_SPIDER => "minecraft:cave_spider",
		EntityLegacyIds::SILVERFISH => "minecraft:silverfish",
		EntityLegacyIds::ENDERMAN => "minecraft:enderman",
		EntityLegacyIds::SLIME => "minecraft:slime",
		EntityLegacyIds::ZOMBIE_PIGMAN => "minecraft:zombie_pigman",
		EntityLegacyIds::SPIDER => "minecraft:spider",
		EntityLegacyIds::SKELETON => "minecraft:skeleton",
		EntityLegacyIds::CREEPER => "minecraft:creeper",
		EntityLegacyIds::ZOMBIE => "minecraft:zombie",
		EntityLegacyIds::SKELETON_HORSE => "minecraft:skeleton_horse",
		EntityLegacyIds::MULE => "minecraft:mule",
		EntityLegacyIds::DONKEY => "minecraft:donkey",
		EntityLegacyIds::DOLPHIN => "minecraft:dolphin",
		EntityLegacyIds::TROPICALFISH => "minecraft:tropicalfish",
		EntityLegacyIds::WOLF => "minecraft:wolf",
		EntityLegacyIds::SQUID => "minecraft:squid",
		EntityLegacyIds::DROWNED => "minecraft:drowned",
		EntityLegacyIds::SHEEP => "minecraft:sheep",
		EntityLegacyIds::MOOSHROOM => "minecraft:mooshroom",
		EntityLegacyIds::PANDA => "minecraft:panda",
		EntityLegacyIds::SALMON => "minecraft:salmon",
		EntityLegacyIds::PIG => "minecraft:pig",
		EntityLegacyIds::VILLAGER => "minecraft:villager",
		EntityLegacyIds::COD => "minecraft:cod",
		EntityLegacyIds::PUFFERFISH => "minecraft:pufferfish",
		EntityLegacyIds::COW => "minecraft:cow",
		EntityLegacyIds::CHICKEN => "minecraft:chicken",
		EntityLegacyIds::BALLOON => "minecraft:balloon",
		EntityLegacyIds::LLAMA => "minecraft:llama",
		EntityLegacyIds::IRON_GOLEM => "minecraft:iron_golem",
		EntityLegacyIds::RABBIT => "minecraft:rabbit",
		EntityLegacyIds::SNOW_GOLEM => "minecraft:snow_golem",
		EntityLegacyIds::BAT => "minecraft:bat",
		EntityLegacyIds::OCELOT => "minecraft:ocelot",
		EntityLegacyIds::HORSE => "minecraft:horse",
		EntityLegacyIds::CAT => "minecraft:cat",
		EntityLegacyIds::POLAR_BEAR => "minecraft:polar_bear",
		EntityLegacyIds::ZOMBIE_HORSE => "minecraft:zombie_horse",
		EntityLegacyIds::TURTLE => "minecraft:turtle",
		EntityLegacyIds::PARROT => "minecraft:parrot",
		EntityLegacyIds::GUARDIAN => "minecraft:guardian",
		EntityLegacyIds::ELDER_GUARDIAN => "minecraft:elder_guardian",
		EntityLegacyIds::VINDICATOR => "minecraft:vindicator",
		EntityLegacyIds::WITHER => "minecraft:wither",
		EntityLegacyIds::ENDER_DRAGON => "minecraft:ender_dragon",
		EntityLegacyIds::SHULKER => "minecraft:shulker",
		EntityLegacyIds::ENDERMITE => "minecraft:endermite",
		EntityLegacyIds::MINECART => "minecraft:minecart",
		EntityLegacyIds::HOPPER_MINECART => "minecraft:hopper_minecart",
		EntityLegacyIds::TNT_MINECART => "minecraft:tnt_minecart",
		EntityLegacyIds::CHEST_MINECART => "minecraft:chest_minecart",
		EntityLegacyIds::COMMAND_BLOCK_MINECART => "minecraft:command_block_minecart",
		EntityLegacyIds::ARMOR_STAND => "minecraft:armor_stand",
		EntityLegacyIds::ITEM => "minecraft:item",
		EntityLegacyIds::TNT => "minecraft:tnt",
		EntityLegacyIds::FALLING_BLOCK => "minecraft:falling_block",
		EntityLegacyIds::XP_BOTTLE => "minecraft:xp_bottle",
		EntityLegacyIds::XP_ORB => "minecraft:xp_orb",
		EntityLegacyIds::EYE_OF_ENDER_SIGNAL => "minecraft:eye_of_ender_signal",
		EntityLegacyIds::ENDER_CRYSTAL => "minecraft:ender_crystal",
		EntityLegacyIds::SHULKER_BULLET => "minecraft:shulker_bullet",
		EntityLegacyIds::FISHING_HOOK => "minecraft:fishing_hook",
		EntityLegacyIds::DRAGON_FIREBALL => "minecraft:dragon_fireball",
		EntityLegacyIds::ARROW => "minecraft:arrow",
		EntityLegacyIds::SNOWBALL => "minecraft:snowball",
		EntityLegacyIds::EGG => "minecraft:egg",
		EntityLegacyIds::PAINTING => "minecraft:painting",
		EntityLegacyIds::THROWN_TRIDENT => "minecraft:thrown_trident",
		EntityLegacyIds::FIREBALL => "minecraft:fireball",
		EntityLegacyIds::SPLASH_POTION => "minecraft:splash_potion",
		EntityLegacyIds::ENDER_PEARL => "minecraft:ender_pearl",
		EntityLegacyIds::LEASH_KNOT => "minecraft:leash_knot",
		EntityLegacyIds::WITHER_SKULL => "minecraft:wither_skull",
		EntityLegacyIds::WITHER_SKULL_DANGEROUS => "minecraft:wither_skull_dangerous",
		EntityLegacyIds::BOAT => "minecraft:boat",
		EntityLegacyIds::LIGHTNING_BOLT => "minecraft:lightning_bolt",
		EntityLegacyIds::SMALL_FIREBALL => "minecraft:small_fireball",
		EntityLegacyIds::LLAMA_SPIT => "minecraft:llama_spit",
		EntityLegacyIds::AREA_EFFECT_CLOUD => "minecraft:area_effect_cloud",
		EntityLegacyIds::LINGERING_POTION => "minecraft:lingering_potion",
		EntityLegacyIds::FIREWORKS_ROCKET => "minecraft:fireworks_rocket",
		EntityLegacyIds::EVOCATION_FANG => "minecraft:evocation_fang",
		EntityLegacyIds::EVOCATION_ILLAGER => "minecraft:evocation_illager",
		EntityLegacyIds::VEX => "minecraft:vex",
		EntityLegacyIds::AGENT => "minecraft:agent",
		EntityLegacyIds::ICE_BOMB => "minecraft:ice_bomb",
		EntityLegacyIds::PHANTOM => "minecraft:phantom",
		EntityLegacyIds::TRIPOD_CAMERA => "minecraft:tripod_camera"
	];

	/** @var int|null */
	public $entityUniqueId = null; //TODO
	/** @var int */
	public $entityRuntimeId;
	/** @var string */
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
	 * @var MetadataProperty[]
	 * @phpstan-var array<int, MetadataProperty>
	 */
	public $metadata = [];
	/** @var EntityLink[] */
	public $links = [];

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->entityUniqueId = $in->getEntityUniqueId();
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->type = $in->getString();
		$this->position = $in->getVector3();
		$this->motion = $in->getVector3();
		$this->pitch = $in->getLFloat();
		$this->yaw = $in->getLFloat();
		$this->headYaw = $in->getLFloat();

		$attrCount = $in->getUnsignedVarInt();
		for($i = 0; $i < $attrCount; ++$i){
			$id = $in->getString();
			$min = $in->getLFloat();
			$current = $in->getLFloat();
			$max = $in->getLFloat();
			$attr = Attribute::get($id);

			if($attr !== null){
				try{
					$attr->setMinValue($min);
					$attr->setMaxValue($max);
					$attr->setValue($current);
				}catch(\InvalidArgumentException $e){
					throw new BadPacketException($e->getMessage(), 0, $e); //TODO: address this properly
				}
				$this->attributes[] = $attr;
			}else{
				throw new BadPacketException("Unknown attribute type \"$id\"");
			}
		}

		$this->metadata = $in->getEntityMetadata();
		$linkCount = $in->getUnsignedVarInt();
		for($i = 0; $i < $linkCount; ++$i){
			$this->links[] = $in->getEntityLink();
		}
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putString($this->type);
		$out->putVector3($this->position);
		$out->putVector3Nullable($this->motion);
		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);
		$out->putLFloat($this->headYaw);

		$out->putUnsignedVarInt(count($this->attributes));
		foreach($this->attributes as $attribute){
			$out->putString($attribute->getId());
			$out->putLFloat($attribute->getMinValue());
			$out->putLFloat($attribute->getValue());
			$out->putLFloat($attribute->getMaxValue());
		}

		$out->putEntityMetadata($this->metadata);
		$out->putUnsignedVarInt(count($this->links));
		foreach($this->links as $link){
			$out->putEntityLink($link);
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleAddActor($this);
	}
}
