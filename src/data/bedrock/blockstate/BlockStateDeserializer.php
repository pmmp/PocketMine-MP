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

namespace pocketmine\data\bedrock\blockstate;

use pocketmine\block\Bamboo;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\SlabType;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\blockstate\BlockStateDeserializerHelper as Helper;
use pocketmine\data\bedrock\blockstate\BlockStateValues as Values;
use pocketmine\data\bedrock\blockstate\BlockTypeNames as Ids;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use function array_key_exists;
use function min;

final class BlockStateDeserializer{

	/**
	 * @var \Closure[]
	 * @phpstan-var array<string, \Closure(BlockStateReader $in) : Block>
	 */
	private array $deserializeFuncs = [];

	/** @phpstan-param \Closure(BlockStateReader) : Block $c */
	private function map(string $id, \Closure $c) : void{
		if(array_key_exists($id, $this->deserializeFuncs)){
			throw new \InvalidArgumentException("Deserializer is already assigned for \"$id\"");
		}
		$this->deserializeFuncs[$id] = $c;
	}

	public function __construct(){
		$this->map(Ids::ACACIA_BUTTON, fn(BlockStateReader $in) => Helper::decodeButton(VanillaBlocks::ACACIA_BUTTON(), $in));
		$this->map(Ids::ACACIA_DOOR, fn(BlockStateReader $in) => Helper::decodeDoor(VanillaBlocks::ACACIA_DOOR(), $in));
		$this->map(Ids::ACACIA_FENCE_GATE, fn(BlockStateReader $in) => Helper::decodeFenceGate(VanillaBlocks::ACACIA_FENCE_GATE(), $in));
		$this->map(Ids::ACACIA_PRESSURE_PLATE, fn(BlockStateReader $in) => Helper::decodeSimplePressurePlate(VanillaBlocks::ACACIA_PRESSURE_PLATE(), $in));
		$this->map(Ids::ACACIA_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::ACACIA_STAIRS(), $in));
		$this->map(Ids::ACACIA_STANDING_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::ACACIA_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::ACACIA_TRAPDOOR, fn(BlockStateReader $in) => Helper::decodeTrapdoor(VanillaBlocks::ACACIA_TRAPDOOR(), $in));
		$this->map(Ids::ACACIA_WALL_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::ACACIA_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::ACTIVATOR_RAIL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::ACTIVATOR_RAIL()
				->setPowered($in->readBool(BlockStateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(BlockStateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->map(Ids::AIR, fn() => VanillaBlocks::AIR());
		$this->map(Ids::ANDESITE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::ANDESITE_STAIRS(), $in));
		$this->map(Ids::ANVIL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::ANVIL()
				->setDamage(match($value = $in->readString(BlockStateNames::DAMAGE)){
					Values::DAMAGE_UNDAMAGED => 0,
					Values::DAMAGE_SLIGHTLY_DAMAGED => 1,
					Values::DAMAGE_VERY_DAMAGED => 2,
					Values::DAMAGE_BROKEN => 0,
					default => throw $in->badValueException(BlockStateNames::DAMAGE, $value),
				})
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::BAMBOO, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BAMBOO()
				->setLeafSize(match($value = $in->readString(BlockStateNames::BAMBOO_LEAF_SIZE)){
					Values::BAMBOO_LEAF_SIZE_NO_LEAVES => Bamboo::NO_LEAVES,
					Values::BAMBOO_LEAF_SIZE_SMALL_LEAVES => Bamboo::SMALL_LEAVES,
					Values::BAMBOO_LEAF_SIZE_LARGE_LEAVES => Bamboo::LARGE_LEAVES,
					default => throw $in->badValueException(BlockStateNames::BAMBOO_LEAF_SIZE, $value),
				})
				->setReady($in->readBool(BlockStateNames::AGE_BIT))
				->setThick(match($value = $in->readString(BlockStateNames::BAMBOO_STALK_THICKNESS)){
					Values::BAMBOO_STALK_THICKNESS_THIN => false,
					Values::BAMBOO_STALK_THICKNESS_THICK => true,
					default => throw $in->badValueException(BlockStateNames::BAMBOO_STALK_THICKNESS, $value),
				});
		});
		$this->map(Ids::BAMBOO_SAPLING, function(BlockStateReader $in) : Block{
			//TODO: sapling_type intentionally ignored (its presence is a bug)
			return VanillaBlocks::BAMBOO_SAPLING()->setReady($in->readBool(BlockStateNames::AGE_BIT));
		});
		$this->map(Ids::BARREL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BARREL()
				->setFacing($in->readFacingDirection())
				->setOpen($in->readBool(BlockStateNames::OPEN_BIT));
		});
		$this->map(Ids::BARRIER, fn() => VanillaBlocks::BARRIER());
		$this->map(Ids::BEACON, fn() => VanillaBlocks::BEACON());
		$this->map(Ids::BED, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BED()
				->setFacing($in->readLegacyHorizontalFacing())
				->setHead($in->readBool(BlockStateNames::HEAD_PIECE_BIT))
				->setOccupied($in->readBool(BlockStateNames::OCCUPIED_BIT));
		});
		$this->map(Ids::BEDROCK, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BEDROCK()
				->setBurnsForever($in->readBool(BlockStateNames::INFINIBURN_BIT));
		});
		$this->map(Ids::BEETROOT, fn(BlockStateReader $in) => Helper::decodeCrops(VanillaBlocks::BEETROOTS(), $in));
		$this->map(Ids::BELL, function(BlockStateReader $in) : Block{
			//TODO: ignored toggle_bit (appears to be internally used in MCPE only, useless for us)
			return VanillaBlocks::BELL()
				->setFacing($in->readLegacyHorizontalFacing())
				->setAttachmentType($in->readBellAttachmentType());
		});
		$this->map(Ids::BIRCH_BUTTON, fn(BlockStateReader $in) => Helper::decodeButton(VanillaBlocks::BIRCH_BUTTON(), $in));
		$this->map(Ids::BIRCH_DOOR, fn(BlockStateReader $in) => Helper::decodeDoor(VanillaBlocks::BIRCH_DOOR(), $in));
		$this->map(Ids::BIRCH_FENCE_GATE, fn(BlockStateReader $in) => Helper::decodeFenceGate(VanillaBlocks::BIRCH_FENCE_GATE(), $in));
		$this->map(Ids::BIRCH_PRESSURE_PLATE, fn(BlockStateReader $in) => Helper::decodeSimplePressurePlate(VanillaBlocks::BIRCH_PRESSURE_PLATE(), $in));
		$this->map(Ids::BIRCH_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::BIRCH_STAIRS(), $in));
		$this->map(Ids::BIRCH_STANDING_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BIRCH_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::BIRCH_TRAPDOOR, fn(BlockStateReader $in) => Helper::decodeTrapdoor(VanillaBlocks::BIRCH_TRAPDOOR(), $in));
		$this->map(Ids::BIRCH_WALL_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BIRCH_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::BLACK_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::BLACK_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::BLAST_FURNACE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::BLUE_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::BLUE_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::BLUE_ICE, fn() => VanillaBlocks::BLUE_ICE());
		$this->map(Ids::BONE_BLOCK, function(BlockStateReader $in) : Block{
			//TODO: intentionally ignored "deprecated" blockstate (useless)
			return VanillaBlocks::BONE_BLOCK()->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::BOOKSHELF, fn() => VanillaBlocks::BOOKSHELF());
		$this->map(Ids::BREWING_STAND, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BREWING_STAND()
				->setSlot(BrewingStandSlot::EAST(), $in->readBool(BlockStateNames::BREWING_STAND_SLOT_A_BIT))
				->setSlot(BrewingStandSlot::NORTHWEST(), $in->readBool(BlockStateNames::BREWING_STAND_SLOT_B_BIT))
				->setSlot(BrewingStandSlot::SOUTHWEST(), $in->readBool(BlockStateNames::BREWING_STAND_SLOT_C_BIT));
		});
		$this->map(Ids::BRICK_BLOCK, fn() => VanillaBlocks::BRICKS());
		$this->map(Ids::BRICK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::BRICK_STAIRS(), $in));
		$this->map(Ids::BROWN_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::BROWN_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::BROWN_MUSHROOM, fn() => VanillaBlocks::BROWN_MUSHROOM());
		$this->map(Ids::BROWN_MUSHROOM_BLOCK, fn(BlockStateReader $in) => Helper::decodeMushroomBlock(VanillaBlocks::BROWN_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::CACTUS, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CACTUS()
				->setAge($in->readBoundedInt(BlockStateNames::AGE, 0, 15));
		});
		$this->map(Ids::CAKE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CAKE()
				->setBites($in->readBoundedInt(BlockStateNames::BITE_COUNTER, 0, 6));
		});
		$this->map(Ids::CARPET, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CARPET()
				->setColor($in->readColor());
		});
		$this->map(Ids::CARROTS, fn(BlockStateReader $in) => Helper::decodeCrops(VanillaBlocks::CARROTS(), $in));
		$this->map(Ids::CARVED_PUMPKIN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CARVED_PUMPKIN()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::CHEMICAL_HEAT, fn() => VanillaBlocks::CHEMICAL_HEAT());
		$this->map(Ids::CHEMISTRY_TABLE, function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNames::CHEMISTRY_TABLE_TYPE)){
				Values::CHEMISTRY_TABLE_TYPE_COMPOUND_CREATOR => VanillaBlocks::COMPOUND_CREATOR(),
				Values::CHEMISTRY_TABLE_TYPE_ELEMENT_CONSTRUCTOR => VanillaBlocks::ELEMENT_CONSTRUCTOR(),
				Values::CHEMISTRY_TABLE_TYPE_LAB_TABLE => VanillaBlocks::LAB_TABLE(),
				Values::CHEMISTRY_TABLE_TYPE_MATERIAL_REDUCER => VanillaBlocks::MATERIAL_REDUCER(),
				default => throw $in->badValueException(BlockStateNames::CHEMISTRY_TABLE_TYPE, $type),
			})->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::CHEST, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::CLAY, fn() => VanillaBlocks::CLAY());
		$this->map(Ids::COAL_BLOCK, fn() => VanillaBlocks::COAL());
		$this->map(Ids::COAL_ORE, fn() => VanillaBlocks::COAL_ORE());
		$this->map(Ids::COBBLESTONE, fn() => VanillaBlocks::COBBLESTONE());
		$this->map(Ids::COBBLESTONE_WALL, fn(BlockStateReader $in) => Helper::decodeWall(VanillaBlocks::COBBLESTONE_WALL(), $in));
		$this->map(Ids::COCOA, function(BlockStateReader $in) : Block{
			return VanillaBlocks::COCOA_POD()
				->setAge($in->readBoundedInt(BlockStateNames::AGE, 0, 2))
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::COLORED_TORCH_BP, function(BlockStateReader $in) : Block{
			return $in->readBool(BlockStateNames::COLOR_BIT) ?
				VanillaBlocks::PURPLE_TORCH()->setFacing($in->readTorchFacing()) :
				VanillaBlocks::BLUE_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::COLORED_TORCH_RG, function(BlockStateReader $in) : Block{
			return $in->readBool(BlockStateNames::COLOR_BIT) ?
				VanillaBlocks::GREEN_TORCH()->setFacing($in->readTorchFacing()) :
				VanillaBlocks::RED_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::CONCRETE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CONCRETE()
				->setColor($in->readColor());
		});
		$this->map(Ids::CONCRETEPOWDER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CONCRETE_POWDER()
				->setColor($in->readColor());
		});
		$this->map(Ids::CORAL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CORAL()
				->setCoralType($in->readCoralType())
				->setDead($in->readBool(BlockStateNames::DEAD_BIT));
		});
		$this->map(Ids::CORAL_BLOCK, function(BlockStateReader $in) : Block{
			return VanillaBlocks::CORAL_BLOCK()
				->setCoralType($in->readCoralType())
				->setDead($in->readBool(BlockStateNames::DEAD_BIT));
		});
		$this->map(Ids::CORAL_FAN, fn(BlockStateReader $in) => Helper::decodeFloorCoralFan(VanillaBlocks::CORAL_FAN(), $in)->setDead(false));
		$this->map(Ids::CORAL_FAN_DEAD, fn(BlockStateReader $in) => Helper::decodeFloorCoralFan(VanillaBlocks::CORAL_FAN(), $in)->setDead(true));
		$this->map(Ids::CORAL_FAN_HANG, function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_CORAL_FAN()
				->setCoralType($in->readBool(BlockStateNames::CORAL_HANG_TYPE_BIT) ? CoralType::BRAIN() : CoralType::TUBE())
				->setDead($in->readBool(BlockStateNames::DEAD_BIT))
				->setFacing($in->readCoralFacing());
		});
		$this->map(Ids::CORAL_FAN_HANG2, function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_CORAL_FAN()
				->setCoralType($in->readBool(BlockStateNames::CORAL_HANG_TYPE_BIT) ? CoralType::FIRE() : CoralType::BUBBLE())
				->setDead($in->readBool(BlockStateNames::DEAD_BIT))
				->setFacing($in->readCoralFacing());
		});
		$this->map(Ids::CORAL_FAN_HANG3, function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_CORAL_FAN()
				->setCoralType(CoralType::HORN())
				->setDead($in->readBool(BlockStateNames::DEAD_BIT))
				->setFacing($in->readCoralFacing());
		});
		$this->map(Ids::CRAFTING_TABLE, fn() => VanillaBlocks::CRAFTING_TABLE());
		$this->map(Ids::CYAN_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::CYAN_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::DARK_OAK_BUTTON, fn(BlockStateReader $in) => Helper::decodeButton(VanillaBlocks::DARK_OAK_BUTTON(), $in));
		$this->map(Ids::DARK_OAK_DOOR, fn(BlockStateReader $in) => Helper::decodeDoor(VanillaBlocks::DARK_OAK_DOOR(), $in));
		$this->map(Ids::DARK_OAK_FENCE_GATE, fn(BlockStateReader $in) => Helper::decodeFenceGate(VanillaBlocks::DARK_OAK_FENCE_GATE(), $in));
		$this->map(Ids::DARK_OAK_PRESSURE_PLATE, fn(BlockStateReader $in) => Helper::decodeSimplePressurePlate(VanillaBlocks::DARK_OAK_PRESSURE_PLATE(), $in));
		$this->map(Ids::DARK_OAK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::DARK_OAK_STAIRS(), $in));
		$this->map(Ids::DARK_OAK_TRAPDOOR, fn(BlockStateReader $in) => Helper::decodeTrapdoor(VanillaBlocks::DARK_OAK_TRAPDOOR(), $in));
		$this->map(Ids::DARK_PRISMARINE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::DARK_PRISMARINE_STAIRS(), $in));
		$this->map(Ids::DARKOAK_STANDING_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::DARK_OAK_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::DARKOAK_WALL_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::DARK_OAK_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::DAYLIGHT_DETECTOR, function(BlockStateReader $in) : Block{
			return VanillaBlocks::DAYLIGHT_SENSOR()
				->setInverted(false)
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::DAYLIGHT_DETECTOR_INVERTED, function(BlockStateReader $in) : Block{
			return VanillaBlocks::DAYLIGHT_SENSOR()
				->setInverted(true)
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::DEADBUSH, fn() => VanillaBlocks::DEAD_BUSH());
		$this->map(Ids::DETECTOR_RAIL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::DETECTOR_RAIL()
				->setActivated($in->readBool(BlockStateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(BlockStateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->map(Ids::DIAMOND_BLOCK, fn() => VanillaBlocks::DIAMOND());
		$this->map(Ids::DIAMOND_ORE, fn() => VanillaBlocks::DIAMOND_ORE());
		$this->map(Ids::DIORITE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::DIORITE_STAIRS(), $in));
		$this->map(Ids::DIRT, function(BlockStateReader $in) : Block{
			return VanillaBlocks::DIRT()
				->setCoarse(match($value = $in->readString(BlockStateNames::DIRT_TYPE)){
					Values::DIRT_TYPE_NORMAL => false,
					Values::DIRT_TYPE_COARSE => true,
					default => throw $in->badValueException(BlockStateNames::DIRT_TYPE, $value),
				});
		});
		$this->map(Ids::DOUBLE_PLANT, function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNames::DOUBLE_PLANT_TYPE)){
				Values::DOUBLE_PLANT_TYPE_FERN => VanillaBlocks::LARGE_FERN(),
				Values::DOUBLE_PLANT_TYPE_GRASS => VanillaBlocks::DOUBLE_TALLGRASS(),
				Values::DOUBLE_PLANT_TYPE_PAEONIA => VanillaBlocks::PEONY(),
				Values::DOUBLE_PLANT_TYPE_ROSE => VanillaBlocks::ROSE_BUSH(),
				Values::DOUBLE_PLANT_TYPE_SUNFLOWER => VanillaBlocks::SUNFLOWER(),
				Values::DOUBLE_PLANT_TYPE_SYRINGA => VanillaBlocks::LILAC(),
				default => throw $in->badValueException(BlockStateNames::DOUBLE_PLANT_TYPE, $type),
			})->setTop($in->readBool(BlockStateNames::UPPER_BLOCK_BIT));
		});
		$this->map(Ids::DOUBLE_STONE_SLAB, function(BlockStateReader $in) : Block{
			return Helper::mapStoneSlab1Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_STONE_SLAB2, function(BlockStateReader $in) : Block{
			return Helper::mapStoneSlab2Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_STONE_SLAB3, function(BlockStateReader $in) : Block{
			return Helper::mapStoneSlab3Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_STONE_SLAB4, function(BlockStateReader $in) : Block{
			return Helper::mapStoneSlab4Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_WOODEN_SLAB, function(BlockStateReader $in) : Block{
			return Helper::mapWoodenSlabType($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DRAGON_EGG, fn() => VanillaBlocks::DRAGON_EGG());
		$this->map(Ids::DRIED_KELP_BLOCK, fn() => VanillaBlocks::DRIED_KELP());
		$this->map(Ids::ELEMENT_0, fn() => VanillaBlocks::ELEMENT_ZERO());
		$this->map(Ids::ELEMENT_1, fn() => VanillaBlocks::ELEMENT_HYDROGEN());
		$this->map(Ids::ELEMENT_10, fn() => VanillaBlocks::ELEMENT_NEON());
		$this->map(Ids::ELEMENT_100, fn() => VanillaBlocks::ELEMENT_FERMIUM());
		$this->map(Ids::ELEMENT_101, fn() => VanillaBlocks::ELEMENT_MENDELEVIUM());
		$this->map(Ids::ELEMENT_102, fn() => VanillaBlocks::ELEMENT_NOBELIUM());
		$this->map(Ids::ELEMENT_103, fn() => VanillaBlocks::ELEMENT_LAWRENCIUM());
		$this->map(Ids::ELEMENT_104, fn() => VanillaBlocks::ELEMENT_RUTHERFORDIUM());
		$this->map(Ids::ELEMENT_105, fn() => VanillaBlocks::ELEMENT_DUBNIUM());
		$this->map(Ids::ELEMENT_106, fn() => VanillaBlocks::ELEMENT_SEABORGIUM());
		$this->map(Ids::ELEMENT_107, fn() => VanillaBlocks::ELEMENT_BOHRIUM());
		$this->map(Ids::ELEMENT_108, fn() => VanillaBlocks::ELEMENT_HASSIUM());
		$this->map(Ids::ELEMENT_109, fn() => VanillaBlocks::ELEMENT_MEITNERIUM());
		$this->map(Ids::ELEMENT_11, fn() => VanillaBlocks::ELEMENT_SODIUM());
		$this->map(Ids::ELEMENT_110, fn() => VanillaBlocks::ELEMENT_DARMSTADTIUM());
		$this->map(Ids::ELEMENT_111, fn() => VanillaBlocks::ELEMENT_ROENTGENIUM());
		$this->map(Ids::ELEMENT_112, fn() => VanillaBlocks::ELEMENT_COPERNICIUM());
		$this->map(Ids::ELEMENT_113, fn() => VanillaBlocks::ELEMENT_NIHONIUM());
		$this->map(Ids::ELEMENT_114, fn() => VanillaBlocks::ELEMENT_FLEROVIUM());
		$this->map(Ids::ELEMENT_115, fn() => VanillaBlocks::ELEMENT_MOSCOVIUM());
		$this->map(Ids::ELEMENT_116, fn() => VanillaBlocks::ELEMENT_LIVERMORIUM());
		$this->map(Ids::ELEMENT_117, fn() => VanillaBlocks::ELEMENT_TENNESSINE());
		$this->map(Ids::ELEMENT_118, fn() => VanillaBlocks::ELEMENT_OGANESSON());
		$this->map(Ids::ELEMENT_12, fn() => VanillaBlocks::ELEMENT_MAGNESIUM());
		$this->map(Ids::ELEMENT_13, fn() => VanillaBlocks::ELEMENT_ALUMINUM());
		$this->map(Ids::ELEMENT_14, fn() => VanillaBlocks::ELEMENT_SILICON());
		$this->map(Ids::ELEMENT_15, fn() => VanillaBlocks::ELEMENT_PHOSPHORUS());
		$this->map(Ids::ELEMENT_16, fn() => VanillaBlocks::ELEMENT_SULFUR());
		$this->map(Ids::ELEMENT_17, fn() => VanillaBlocks::ELEMENT_CHLORINE());
		$this->map(Ids::ELEMENT_18, fn() => VanillaBlocks::ELEMENT_ARGON());
		$this->map(Ids::ELEMENT_19, fn() => VanillaBlocks::ELEMENT_POTASSIUM());
		$this->map(Ids::ELEMENT_2, fn() => VanillaBlocks::ELEMENT_HELIUM());
		$this->map(Ids::ELEMENT_20, fn() => VanillaBlocks::ELEMENT_CALCIUM());
		$this->map(Ids::ELEMENT_21, fn() => VanillaBlocks::ELEMENT_SCANDIUM());
		$this->map(Ids::ELEMENT_22, fn() => VanillaBlocks::ELEMENT_TITANIUM());
		$this->map(Ids::ELEMENT_23, fn() => VanillaBlocks::ELEMENT_VANADIUM());
		$this->map(Ids::ELEMENT_24, fn() => VanillaBlocks::ELEMENT_CHROMIUM());
		$this->map(Ids::ELEMENT_25, fn() => VanillaBlocks::ELEMENT_MANGANESE());
		$this->map(Ids::ELEMENT_26, fn() => VanillaBlocks::ELEMENT_IRON());
		$this->map(Ids::ELEMENT_27, fn() => VanillaBlocks::ELEMENT_COBALT());
		$this->map(Ids::ELEMENT_28, fn() => VanillaBlocks::ELEMENT_NICKEL());
		$this->map(Ids::ELEMENT_29, fn() => VanillaBlocks::ELEMENT_COPPER());
		$this->map(Ids::ELEMENT_3, fn() => VanillaBlocks::ELEMENT_LITHIUM());
		$this->map(Ids::ELEMENT_30, fn() => VanillaBlocks::ELEMENT_ZINC());
		$this->map(Ids::ELEMENT_31, fn() => VanillaBlocks::ELEMENT_GALLIUM());
		$this->map(Ids::ELEMENT_32, fn() => VanillaBlocks::ELEMENT_GERMANIUM());
		$this->map(Ids::ELEMENT_33, fn() => VanillaBlocks::ELEMENT_ARSENIC());
		$this->map(Ids::ELEMENT_34, fn() => VanillaBlocks::ELEMENT_SELENIUM());
		$this->map(Ids::ELEMENT_35, fn() => VanillaBlocks::ELEMENT_BROMINE());
		$this->map(Ids::ELEMENT_36, fn() => VanillaBlocks::ELEMENT_KRYPTON());
		$this->map(Ids::ELEMENT_37, fn() => VanillaBlocks::ELEMENT_RUBIDIUM());
		$this->map(Ids::ELEMENT_38, fn() => VanillaBlocks::ELEMENT_STRONTIUM());
		$this->map(Ids::ELEMENT_39, fn() => VanillaBlocks::ELEMENT_YTTRIUM());
		$this->map(Ids::ELEMENT_4, fn() => VanillaBlocks::ELEMENT_BERYLLIUM());
		$this->map(Ids::ELEMENT_40, fn() => VanillaBlocks::ELEMENT_ZIRCONIUM());
		$this->map(Ids::ELEMENT_41, fn() => VanillaBlocks::ELEMENT_NIOBIUM());
		$this->map(Ids::ELEMENT_42, fn() => VanillaBlocks::ELEMENT_MOLYBDENUM());
		$this->map(Ids::ELEMENT_43, fn() => VanillaBlocks::ELEMENT_TECHNETIUM());
		$this->map(Ids::ELEMENT_44, fn() => VanillaBlocks::ELEMENT_RUTHENIUM());
		$this->map(Ids::ELEMENT_45, fn() => VanillaBlocks::ELEMENT_RHODIUM());
		$this->map(Ids::ELEMENT_46, fn() => VanillaBlocks::ELEMENT_PALLADIUM());
		$this->map(Ids::ELEMENT_47, fn() => VanillaBlocks::ELEMENT_SILVER());
		$this->map(Ids::ELEMENT_48, fn() => VanillaBlocks::ELEMENT_CADMIUM());
		$this->map(Ids::ELEMENT_49, fn() => VanillaBlocks::ELEMENT_INDIUM());
		$this->map(Ids::ELEMENT_5, fn() => VanillaBlocks::ELEMENT_BORON());
		$this->map(Ids::ELEMENT_50, fn() => VanillaBlocks::ELEMENT_TIN());
		$this->map(Ids::ELEMENT_51, fn() => VanillaBlocks::ELEMENT_ANTIMONY());
		$this->map(Ids::ELEMENT_52, fn() => VanillaBlocks::ELEMENT_TELLURIUM());
		$this->map(Ids::ELEMENT_53, fn() => VanillaBlocks::ELEMENT_IODINE());
		$this->map(Ids::ELEMENT_54, fn() => VanillaBlocks::ELEMENT_XENON());
		$this->map(Ids::ELEMENT_55, fn() => VanillaBlocks::ELEMENT_CESIUM());
		$this->map(Ids::ELEMENT_56, fn() => VanillaBlocks::ELEMENT_BARIUM());
		$this->map(Ids::ELEMENT_57, fn() => VanillaBlocks::ELEMENT_LANTHANUM());
		$this->map(Ids::ELEMENT_58, fn() => VanillaBlocks::ELEMENT_CERIUM());
		$this->map(Ids::ELEMENT_59, fn() => VanillaBlocks::ELEMENT_PRASEODYMIUM());
		$this->map(Ids::ELEMENT_6, fn() => VanillaBlocks::ELEMENT_CARBON());
		$this->map(Ids::ELEMENT_60, fn() => VanillaBlocks::ELEMENT_NEODYMIUM());
		$this->map(Ids::ELEMENT_61, fn() => VanillaBlocks::ELEMENT_PROMETHIUM());
		$this->map(Ids::ELEMENT_62, fn() => VanillaBlocks::ELEMENT_SAMARIUM());
		$this->map(Ids::ELEMENT_63, fn() => VanillaBlocks::ELEMENT_EUROPIUM());
		$this->map(Ids::ELEMENT_64, fn() => VanillaBlocks::ELEMENT_GADOLINIUM());
		$this->map(Ids::ELEMENT_65, fn() => VanillaBlocks::ELEMENT_TERBIUM());
		$this->map(Ids::ELEMENT_66, fn() => VanillaBlocks::ELEMENT_DYSPROSIUM());
		$this->map(Ids::ELEMENT_67, fn() => VanillaBlocks::ELEMENT_HOLMIUM());
		$this->map(Ids::ELEMENT_68, fn() => VanillaBlocks::ELEMENT_ERBIUM());
		$this->map(Ids::ELEMENT_69, fn() => VanillaBlocks::ELEMENT_THULIUM());
		$this->map(Ids::ELEMENT_7, fn() => VanillaBlocks::ELEMENT_NITROGEN());
		$this->map(Ids::ELEMENT_70, fn() => VanillaBlocks::ELEMENT_YTTERBIUM());
		$this->map(Ids::ELEMENT_71, fn() => VanillaBlocks::ELEMENT_LUTETIUM());
		$this->map(Ids::ELEMENT_72, fn() => VanillaBlocks::ELEMENT_HAFNIUM());
		$this->map(Ids::ELEMENT_73, fn() => VanillaBlocks::ELEMENT_TANTALUM());
		$this->map(Ids::ELEMENT_74, fn() => VanillaBlocks::ELEMENT_TUNGSTEN());
		$this->map(Ids::ELEMENT_75, fn() => VanillaBlocks::ELEMENT_RHENIUM());
		$this->map(Ids::ELEMENT_76, fn() => VanillaBlocks::ELEMENT_OSMIUM());
		$this->map(Ids::ELEMENT_77, fn() => VanillaBlocks::ELEMENT_IRIDIUM());
		$this->map(Ids::ELEMENT_78, fn() => VanillaBlocks::ELEMENT_PLATINUM());
		$this->map(Ids::ELEMENT_79, fn() => VanillaBlocks::ELEMENT_GOLD());
		$this->map(Ids::ELEMENT_8, fn() => VanillaBlocks::ELEMENT_OXYGEN());
		$this->map(Ids::ELEMENT_80, fn() => VanillaBlocks::ELEMENT_MERCURY());
		$this->map(Ids::ELEMENT_81, fn() => VanillaBlocks::ELEMENT_THALLIUM());
		$this->map(Ids::ELEMENT_82, fn() => VanillaBlocks::ELEMENT_LEAD());
		$this->map(Ids::ELEMENT_83, fn() => VanillaBlocks::ELEMENT_BISMUTH());
		$this->map(Ids::ELEMENT_84, fn() => VanillaBlocks::ELEMENT_POLONIUM());
		$this->map(Ids::ELEMENT_85, fn() => VanillaBlocks::ELEMENT_ASTATINE());
		$this->map(Ids::ELEMENT_86, fn() => VanillaBlocks::ELEMENT_RADON());
		$this->map(Ids::ELEMENT_87, fn() => VanillaBlocks::ELEMENT_FRANCIUM());
		$this->map(Ids::ELEMENT_88, fn() => VanillaBlocks::ELEMENT_RADIUM());
		$this->map(Ids::ELEMENT_89, fn() => VanillaBlocks::ELEMENT_ACTINIUM());
		$this->map(Ids::ELEMENT_9, fn() => VanillaBlocks::ELEMENT_FLUORINE());
		$this->map(Ids::ELEMENT_90, fn() => VanillaBlocks::ELEMENT_THORIUM());
		$this->map(Ids::ELEMENT_91, fn() => VanillaBlocks::ELEMENT_PROTACTINIUM());
		$this->map(Ids::ELEMENT_92, fn() => VanillaBlocks::ELEMENT_URANIUM());
		$this->map(Ids::ELEMENT_93, fn() => VanillaBlocks::ELEMENT_NEPTUNIUM());
		$this->map(Ids::ELEMENT_94, fn() => VanillaBlocks::ELEMENT_PLUTONIUM());
		$this->map(Ids::ELEMENT_95, fn() => VanillaBlocks::ELEMENT_AMERICIUM());
		$this->map(Ids::ELEMENT_96, fn() => VanillaBlocks::ELEMENT_CURIUM());
		$this->map(Ids::ELEMENT_97, fn() => VanillaBlocks::ELEMENT_BERKELIUM());
		$this->map(Ids::ELEMENT_98, fn() => VanillaBlocks::ELEMENT_CALIFORNIUM());
		$this->map(Ids::ELEMENT_99, fn() => VanillaBlocks::ELEMENT_EINSTEINIUM());
		$this->map(Ids::EMERALD_BLOCK, fn() => VanillaBlocks::EMERALD());
		$this->map(Ids::EMERALD_ORE, fn() => VanillaBlocks::EMERALD_ORE());
		$this->map(Ids::ENCHANTING_TABLE, fn() => VanillaBlocks::ENCHANTING_TABLE());
		$this->map(Ids::END_BRICK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::END_STONE_BRICK_STAIRS(), $in));
		$this->map(Ids::END_BRICKS, fn() => VanillaBlocks::END_STONE_BRICKS());
		$this->map(Ids::END_PORTAL_FRAME, function(BlockStateReader $in) : Block{
			return VanillaBlocks::END_PORTAL_FRAME()
				->setEye($in->readBool(BlockStateNames::END_PORTAL_EYE_BIT))
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::END_ROD, function(BlockStateReader $in) : Block{
			return VanillaBlocks::END_ROD()
				->setFacing($in->readFacingDirection());
		});
		$this->map(Ids::END_STONE, fn() => VanillaBlocks::END_STONE());
		$this->map(Ids::ENDER_CHEST, function(BlockStateReader $in) : Block{
			return VanillaBlocks::ENDER_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::FARMLAND, function(BlockStateReader $in) : Block{
			return VanillaBlocks::FARMLAND()
				->setWetness($in->readBoundedInt(BlockStateNames::MOISTURIZED_AMOUNT, 0, 7));
		});
		$this->map(Ids::FENCE, function(BlockStateReader $in) : Block{
			return match($woodName = $in->readString(BlockStateNames::WOOD_TYPE)){
				Values::WOOD_TYPE_OAK => VanillaBlocks::OAK_FENCE(),
				Values::WOOD_TYPE_SPRUCE => VanillaBlocks::SPRUCE_FENCE(),
				Values::WOOD_TYPE_BIRCH => VanillaBlocks::BIRCH_FENCE(),
				Values::WOOD_TYPE_JUNGLE => VanillaBlocks::JUNGLE_FENCE(),
				Values::WOOD_TYPE_ACACIA => VanillaBlocks::ACACIA_FENCE(),
				Values::WOOD_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_FENCE(),
				default => throw $in->badValueException(BlockStateNames::WOOD_TYPE, $woodName),
			};
		});
		$this->map(Ids::FENCE_GATE, fn(BlockStateReader $in) => Helper::decodeFenceGate(VanillaBlocks::OAK_FENCE_GATE(), $in));
		$this->map(Ids::FIRE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::FIRE()
				->setAge($in->readBoundedInt(BlockStateNames::AGE, 0, 15));
		});
		$this->map(Ids::FLETCHING_TABLE, fn() => VanillaBlocks::FLETCHING_TABLE());
		$this->map(Ids::FLOWER_POT, function() : Block{
			//TODO: ignored update_bit (only useful on network to make the client actually render contents, not needed on disk)
			return VanillaBlocks::FLOWER_POT();
		});
		$this->map(Ids::FLOWING_LAVA, fn(BlockStateReader $in) => Helper::decodeFlowingLiquid(VanillaBlocks::LAVA(), $in));
		$this->map(Ids::FLOWING_WATER, fn(BlockStateReader $in) => Helper::decodeFlowingLiquid(VanillaBlocks::WATER(), $in));
		$this->map(Ids::FRAME, function(BlockStateReader $in) : Block{
			//TODO: in R13 this can be any side, not just horizontal
			return VanillaBlocks::ITEM_FRAME()
				->setFacing($in->readHorizontalFacing())
				->setHasMap($in->readBool(BlockStateNames::ITEM_FRAME_MAP_BIT));
		});
		$this->map(Ids::FROSTED_ICE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::FROSTED_ICE()
				->setAge($in->readBoundedInt(BlockStateNames::AGE, 0, 3));
		});
		$this->map(Ids::FURNACE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::GLASS, fn() => VanillaBlocks::GLASS());
		$this->map(Ids::GLASS_PANE, fn() => VanillaBlocks::GLASS_PANE());
		$this->map(Ids::GLOWINGOBSIDIAN, fn() => VanillaBlocks::GLOWING_OBSIDIAN());
		$this->map(Ids::GLOWSTONE, fn() => VanillaBlocks::GLOWSTONE());
		$this->map(Ids::GOLD_BLOCK, fn() => VanillaBlocks::GOLD());
		$this->map(Ids::GOLD_ORE, fn() => VanillaBlocks::GOLD_ORE());
		$this->map(Ids::GOLDEN_RAIL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::POWERED_RAIL()
				->setPowered($in->readBool(BlockStateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(BlockStateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->map(Ids::GRANITE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::GRANITE_STAIRS(), $in));
		$this->map(Ids::GRASS, fn() => VanillaBlocks::GRASS());
		$this->map(Ids::GRASS_PATH, fn() => VanillaBlocks::GRASS_PATH());
		$this->map(Ids::GRAVEL, fn() => VanillaBlocks::GRAVEL());
		$this->map(Ids::GRAY_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::GRAY_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::GREEN_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::GREEN_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::HARD_GLASS, fn() => VanillaBlocks::HARDENED_GLASS());
		$this->map(Ids::HARD_GLASS_PANE, fn() => VanillaBlocks::HARDENED_GLASS_PANE());
		$this->map(Ids::HARD_STAINED_GLASS, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_HARDENED_GLASS()
				->setColor($in->readColor());
		});
		$this->map(Ids::HARD_STAINED_GLASS_PANE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_HARDENED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->map(Ids::HARDENED_CLAY, fn() => VanillaBlocks::HARDENED_CLAY());
		$this->map(Ids::HAY_BLOCK, function(BlockStateReader $in) : Block{
			//TODO: intentionally ignored "deprecated" blockstate (useless)
			return VanillaBlocks::HAY_BALE()->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::WEIGHTED_PRESSURE_PLATE_HEAVY()
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::HOPPER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::HOPPER()
				->setFacing($in->readFacingWithoutUp())
				->setPowered($in->readBool(BlockStateNames::TOGGLE_BIT));
		});
		$this->map(Ids::ICE, fn() => VanillaBlocks::ICE());
		$this->map(Ids::INFO_UPDATE, fn() => VanillaBlocks::INFO_UPDATE());
		$this->map(Ids::INFO_UPDATE2, fn() => VanillaBlocks::INFO_UPDATE2());
		$this->map(Ids::INVISIBLEBEDROCK, fn() => VanillaBlocks::INVISIBLE_BEDROCK());
		$this->map(Ids::IRON_BARS, fn() => VanillaBlocks::IRON_BARS());
		$this->map(Ids::IRON_BLOCK, fn() => VanillaBlocks::IRON());
		$this->map(Ids::IRON_DOOR, fn(BlockStateReader $in) => Helper::decodeDoor(VanillaBlocks::IRON_DOOR(), $in));
		$this->map(Ids::IRON_ORE, fn() => VanillaBlocks::IRON_ORE());
		$this->map(Ids::IRON_TRAPDOOR, fn(BlockStateReader $in) => Helper::decodeTrapdoor(VanillaBlocks::IRON_TRAPDOOR(), $in));
		$this->map(Ids::JUKEBOX, fn() => VanillaBlocks::JUKEBOX());
		$this->map(Ids::JUNGLE_BUTTON, fn(BlockStateReader $in) => Helper::decodeButton(VanillaBlocks::JUNGLE_BUTTON(), $in));
		$this->map(Ids::JUNGLE_DOOR, fn(BlockStateReader $in) => Helper::decodeDoor(VanillaBlocks::JUNGLE_DOOR(), $in));
		$this->map(Ids::JUNGLE_FENCE_GATE, fn(BlockStateReader $in) => Helper::decodeFenceGate(VanillaBlocks::JUNGLE_FENCE_GATE(), $in));
		$this->map(Ids::JUNGLE_PRESSURE_PLATE, fn(BlockStateReader $in) => Helper::decodeSimplePressurePlate(VanillaBlocks::JUNGLE_PRESSURE_PLATE(), $in));
		$this->map(Ids::JUNGLE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::JUNGLE_STAIRS(), $in));
		$this->map(Ids::JUNGLE_STANDING_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::JUNGLE_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::JUNGLE_TRAPDOOR, fn(BlockStateReader $in) => Helper::decodeTrapdoor(VanillaBlocks::JUNGLE_TRAPDOOR(), $in));
		$this->map(Ids::JUNGLE_WALL_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::JUNGLE_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::LADDER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::LADDER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::LANTERN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::LANTERN()
				->setHanging($in->readBool(BlockStateNames::HANGING));
		});
		$this->map(Ids::LAPIS_BLOCK, fn() => VanillaBlocks::LAPIS_LAZULI());
		$this->map(Ids::LAPIS_ORE, fn() => VanillaBlocks::LAPIS_LAZULI_ORE());
		$this->map(Ids::LAVA, fn(BlockStateReader $in) => Helper::decodeStillLiquid(VanillaBlocks::LAVA(), $in));
		$this->map(Ids::LEAVES, function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNames::OLD_LEAF_TYPE)){
					Values::OLD_LEAF_TYPE_BIRCH => VanillaBlocks::BIRCH_LEAVES(),
					Values::OLD_LEAF_TYPE_JUNGLE => VanillaBlocks::JUNGLE_LEAVES(),
					Values::OLD_LEAF_TYPE_OAK => VanillaBlocks::OAK_LEAVES(),
					Values::OLD_LEAF_TYPE_SPRUCE => VanillaBlocks::SPRUCE_LEAVES(),
					default => throw $in->badValueException(BlockStateNames::OLD_LEAF_TYPE, $type),
				})
				->setNoDecay($in->readBool(BlockStateNames::PERSISTENT_BIT))
				->setCheckDecay($in->readBool(BlockStateNames::UPDATE_BIT));
		});
		$this->map(Ids::LEAVES2, function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNames::NEW_LEAF_TYPE)){
					Values::NEW_LEAF_TYPE_ACACIA => VanillaBlocks::ACACIA_LEAVES(),
					Values::NEW_LEAF_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_LEAVES(),
					default => throw $in->badValueException(BlockStateNames::NEW_LEAF_TYPE, $type),
				})
				->setNoDecay($in->readBool(BlockStateNames::PERSISTENT_BIT))
				->setCheckDecay($in->readBool(BlockStateNames::UPDATE_BIT));
		});
		$this->map(Ids::LECTERN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::LECTERN()
				->setFacing($in->readLegacyHorizontalFacing())
				->setProducingSignal($in->readBool(BlockStateNames::POWERED_BIT));
		});
		$this->map(Ids::LEVER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::LEVER()
				->setActivated($in->readBool(BlockStateNames::OPEN_BIT))
				->setFacing(match($value = $in->readString(BlockStateNames::LEVER_DIRECTION)){
					Values::LEVER_DIRECTION_DOWN_NORTH_SOUTH => LeverFacing::DOWN_AXIS_Z(),
					Values::LEVER_DIRECTION_DOWN_EAST_WEST => LeverFacing::DOWN_AXIS_X(),
					Values::LEVER_DIRECTION_UP_NORTH_SOUTH => LeverFacing::UP_AXIS_Z(),
					Values::LEVER_DIRECTION_UP_EAST_WEST => LeverFacing::UP_AXIS_X(),
					Values::LEVER_DIRECTION_NORTH => LeverFacing::NORTH(),
					Values::LEVER_DIRECTION_SOUTH => LeverFacing::SOUTH(),
					Values::LEVER_DIRECTION_WEST => LeverFacing::WEST(),
					Values::LEVER_DIRECTION_EAST => LeverFacing::EAST(),
					default => throw $in->badValueException(BlockStateNames::LEVER_DIRECTION, $value),
				});
		});
		$this->map(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::LIGHT_BLUE_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::WEIGHTED_PRESSURE_PLATE_LIGHT()
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::LIME_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::LIME_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::LIT_BLAST_FURNACE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_FURNACE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_PUMPKIN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::LIT_PUMPKIN()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::LIT_REDSTONE_LAMP, function() : Block{
			return VanillaBlocks::REDSTONE_LAMP()
				->setPowered(true);
		});
		$this->map(Ids::LIT_REDSTONE_ORE, function() : Block{
			return VanillaBlocks::REDSTONE_ORE()
				->setLit(true);
		});
		$this->map(Ids::LIT_SMOKER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::SMOKER()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LOG, function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNames::OLD_LOG_TYPE)){
					Values::OLD_LOG_TYPE_BIRCH => VanillaBlocks::BIRCH_LOG(),
					Values::OLD_LOG_TYPE_JUNGLE => VanillaBlocks::JUNGLE_LOG(),
					Values::OLD_LOG_TYPE_OAK => VanillaBlocks::OAK_LOG(),
					Values::OLD_LOG_TYPE_SPRUCE => VanillaBlocks::SPRUCE_LOG(),
					default => throw $in->badValueException(BlockStateNames::OLD_LOG_TYPE, $type),
				})
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::LOG2, function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNames::NEW_LOG_TYPE)){
					Values::NEW_LOG_TYPE_ACACIA => VanillaBlocks::ACACIA_LOG(),
					Values::NEW_LOG_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_LOG(),
					default => throw $in->badValueException(BlockStateNames::NEW_LOG_TYPE, $type),
				})
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::LOOM, function(BlockStateReader $in) : Block{
			return VanillaBlocks::LOOM()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::MAGENTA_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::MAGENTA_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::MAGMA, fn() => VanillaBlocks::MAGMA());
		$this->map(Ids::MELON_BLOCK, fn() => VanillaBlocks::MELON());
		$this->map(Ids::MELON_STEM, fn(BlockStateReader $in) => Helper::decodeStem(VanillaBlocks::MELON_STEM(), $in));
		$this->map(Ids::MOB_SPAWNER, fn() => VanillaBlocks::MONSTER_SPAWNER());
		$this->map(Ids::MONSTER_EGG, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::MONSTER_EGG_STONE_TYPE)){
				Values::MONSTER_EGG_STONE_TYPE_CHISELED_STONE_BRICK => VanillaBlocks::INFESTED_CHISELED_STONE_BRICK(),
				Values::MONSTER_EGG_STONE_TYPE_COBBLESTONE => VanillaBlocks::INFESTED_COBBLESTONE(),
				Values::MONSTER_EGG_STONE_TYPE_CRACKED_STONE_BRICK => VanillaBlocks::INFESTED_CRACKED_STONE_BRICK(),
				Values::MONSTER_EGG_STONE_TYPE_MOSSY_STONE_BRICK => VanillaBlocks::INFESTED_MOSSY_STONE_BRICK(),
				Values::MONSTER_EGG_STONE_TYPE_STONE => VanillaBlocks::INFESTED_STONE(),
				Values::MONSTER_EGG_STONE_TYPE_STONE_BRICK => VanillaBlocks::INFESTED_STONE_BRICK(),
				default => throw $in->badValueException(BlockStateNames::MONSTER_EGG_STONE_TYPE, $type),
			};
		});
		$this->map(Ids::MOSSY_COBBLESTONE, fn() => VanillaBlocks::MOSSY_COBBLESTONE());
		$this->map(Ids::MOSSY_COBBLESTONE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::MOSSY_COBBLESTONE_STAIRS(), $in));
		$this->map(Ids::MOSSY_STONE_BRICK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::MOSSY_STONE_BRICK_STAIRS(), $in));
		$this->map(Ids::MYCELIUM, fn() => VanillaBlocks::MYCELIUM());
		$this->map(Ids::NETHER_BRICK, fn() => VanillaBlocks::NETHER_BRICKS());
		$this->map(Ids::NETHER_BRICK_FENCE, fn() => VanillaBlocks::NETHER_BRICK_FENCE());
		$this->map(Ids::NETHER_BRICK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::NETHER_BRICK_STAIRS(), $in));
		$this->map(Ids::NETHER_WART, function(BlockStateReader $in) : Block{
			return VanillaBlocks::NETHER_WART()
				->setAge($in->readBoundedInt(BlockStateNames::AGE, 0, 3));
		});
		$this->map(Ids::NETHER_WART_BLOCK, fn() => VanillaBlocks::NETHER_WART_BLOCK());
		$this->map(Ids::NETHERRACK, fn() => VanillaBlocks::NETHERRACK());
		$this->map(Ids::NETHERREACTOR, fn() => VanillaBlocks::NETHER_REACTOR_CORE());
		$this->map(Ids::NORMAL_STONE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::STONE_STAIRS(), $in));
		$this->map(Ids::NOTEBLOCK, fn() => VanillaBlocks::NOTE_BLOCK());
		$this->map(Ids::OAK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::OAK_STAIRS(), $in));
		$this->map(Ids::OBSIDIAN, fn() => VanillaBlocks::OBSIDIAN());
		$this->map(Ids::ORANGE_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::ORANGE_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::PACKED_ICE, fn() => VanillaBlocks::PACKED_ICE());
		$this->map(Ids::PINK_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::PINK_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::PLANKS, function(BlockStateReader $in) : Block{
			return match($woodName = $in->readString(BlockStateNames::WOOD_TYPE)){
				Values::WOOD_TYPE_OAK => VanillaBlocks::OAK_PLANKS(),
				Values::WOOD_TYPE_SPRUCE => VanillaBlocks::SPRUCE_PLANKS(),
				Values::WOOD_TYPE_BIRCH => VanillaBlocks::BIRCH_PLANKS(),
				Values::WOOD_TYPE_JUNGLE => VanillaBlocks::JUNGLE_PLANKS(),
				Values::WOOD_TYPE_ACACIA => VanillaBlocks::ACACIA_PLANKS(),
				Values::WOOD_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_PLANKS(),
				default => throw $in->badValueException(BlockStateNames::WOOD_TYPE, $woodName),
			};
		});
		$this->map(Ids::PODZOL, fn() => VanillaBlocks::PODZOL());
		$this->map(Ids::POLISHED_ANDESITE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::POLISHED_ANDESITE_STAIRS(), $in));
		$this->map(Ids::POLISHED_DIORITE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::POLISHED_DIORITE_STAIRS(), $in));
		$this->map(Ids::POLISHED_GRANITE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::POLISHED_GRANITE_STAIRS(), $in));
		$this->map(Ids::PORTAL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::NETHER_PORTAL()
				->setAxis(match($value = $in->readString(BlockStateNames::PORTAL_AXIS)){
					Values::PORTAL_AXIS_UNKNOWN => Axis::X,
					Values::PORTAL_AXIS_X => Axis::X,
					Values::PORTAL_AXIS_Z => Axis::Z,
					default => throw $in->badValueException(BlockStateNames::PORTAL_AXIS, $value),
				});
		});
		$this->map(Ids::POTATOES, fn(BlockStateReader $in) => Helper::decodeCrops(VanillaBlocks::POTATOES(), $in));
		$this->map(Ids::POWERED_COMPARATOR, fn(BlockStateReader $in) => Helper::decodeComparator(VanillaBlocks::REDSTONE_COMPARATOR(), $in));
		$this->map(Ids::POWERED_REPEATER, fn(BlockStateReader $in) => Helper::decodeRepeater(VanillaBlocks::REDSTONE_REPEATER(), $in)
				->setPowered(true));
		$this->map(Ids::PRISMARINE, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::PRISMARINE_BLOCK_TYPE)){
				Values::PRISMARINE_BLOCK_TYPE_BRICKS => VanillaBlocks::PRISMARINE_BRICKS(),
				Values::PRISMARINE_BLOCK_TYPE_DARK => VanillaBlocks::DARK_PRISMARINE(),
				Values::PRISMARINE_BLOCK_TYPE_DEFAULT => VanillaBlocks::PRISMARINE(),
				default => throw $in->badValueException(BlockStateNames::PRISMARINE_BLOCK_TYPE, $type),
			};
		});
		$this->map(Ids::PRISMARINE_BRICKS_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::PRISMARINE_BRICKS_STAIRS(), $in));
		$this->map(Ids::PRISMARINE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::PRISMARINE_STAIRS(), $in));
		$this->map(Ids::PUMPKIN, function() : Block{
			//TODO: intentionally ignored "direction" property (obsolete)
			return VanillaBlocks::PUMPKIN();
		});
		$this->map(Ids::PUMPKIN_STEM, fn(BlockStateReader $in) => Helper::decodeStem(VanillaBlocks::PUMPKIN_STEM(), $in));
		$this->map(Ids::PURPLE_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::PURPLE_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::PURPUR_BLOCK, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::CHISEL_TYPE)){
				Values::CHISEL_TYPE_CHISELED, //TODO: bug in MCPE
				Values::CHISEL_TYPE_SMOOTH, //TODO: bug in MCPE
				Values::CHISEL_TYPE_DEFAULT => VanillaBlocks::PURPUR(), //TODO: axis intentionally ignored (useless)
				Values::CHISEL_TYPE_LINES => VanillaBlocks::PURPUR_PILLAR()->setAxis($in->readPillarAxis()),
				default => throw $in->badValueException(BlockStateNames::CHISEL_TYPE, $type),
			};
		});
		$this->map(Ids::PURPUR_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::PURPUR_STAIRS(), $in));
		$this->map(Ids::QUARTZ_BLOCK, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::CHISEL_TYPE)){
				Values::CHISEL_TYPE_CHISELED => VanillaBlocks::CHISELED_QUARTZ()->setAxis($in->readPillarAxis()),
				Values::CHISEL_TYPE_DEFAULT => VanillaBlocks::QUARTZ(), //TODO: axis intentionally ignored (useless)
				Values::CHISEL_TYPE_LINES => VanillaBlocks::QUARTZ_PILLAR()->setAxis($in->readPillarAxis()),
				Values::CHISEL_TYPE_SMOOTH => VanillaBlocks::SMOOTH_QUARTZ(), //TODO: axis intentionally ignored (useless)
				default => throw $in->badValueException(BlockStateNames::CHISEL_TYPE, $type),
			};
		});
		$this->map(Ids::QUARTZ_ORE, fn() => VanillaBlocks::NETHER_QUARTZ_ORE());
		$this->map(Ids::QUARTZ_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::QUARTZ_STAIRS(), $in));
		$this->map(Ids::RAIL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::RAIL()
				->setShape($in->readBoundedInt(BlockStateNames::RAIL_DIRECTION, 0, 9));
		});
		$this->map(Ids::RED_FLOWER, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::FLOWER_TYPE)){
				Values::FLOWER_TYPE_ALLIUM => VanillaBlocks::ALLIUM(),
				Values::FLOWER_TYPE_CORNFLOWER => VanillaBlocks::CORNFLOWER(),
				Values::FLOWER_TYPE_HOUSTONIA => VanillaBlocks::AZURE_BLUET(), //wtf ???
				Values::FLOWER_TYPE_LILY_OF_THE_VALLEY => VanillaBlocks::LILY_OF_THE_VALLEY(),
				Values::FLOWER_TYPE_ORCHID => VanillaBlocks::BLUE_ORCHID(),
				Values::FLOWER_TYPE_OXEYE => VanillaBlocks::OXEYE_DAISY(),
				Values::FLOWER_TYPE_POPPY => VanillaBlocks::POPPY(),
				Values::FLOWER_TYPE_TULIP_ORANGE => VanillaBlocks::ORANGE_TULIP(),
				Values::FLOWER_TYPE_TULIP_PINK => VanillaBlocks::PINK_TULIP(),
				Values::FLOWER_TYPE_TULIP_RED => VanillaBlocks::RED_TULIP(),
				Values::FLOWER_TYPE_TULIP_WHITE => VanillaBlocks::WHITE_TULIP(),
				default => throw $in->badValueException(BlockStateNames::FLOWER_TYPE, $type),
			};
		});
		$this->map(Ids::RED_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::RED_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::RED_MUSHROOM, fn() => VanillaBlocks::RED_MUSHROOM());
		$this->map(Ids::RED_MUSHROOM_BLOCK, fn(BlockStateReader $in) => Helper::decodeMushroomBlock(VanillaBlocks::RED_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::RED_NETHER_BRICK, fn() => VanillaBlocks::RED_NETHER_BRICKS());
		$this->map(Ids::RED_NETHER_BRICK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::RED_NETHER_BRICK_STAIRS(), $in));
		$this->map(Ids::RED_SANDSTONE, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::SAND_STONE_TYPE)){
				Values::SAND_STONE_TYPE_CUT => VanillaBlocks::CUT_RED_SANDSTONE(),
				Values::SAND_STONE_TYPE_DEFAULT => VanillaBlocks::RED_SANDSTONE(),
				Values::SAND_STONE_TYPE_HEIROGLYPHS => VanillaBlocks::CHISELED_RED_SANDSTONE(),
				Values::SAND_STONE_TYPE_SMOOTH => VanillaBlocks::SMOOTH_RED_SANDSTONE(),
				default => throw $in->badValueException(BlockStateNames::SAND_STONE_TYPE, $type),
			};
		});
		$this->map(Ids::RED_SANDSTONE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::RED_SANDSTONE_STAIRS(), $in));
		$this->map(Ids::REDSTONE_BLOCK, fn() => VanillaBlocks::REDSTONE());
		$this->map(Ids::REDSTONE_LAMP, function() : Block{
			return VanillaBlocks::REDSTONE_LAMP()
				->setPowered(false);
		});
		$this->map(Ids::REDSTONE_ORE, function() : Block{
			return VanillaBlocks::REDSTONE_ORE()
				->setLit(false);
		});
		$this->map(Ids::REDSTONE_TORCH, function(BlockStateReader $in) : Block{
			return VanillaBlocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(true);
		});
		$this->map(Ids::REDSTONE_WIRE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::REDSTONE_WIRE()
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::REEDS, function(BlockStateReader $in) : Block{
			return VanillaBlocks::SUGARCANE()
				->setAge($in->readBoundedInt(BlockStateNames::AGE, 0, 15));
		});
		$this->map(Ids::RESERVED6, fn() => VanillaBlocks::RESERVED6());
		$this->map(Ids::SAND, function(BlockStateReader $in) : Block{
			return match($value = $in->readString(BlockStateNames::SAND_TYPE)){
				Values::SAND_TYPE_NORMAL => VanillaBlocks::SAND(),
				Values::SAND_TYPE_RED => VanillaBlocks::RED_SAND(),
				default => throw $in->badValueException(BlockStateNames::SAND_TYPE, $value),
			};
		});
		$this->map(Ids::SANDSTONE, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::SAND_STONE_TYPE)){
				Values::SAND_STONE_TYPE_CUT => VanillaBlocks::CUT_SANDSTONE(),
				Values::SAND_STONE_TYPE_DEFAULT => VanillaBlocks::SANDSTONE(),
				Values::SAND_STONE_TYPE_HEIROGLYPHS => VanillaBlocks::CHISELED_SANDSTONE(),
				Values::SAND_STONE_TYPE_SMOOTH => VanillaBlocks::SMOOTH_SANDSTONE(),
				default => throw $in->badValueException(BlockStateNames::SAND_STONE_TYPE, $type),
			};
		});
		$this->map(Ids::SANDSTONE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::SANDSTONE_STAIRS(), $in));
		$this->map(Ids::SAPLING, function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNames::SAPLING_TYPE)){
					Values::SAPLING_TYPE_ACACIA => VanillaBlocks::ACACIA_SAPLING(),
					Values::SAPLING_TYPE_BIRCH => VanillaBlocks::BIRCH_SAPLING(),
					Values::SAPLING_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_SAPLING(),
					Values::SAPLING_TYPE_JUNGLE => VanillaBlocks::JUNGLE_SAPLING(),
					Values::SAPLING_TYPE_OAK => VanillaBlocks::OAK_SAPLING(),
					Values::SAPLING_TYPE_SPRUCE => VanillaBlocks::SPRUCE_SAPLING(),
					default => throw $in->badValueException(BlockStateNames::SAPLING_TYPE, $type),
				})
				->setReady($in->readBool(BlockStateNames::AGE_BIT));
		});
		$this->map(Ids::SEALANTERN, fn() => VanillaBlocks::SEA_LANTERN());
		$this->map(Ids::SEA_PICKLE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::SEA_PICKLE()
				->setCount($in->readBoundedInt(BlockStateNames::CLUSTER_COUNT, 0, 3) + 1)
				->setUnderwater(!$in->readBool(BlockStateNames::DEAD_BIT));
		});
		$this->map(Ids::SHULKER_BOX, function(BlockStateReader $in) : Block{
			return VanillaBlocks::DYED_SHULKER_BOX()
				->setColor($in->readColor());
		});
		$this->map(Ids::SILVER_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::LIGHT_GRAY_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::SKULL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::MOB_HEAD()
				->setFacing($in->readFacingWithoutDown())
				->setNoDrops($in->readBool(BlockStateNames::NO_DROP_BIT));
		});
		$this->map(Ids::SLIME, fn() => VanillaBlocks::SLIME());
		$this->map(Ids::SMOKER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::SMOKER()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::SMOOTH_QUARTZ_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::SMOOTH_QUARTZ_STAIRS(), $in));
		$this->map(Ids::SMOOTH_RED_SANDSTONE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::SMOOTH_RED_SANDSTONE_STAIRS(), $in));
		$this->map(Ids::SMOOTH_SANDSTONE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::SMOOTH_SANDSTONE_STAIRS(), $in));
		$this->map(Ids::SMOOTH_STONE, fn() => VanillaBlocks::SMOOTH_STONE());
		$this->map(Ids::SNOW, fn() => VanillaBlocks::SNOW());
		$this->map(Ids::SNOW_LAYER, function(BlockStateReader $in) : Block{
			//TODO: intentionally ignored covered_bit property (appears useless and we don't track it)
			return VanillaBlocks::SNOW_LAYER()->setLayers($in->readBoundedInt(BlockStateNames::HEIGHT, 0, 7) + 1);
		});
		$this->map(Ids::SOUL_SAND, fn() => VanillaBlocks::SOUL_SAND());
		$this->map(Ids::SPONGE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::SPONGE()->setWet(match($type = $in->readString(BlockStateNames::SPONGE_TYPE)){
				Values::SPONGE_TYPE_DRY => false,
				Values::SPONGE_TYPE_WET => true,
				default => throw $in->badValueException(BlockStateNames::SPONGE_TYPE, $type),
			});
		});
		$this->map(Ids::SPRUCE_BUTTON, fn(BlockStateReader $in) => Helper::decodeButton(VanillaBlocks::SPRUCE_BUTTON(), $in));
		$this->map(Ids::SPRUCE_DOOR, fn(BlockStateReader $in) => Helper::decodeDoor(VanillaBlocks::SPRUCE_DOOR(), $in));
		$this->map(Ids::SPRUCE_FENCE_GATE, fn(BlockStateReader $in) => Helper::decodeFenceGate(VanillaBlocks::SPRUCE_FENCE_GATE(), $in));
		$this->map(Ids::SPRUCE_PRESSURE_PLATE, fn(BlockStateReader $in) => Helper::decodeSimplePressurePlate(VanillaBlocks::SPRUCE_PRESSURE_PLATE(), $in));
		$this->map(Ids::SPRUCE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::SPRUCE_STAIRS(), $in));
		$this->map(Ids::SPRUCE_STANDING_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::SPRUCE_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::SPRUCE_TRAPDOOR, fn(BlockStateReader $in) => Helper::decodeTrapdoor(VanillaBlocks::SPRUCE_TRAPDOOR(), $in));
		$this->map(Ids::SPRUCE_WALL_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::SPRUCE_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::STAINED_GLASS, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_GLASS()
				->setColor($in->readColor());
		});
		$this->map(Ids::STAINED_GLASS_PANE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->map(Ids::STAINED_HARDENED_CLAY, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_CLAY()
				->setColor($in->readColor());
		});
		$this->map(Ids::STANDING_BANNER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::BANNER()
				->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::STANDING_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::OAK_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::STONE, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::STONE_TYPE)){
				Values::STONE_TYPE_ANDESITE => VanillaBlocks::ANDESITE(),
				Values::STONE_TYPE_ANDESITE_SMOOTH => VanillaBlocks::POLISHED_ANDESITE(),
				Values::STONE_TYPE_DIORITE => VanillaBlocks::DIORITE(),
				Values::STONE_TYPE_DIORITE_SMOOTH => VanillaBlocks::POLISHED_DIORITE(),
				Values::STONE_TYPE_GRANITE => VanillaBlocks::GRANITE(),
				Values::STONE_TYPE_GRANITE_SMOOTH => VanillaBlocks::POLISHED_GRANITE(),
				Values::STONE_TYPE_STONE => VanillaBlocks::STONE(),
				default => throw $in->badValueException(BlockStateNames::STONE_TYPE, $type),
			};
		});
		$this->map(Ids::STONE_BRICK_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::STONE_BRICK_STAIRS(), $in));
		$this->map(Ids::STONE_BUTTON, fn(BlockStateReader $in) => Helper::decodeButton(VanillaBlocks::STONE_BUTTON(), $in));
		$this->map(Ids::STONE_PRESSURE_PLATE, fn(BlockStateReader $in) => Helper::decodeSimplePressurePlate(VanillaBlocks::STONE_PRESSURE_PLATE(), $in));
		$this->map(Ids::STONE_SLAB, fn(BlockStateReader $in) => Helper::mapStoneSlab1Type($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::STONE_SLAB2, fn(BlockStateReader $in) => Helper::mapStoneSlab2Type($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::STONE_SLAB3, fn(BlockStateReader $in) => Helper::mapStoneSlab3Type($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::STONE_SLAB4, fn(BlockStateReader $in) => Helper::mapStoneSlab4Type($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::STONE_STAIRS, fn(BlockStateReader $in) => Helper::decodeStairs(VanillaBlocks::COBBLESTONE_STAIRS(), $in));
		$this->map(Ids::STONEBRICK, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::STONE_BRICK_TYPE)){
				Values::STONE_BRICK_TYPE_SMOOTH, //TODO: bug in vanilla
				Values::STONE_BRICK_TYPE_DEFAULT => VanillaBlocks::STONE_BRICKS(),
				Values::STONE_BRICK_TYPE_CHISELED => VanillaBlocks::CHISELED_STONE_BRICKS(),
				Values::STONE_BRICK_TYPE_CRACKED => VanillaBlocks::CRACKED_STONE_BRICKS(),
				Values::STONE_BRICK_TYPE_MOSSY => VanillaBlocks::MOSSY_STONE_BRICKS(),
				default => throw $in->badValueException(BlockStateNames::STONE_BRICK_TYPE, $type),
			};
		});
		$this->map(Ids::STONECUTTER, fn() => VanillaBlocks::LEGACY_STONECUTTER());
		$this->map(Ids::STRIPPED_ACACIA_LOG, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_ACACIA_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::STRIPPED_BIRCH_LOG, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_BIRCH_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::STRIPPED_DARK_OAK_LOG, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_DARK_OAK_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::STRIPPED_JUNGLE_LOG, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_JUNGLE_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::STRIPPED_OAK_LOG, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_OAK_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::STRIPPED_SPRUCE_LOG, function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_SPRUCE_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::SWEET_BERRY_BUSH, function(BlockStateReader $in) : Block{
			//berry bush only wants 0-3, but it can be bigger in MCPE due to misuse of GROWTH state which goes up to 7
			$growth = $in->readBoundedInt(BlockStateNames::GROWTH, 0, 7);
			return VanillaBlocks::SWEET_BERRY_BUSH()
				->setAge(min($growth, SweetBerryBush::STAGE_MATURE));
		});
		$this->map(Ids::TALLGRASS, function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNames::TALL_GRASS_TYPE)){
				Values::TALL_GRASS_TYPE_DEFAULT, Values::TALL_GRASS_TYPE_SNOW, Values::TALL_GRASS_TYPE_TALL => VanillaBlocks::TALL_GRASS(),
				Values::TALL_GRASS_TYPE_FERN => VanillaBlocks::FERN(),
				default => throw $in->badValueException(BlockStateNames::TALL_GRASS_TYPE, $type),
			};
		});
		$this->map(Ids::TNT, function(BlockStateReader $in) : Block{
			return VanillaBlocks::TNT()
				->setUnstable($in->readBool(BlockStateNames::EXPLODE_BIT))
				->setWorksUnderwater($in->readBool(BlockStateNames::ALLOW_UNDERWATER_BIT));
		});
		$this->map(Ids::TORCH, function(BlockStateReader $in) : Block{
			return VanillaBlocks::TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::TRAPDOOR, fn(BlockStateReader $in) => Helper::decodeTrapdoor(VanillaBlocks::OAK_TRAPDOOR(), $in));
		$this->map(Ids::TRAPPED_CHEST, function(BlockStateReader $in) : Block{
			return VanillaBlocks::TRAPPED_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::TRIPWIRE, function(BlockStateReader $in) : Block{
			return VanillaBlocks::TRIPWIRE()
				->setConnected($in->readBool(BlockStateNames::ATTACHED_BIT))
				->setDisarmed($in->readBool(BlockStateNames::DISARMED_BIT))
				->setSuspended($in->readBool(BlockStateNames::SUSPENDED_BIT))
				->setTriggered($in->readBool(BlockStateNames::POWERED_BIT));
		});
		$this->map(Ids::TRIPWIRE_HOOK, function(BlockStateReader $in) : Block{
			return VanillaBlocks::TRIPWIRE_HOOK()
				->setConnected($in->readBool(BlockStateNames::ATTACHED_BIT))
				->setFacing($in->readLegacyHorizontalFacing())
				->setPowered($in->readBool(BlockStateNames::POWERED_BIT));
		});
		$this->map(Ids::UNDERWATER_TORCH, function(BlockStateReader $in) : Block{
			return VanillaBlocks::UNDERWATER_TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::UNDYED_SHULKER_BOX, fn() => VanillaBlocks::SHULKER_BOX());
		$this->map(Ids::UNLIT_REDSTONE_TORCH, function(BlockStateReader $in) : Block{
			return VanillaBlocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(false);
		});
		$this->map(Ids::UNPOWERED_COMPARATOR, fn(BlockStateReader $in) => Helper::decodeComparator(VanillaBlocks::REDSTONE_COMPARATOR(), $in));
		$this->map(Ids::UNPOWERED_REPEATER, fn(BlockStateReader $in) => Helper::decodeRepeater(VanillaBlocks::REDSTONE_REPEATER(), $in)
				->setPowered(false));
		$this->map(Ids::VINE, function(BlockStateReader $in) : Block{
			$vineDirectionFlags = $in->readBoundedInt(BlockStateNames::VINE_DIRECTION_BITS, 0, 15);
			return VanillaBlocks::VINES()
				->setFace(Facing::NORTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_NORTH) !== 0)
				->setFace(Facing::SOUTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_SOUTH) !== 0)
				->setFace(Facing::WEST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_WEST) !== 0)
				->setFace(Facing::EAST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_EAST) !== 0);
		});
		$this->map(Ids::WALL_BANNER, function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_BANNER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::WALL_SIGN, function(BlockStateReader $in) : Block{
			return VanillaBlocks::OAK_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::WATER, fn(BlockStateReader $in) => Helper::decodeStillLiquid(VanillaBlocks::WATER(), $in));
		$this->map(Ids::WATERLILY, fn() => VanillaBlocks::LILY_PAD());
		$this->map(Ids::WEB, fn() => VanillaBlocks::COBWEB());
		$this->map(Ids::WHEAT, fn(BlockStateReader $in) => Helper::decodeCrops(VanillaBlocks::WHEAT(), $in));
		$this->map(Ids::WHITE_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::WHITE_GLAZED_TERRACOTTA(), $in));
		$this->map(Ids::WOOD, function(BlockStateReader $in) : Block{
			//TODO: our impl doesn't support axis yet
			$stripped = $in->readBool(BlockStateNames::STRIPPED_BIT);
			return match($woodType = $in->readString(BlockStateNames::WOOD_TYPE)){
				Values::WOOD_TYPE_ACACIA => $stripped ? VanillaBlocks::STRIPPED_ACACIA_WOOD() : VanillaBlocks::ACACIA_WOOD(),
				Values::WOOD_TYPE_BIRCH => $stripped ? VanillaBlocks::STRIPPED_BIRCH_WOOD() : VanillaBlocks::BIRCH_WOOD(),
				Values::WOOD_TYPE_DARK_OAK => $stripped ? VanillaBlocks::STRIPPED_DARK_OAK_WOOD() : VanillaBlocks::DARK_OAK_WOOD(),
				Values::WOOD_TYPE_JUNGLE => $stripped ? VanillaBlocks::STRIPPED_JUNGLE_WOOD() : VanillaBlocks::JUNGLE_WOOD(),
				Values::WOOD_TYPE_OAK => $stripped ? VanillaBlocks::STRIPPED_OAK_WOOD() : VanillaBlocks::OAK_WOOD(),
				Values::WOOD_TYPE_SPRUCE => $stripped ? VanillaBlocks::STRIPPED_SPRUCE_WOOD() : VanillaBlocks::SPRUCE_WOOD(),
				default => throw $in->badValueException(BlockStateNames::WOOD_TYPE, $woodType),
			};
		});
		$this->map(Ids::WOODEN_BUTTON, fn(BlockStateReader $in) => Helper::decodeButton(VanillaBlocks::OAK_BUTTON(), $in));
		$this->map(Ids::WOODEN_DOOR, fn(BlockStateReader $in) => Helper::decodeDoor(VanillaBlocks::OAK_DOOR(), $in));
		$this->map(Ids::WOODEN_PRESSURE_PLATE, fn(BlockStateReader $in) => Helper::decodeSimplePressurePlate(VanillaBlocks::OAK_PRESSURE_PLATE(), $in));
		$this->map(Ids::WOODEN_SLAB, fn(BlockStateReader $in) => Helper::mapWoodenSlabType($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::WOOL, function(BlockStateReader $in) : Block{
			return VanillaBlocks::WOOL()
				->setColor($in->readColor());
		});
		$this->map(Ids::YELLOW_FLOWER, fn() => VanillaBlocks::DANDELION());
		$this->map(Ids::YELLOW_GLAZED_TERRACOTTA, fn(BlockStateReader $in) => Helper::decodeGlazedTerracotta(VanillaBlocks::YELLOW_GLAZED_TERRACOTTA(), $in));
		//$this->map(Ids::ALLOW, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::AMETHYST_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::AMETHYST_CLUSTER, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::ANCIENT_DEBRIS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::AZALEA, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::AZALEA_LEAVES, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::AZALEA_LEAVES_FLOWERED, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BASALT, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::BEE_NEST, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * honey_level (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::BEEHIVE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * honey_level (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::BIG_DRIPLEAF, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * big_dripleaf_head (ByteTag) = 0, 1
			 * big_dripleaf_tilt (StringTag) = full_tilt, none, partial_tilt, unstable
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::BLACK_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLACK_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLACKSTONE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::BLACKSTONE_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLACKSTONE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLACKSTONE_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::BLACKSTONE_WALL, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLUE_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLUE_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BORDER_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BROWN_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BROWN_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BUBBLE_COLUMN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * drag_down (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BUDDING_AMETHYST, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CALCITE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CAMERA, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CAMPFIRE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * extinguished (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CARTOGRAPHY_TABLE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CAULDRON, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * cauldron_liquid (StringTag) = lava, powder_snow, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->map(Ids::CAVE_VINES, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::CAVE_VINES_BODY_WITH_BERRIES, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::CAVE_VINES_HEAD_WITH_BERRIES, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::CHAIN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::CHAIN_COMMAND_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CHISELED_DEEPSLATE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CHISELED_NETHER_BRICKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CHISELED_POLISHED_BLACKSTONE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CHORUS_FLOWER, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * age (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CHORUS_PLANT, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CLIENT_REQUEST_PLACEHOLDER_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_WALL, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::COMMAND_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::COMPOSTER, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * composter_fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8
			 */
		//});
		//$this->map(Ids::CONDUIT, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COPPER_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COPPER_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRACKED_DEEPSLATE_BRICKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRACKED_DEEPSLATE_TILES, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRACKED_NETHER_BRICKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_BUTTON, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CRIMSON_DOOR, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_FENCE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_FENCE_GATE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_FUNGUS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_HYPHAE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::CRIMSON_NYLIUM, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_PLANKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_PRESSURE_PLATE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::CRIMSON_ROOTS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::CRIMSON_STANDING_SIGN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::CRIMSON_STEM, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::CRIMSON_TRAPDOOR, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_WALL_SIGN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CRYING_OBSIDIAN, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::CYAN_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CYAN_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_WALL, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_COAL_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_COPPER_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_DIAMOND_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_EMERALD_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_GOLD_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_IRON_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_LAPIS_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_REDSTONE_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_WALL, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILES, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DENY, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DIRT_WITH_ROOTS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DISPENSER, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DRIPSTONE_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DROPPER, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::END_GATEWAY, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::END_PORTAL, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::EXPOSED_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::EXPOSED_CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::EXPOSED_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::EXPOSED_CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::EXPOSED_DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::FLOWERING_AZALEA, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::GILDED_BLACKSTONE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::GLOW_FRAME, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * item_frame_map_bit (ByteTag) = 0, 1
			 * item_frame_photo_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GLOW_LICHEN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * multi_face_direction_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63
			 */
		//});
		//$this->map(Ids::GRAY_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GRAY_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GREEN_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GREEN_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GRINDSTONE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * attachment (StringTag) = hanging, multiple, side, standing
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::HANGING_ROOTS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::HONEY_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::HONEYCOMB_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::INFESTED_DEEPSLATE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::JIGSAW, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * rotation (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::KELP, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * kelp_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::LARGE_AMETHYST_BUD, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::LAVA_CAULDRON, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * cauldron_liquid (StringTag) = lava, powder_snow, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->map(Ids::LIGHT_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * block_light_level (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::LIGHT_BLUE_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHT_BLUE_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHT_GRAY_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHT_GRAY_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHTNING_ROD, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::LIME_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIME_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIT_DEEPSLATE_REDSTONE_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::LODESTONE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MAGENTA_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MAGENTA_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MEDIUM_AMETHYST_BUD, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::MOSS_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MOSS_CARPET, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MOVINGBLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MYSTERIOUS_FRAME, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MYSTERIOUS_FRAME_SLOT, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::NETHER_GOLD_ORE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::NETHER_SPROUTS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::NETHERITE_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::OBSERVER, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::ORANGE_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::ORANGE_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::OXIDIZED_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::OXIDIZED_CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::OXIDIZED_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::OXIDIZED_CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::OXIDIZED_DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PINK_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PINK_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PISTON, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::PISTONARMCOLLISION, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::POINTED_DRIPSTONE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * dripstone_thickness (StringTag) = base, frustum, merge, middle, tip
			 * hanging (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_BASALT, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_BRICK_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_BRICK_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_BRICK_WALL, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_BRICKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_BUTTON, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::POLISHED_BLACKSTONE_WALL, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_WALL, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POWDER_SNOW, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::PURPLE_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PURPLE_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::QUARTZ_BRICKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::RAW_COPPER_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::RAW_GOLD_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::RAW_IRON_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::RED_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::RED_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::REPEATING_COMMAND_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::RESPAWN_ANCHOR, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * respawn_anchor_charge (IntTag) = 0, 1, 2, 3, 4
			 */
		//});
		//$this->map(Ids::SCAFFOLDING, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * stability (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 * stability_check (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SCULK_CATALYST, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * bloom (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK_SENSOR, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK_SHRIEKER, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * active (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK_VEIN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * multi_face_direction_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63
			 */
		//});
		//$this->map(Ids::SEAGRASS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * sea_grass_type (StringTag) = default, double_bot, double_top
			 */
		//});
		//$this->map(Ids::SHROOMLIGHT, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SMALL_AMETHYST_BUD, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::SMALL_DRIPLEAF_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SMITHING_TABLE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SMOOTH_BASALT, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SOUL_CAMPFIRE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * extinguished (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SOUL_FIRE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::SOUL_LANTERN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * hanging (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SOUL_SOIL, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SOUL_TORCH, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(Ids::SPORE_BLOSSOM, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::STICKYPISTONARMCOLLISION, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::STICKY_PISTON, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::STONECUTTER_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::STRIPPED_CRIMSON_HYPHAE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_CRIMSON_STEM, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_WARPED_HYPHAE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_WARPED_STEM, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRUCTURE_BLOCK, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * structure_block_type (StringTag) = corner, data, export, invalid, load, save
			 */
		//});
		//$this->map(Ids::STRUCTURE_VOID, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * structure_void_type (StringTag) = air, void
			 */
		//});
		//$this->map(Ids::TARGET, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::TINTED_GLASS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::TUFF, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::TURTLE_EGG, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * cracked_state (StringTag) = cracked, max_cracked, no_cracks
			 * turtle_egg_count (StringTag) = four_egg, one_egg, three_egg, two_egg
			 */
		//});
		//$this->map(Ids::TWISTING_VINES, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * twisting_vines_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::UNKNOWN, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_BUTTON, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::WARPED_DOOR, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_DOUBLE_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_FENCE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_FENCE_GATE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_FUNGUS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_HYPHAE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::WARPED_NYLIUM, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_PLANKS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_PRESSURE_PLATE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::WARPED_ROOTS, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WARPED_STANDING_SIGN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::WARPED_STEM, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::WARPED_TRAPDOOR, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_WALL_SIGN, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::WARPED_WART_BLOCK, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_EXPOSED_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_EXPOSED_CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_EXPOSED_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_EXPOSED_CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_WEATHERED_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_WEATHERED_CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_WEATHERED_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_WEATHERED_CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WEATHERED_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WEATHERED_CUT_COPPER, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WEATHERED_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WEATHERED_CUT_COPPER_STAIRS, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WEATHERED_DOUBLE_CUT_COPPER_SLAB, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WEEPING_VINES, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * weeping_vines_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::WHITE_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WHITE_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WITHER_ROSE, function(BlockStateReader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::YELLOW_CANDLE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::YELLOW_CANDLE_CAKE, function(BlockStateReader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
	}

	/** @throws BlockStateDeserializeException */
	public function deserialize(string $id, CompoundTag $blockState) : Block{
		if(!array_key_exists($id, $this->deserializeFuncs)){
			throw new BlockStateDeserializeException("Unknown block ID \"$id\"");
		}
		return $this->deserializeFuncs[$id](new BlockStateReader($blockState));
	}
}
