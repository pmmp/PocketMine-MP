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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\block\Bamboo;
use pocketmine\block\Block;
use pocketmine\block\Light;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\SlabType;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateDeserializer;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateDeserializerHelper as Helper;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use function array_key_exists;
use function min;

final class BlockStateToBlockObjectDeserializer implements BlockStateDeserializer{

	/**
	 * @var \Closure[]
	 * @phpstan-var array<string, \Closure(Reader $in) : Block>
	 */
	private array $deserializeFuncs = [];

	public function __construct(){
		$this->registerDeserializers();
	}

	public function deserialize(BlockStateData $stateData) : int{
		return $this->deserializeBlock($stateData)->getStateId();
	}

	/** @phpstan-param \Closure(Reader) : Block $c */
	public function map(string $id, \Closure $c) : void{
		if(array_key_exists($id, $this->deserializeFuncs)){
			throw new \InvalidArgumentException("Deserializer is already assigned for \"$id\"");
		}
		$this->deserializeFuncs[$id] = $c;
	}

	/**
	 * @phpstan-param \Closure() : Slab $getBlock
	 */
	public function mapSlab(string $singleId, string $doubleId, \Closure $getBlock) : void{
		$this->map($singleId, fn(Reader $in) : Slab => $getBlock()->setSlabType($in->readSlabPosition()));
		$this->map($doubleId, function(Reader $in) use ($getBlock) : Slab{
			$in->ignored(StateNames::TOP_SLOT_BIT);
			return $getBlock()->setSlabType(SlabType::DOUBLE());
		});
	}

	/**
	 * @phpstan-param \Closure() : Stair $getBlock
	 */
	public function mapStairs(string $id, \Closure $getBlock) : void{
		$this->map($id, fn(Reader $in) : Stair => Helper::decodeStairs($getBlock(), $in));
	}

	private function registerDeserializers() : void{
		$this->map(Ids::ACACIA_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::ACACIA_BUTTON(), $in));
		$this->map(Ids::ACACIA_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::ACACIA_DOOR(), $in));
		$this->map(Ids::ACACIA_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::ACACIA_FENCE_GATE(), $in));
		$this->map(Ids::ACACIA_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::ACACIA_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::ACACIA_STAIRS, fn() => Blocks::ACACIA_STAIRS());
		$this->map(Ids::ACACIA_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::ACACIA_SIGN(), $in));
		$this->map(Ids::ACACIA_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::ACACIA_TRAPDOOR(), $in));
		$this->map(Ids::ACACIA_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::ACACIA_WALL_SIGN(), $in));
		$this->map(Ids::ACTIVATOR_RAIL, function(Reader $in) : Block{
			return Blocks::ACTIVATOR_RAIL()
				->setPowered($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->map(Ids::AIR, fn() => Blocks::AIR());
		$this->map(Ids::AMETHYST_BLOCK, fn() => Blocks::AMETHYST());
		$this->map(Ids::ANCIENT_DEBRIS, fn() => Blocks::ANCIENT_DEBRIS());
		$this->mapStairs(Ids::ANDESITE_STAIRS, fn() => Blocks::ANDESITE_STAIRS());
		$this->map(Ids::ANVIL, function(Reader $in) : Block{
			return Blocks::ANVIL()
				->setDamage(match($value = $in->readString(StateNames::DAMAGE)){
					StringValues::DAMAGE_UNDAMAGED => 0,
					StringValues::DAMAGE_SLIGHTLY_DAMAGED => 1,
					StringValues::DAMAGE_VERY_DAMAGED => 2,
					StringValues::DAMAGE_BROKEN => 0,
					default => throw $in->badValueException(StateNames::DAMAGE, $value),
				})
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::BAMBOO, function(Reader $in) : Block{
			return Blocks::BAMBOO()
				->setLeafSize(match($value = $in->readString(StateNames::BAMBOO_LEAF_SIZE)){
					StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES => Bamboo::NO_LEAVES,
					StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES => Bamboo::SMALL_LEAVES,
					StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES => Bamboo::LARGE_LEAVES,
					default => throw $in->badValueException(StateNames::BAMBOO_LEAF_SIZE, $value),
				})
				->setReady($in->readBool(StateNames::AGE_BIT))
				->setThick(match($value = $in->readString(StateNames::BAMBOO_STALK_THICKNESS)){
					StringValues::BAMBOO_STALK_THICKNESS_THIN => false,
					StringValues::BAMBOO_STALK_THICKNESS_THICK => true,
					default => throw $in->badValueException(StateNames::BAMBOO_STALK_THICKNESS, $value),
				});
		});
		$this->map(Ids::BAMBOO_SAPLING, function(Reader $in) : Block{
			$in->ignored(StateNames::SAPLING_TYPE); //bug in MCPE
			return Blocks::BAMBOO_SAPLING()->setReady($in->readBool(StateNames::AGE_BIT));
		});
		$this->map(Ids::BARREL, function(Reader $in) : Block{
			return Blocks::BARREL()
				->setFacing($in->readFacingDirection())
				->setOpen($in->readBool(StateNames::OPEN_BIT));
		});
		$this->map(Ids::BARRIER, fn() => Blocks::BARRIER());
		$this->map(Ids::BASALT, function(Reader $in){
			return Blocks::BASALT()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::BEACON, fn() => Blocks::BEACON());
		$this->map(Ids::BED, function(Reader $in) : Block{
			return Blocks::BED()
				->setFacing($in->readLegacyHorizontalFacing())
				->setHead($in->readBool(StateNames::HEAD_PIECE_BIT))
				->setOccupied($in->readBool(StateNames::OCCUPIED_BIT));
		});
		$this->map(Ids::BEDROCK, function(Reader $in) : Block{
			return Blocks::BEDROCK()
				->setBurnsForever($in->readBool(StateNames::INFINIBURN_BIT));
		});
		$this->map(Ids::BEETROOT, fn(Reader $in) => Helper::decodeCrops(Blocks::BEETROOTS(), $in));
		$this->map(Ids::BELL, function(Reader $in) : Block{
			$in->ignored(StateNames::TOGGLE_BIT); //only useful at runtime
			return Blocks::BELL()
				->setFacing($in->readLegacyHorizontalFacing())
				->setAttachmentType($in->readBellAttachmentType());
		});
		$this->map(Ids::BIRCH_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::BIRCH_BUTTON(), $in));
		$this->map(Ids::BIRCH_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::BIRCH_DOOR(), $in));
		$this->map(Ids::BIRCH_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::BIRCH_FENCE_GATE(), $in));
		$this->map(Ids::BIRCH_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::BIRCH_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::BIRCH_STAIRS, fn() => Blocks::BIRCH_STAIRS());
		$this->map(Ids::BIRCH_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::BIRCH_SIGN(), $in));
		$this->map(Ids::BIRCH_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::BIRCH_TRAPDOOR(), $in));
		$this->map(Ids::BIRCH_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::BIRCH_WALL_SIGN(), $in));
		$this->map(Ids::BLACKSTONE, fn() => Blocks::BLACKSTONE());
		$this->mapSlab(Ids::BLACKSTONE_SLAB, Ids::BLACKSTONE_DOUBLE_SLAB, fn() => Blocks::BLACKSTONE_SLAB());
		$this->mapStairs(Ids::BLACKSTONE_STAIRS, fn() => Blocks::BLACKSTONE_STAIRS());
		$this->map(Ids::BLACKSTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::BLACKSTONE_WALL(), $in));
		$this->map(Ids::BLACK_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::BLACK(), $in));
		$this->map(Ids::BLAST_FURNACE, function(Reader $in) : Block{
			return Blocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::BLUE_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::BLUE(), $in));
		$this->map(Ids::BLUE_ICE, fn() => Blocks::BLUE_ICE());
		$this->map(Ids::BONE_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::DEPRECATED);
			return Blocks::BONE_BLOCK()->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::BOOKSHELF, fn() => Blocks::BOOKSHELF());
		$this->map(Ids::BREWING_STAND, function(Reader $in) : Block{
			return Blocks::BREWING_STAND()
				->setSlot(BrewingStandSlot::EAST(), $in->readBool(StateNames::BREWING_STAND_SLOT_A_BIT))
				->setSlot(BrewingStandSlot::SOUTHWEST(), $in->readBool(StateNames::BREWING_STAND_SLOT_B_BIT))
				->setSlot(BrewingStandSlot::NORTHWEST(), $in->readBool(StateNames::BREWING_STAND_SLOT_C_BIT));
		});
		$this->map(Ids::BRICK_BLOCK, fn() => Blocks::BRICKS());
		$this->mapStairs(Ids::BRICK_STAIRS, fn() => Blocks::BRICK_STAIRS());
		$this->map(Ids::BROWN_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::BROWN(), $in));
		$this->map(Ids::BROWN_MUSHROOM, fn() => Blocks::BROWN_MUSHROOM());
		$this->map(Ids::BROWN_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::BROWN_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::CACTUS, function(Reader $in) : Block{
			return Blocks::CACTUS()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::CAKE, function(Reader $in) : Block{
			return Blocks::CAKE()
				->setBites($in->readBoundedInt(StateNames::BITE_COUNTER, 0, 6));
		});
		$this->map(Ids::CALCITE, fn() => Blocks::CALCITE());
		$this->map(Ids::CARPET, function(Reader $in) : Block{
			return Blocks::CARPET()
				->setColor($in->readColor());
		});
		$this->map(Ids::CARROTS, fn(Reader $in) => Helper::decodeCrops(Blocks::CARROTS(), $in));
		$this->map(Ids::CARVED_PUMPKIN, function(Reader $in) : Block{
			return Blocks::CARVED_PUMPKIN()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::CHEMICAL_HEAT, fn() => Blocks::CHEMICAL_HEAT());
		$this->map(Ids::CHEMISTRY_TABLE, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::CHEMISTRY_TABLE_TYPE)){
				StringValues::CHEMISTRY_TABLE_TYPE_COMPOUND_CREATOR => Blocks::COMPOUND_CREATOR(),
				StringValues::CHEMISTRY_TABLE_TYPE_ELEMENT_CONSTRUCTOR => Blocks::ELEMENT_CONSTRUCTOR(),
				StringValues::CHEMISTRY_TABLE_TYPE_LAB_TABLE => Blocks::LAB_TABLE(),
				StringValues::CHEMISTRY_TABLE_TYPE_MATERIAL_REDUCER => Blocks::MATERIAL_REDUCER(),
				default => throw $in->badValueException(StateNames::CHEMISTRY_TABLE_TYPE, $type),
			})->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()));
		});
		$this->map(Ids::CHEST, function(Reader $in) : Block{
			return Blocks::CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::CHISELED_DEEPSLATE, fn() => Blocks::CHISELED_DEEPSLATE());
		$this->map(Ids::CHISELED_NETHER_BRICKS, fn() => Blocks::CHISELED_NETHER_BRICKS());
		$this->map(Ids::CHISELED_POLISHED_BLACKSTONE, fn() => Blocks::CHISELED_POLISHED_BLACKSTONE());
		$this->map(Ids::CLAY, fn() => Blocks::CLAY());
		$this->map(Ids::COAL_BLOCK, fn() => Blocks::COAL());
		$this->map(Ids::COAL_ORE, fn() => Blocks::COAL_ORE());
		$this->map(Ids::COBBLED_DEEPSLATE, fn() => Blocks::COBBLED_DEEPSLATE());
		$this->mapSlab(Ids::COBBLED_DEEPSLATE_SLAB, Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB, fn() => Blocks::COBBLED_DEEPSLATE_SLAB());
		$this->mapStairs(Ids::COBBLED_DEEPSLATE_STAIRS, fn() => Blocks::COBBLED_DEEPSLATE_STAIRS());
		$this->map(Ids::COBBLED_DEEPSLATE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::COBBLED_DEEPSLATE_WALL(), $in));
		$this->map(Ids::COBBLESTONE, fn() => Blocks::COBBLESTONE());
		$this->map(Ids::COBBLESTONE_WALL, fn(Reader $in) => Helper::mapLegacyWallType($in));
		$this->map(Ids::COCOA, function(Reader $in) : Block{
			return Blocks::COCOA_POD()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 2))
				->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()));
		});
		$this->map(Ids::COLORED_TORCH_BP, function(Reader $in) : Block{
			return $in->readBool(StateNames::COLOR_BIT) ?
				Blocks::PURPLE_TORCH()->setFacing($in->readTorchFacing()) :
				Blocks::BLUE_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::COLORED_TORCH_RG, function(Reader $in) : Block{
			return $in->readBool(StateNames::COLOR_BIT) ?
				Blocks::GREEN_TORCH()->setFacing($in->readTorchFacing()) :
				Blocks::RED_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::CONCRETE, function(Reader $in) : Block{
			return Blocks::CONCRETE()
				->setColor($in->readColor());
		});
		$this->map(Ids::CONCRETE_POWDER, function(Reader $in) : Block{
			return Blocks::CONCRETE_POWDER()
				->setColor($in->readColor());
		});
		$this->map(Ids::COPPER_ORE, fn() => Blocks::COPPER_ORE());
		$this->map(Ids::CORAL, function(Reader $in) : Block{
			return Blocks::CORAL()
				->setCoralType($in->readCoralType())
				->setDead($in->readBool(StateNames::DEAD_BIT));
		});
		$this->map(Ids::CORAL_BLOCK, function(Reader $in) : Block{
			return Blocks::CORAL_BLOCK()
				->setCoralType($in->readCoralType())
				->setDead($in->readBool(StateNames::DEAD_BIT));
		});
		$this->map(Ids::CORAL_FAN, fn(Reader $in) => Helper::decodeFloorCoralFan(Blocks::CORAL_FAN(), $in)
				->setDead(false));
		$this->map(Ids::CORAL_FAN_DEAD, fn(Reader $in) => Helper::decodeFloorCoralFan(Blocks::CORAL_FAN(), $in)
				->setDead(true));
		$this->map(Ids::CORAL_FAN_HANG, fn(Reader $in) => Helper::decodeWallCoralFan(Blocks::WALL_CORAL_FAN(), $in)
				->setCoralType($in->readBool(StateNames::CORAL_HANG_TYPE_BIT) ? CoralType::BRAIN() : CoralType::TUBE()));
		$this->map(Ids::CORAL_FAN_HANG2, fn(Reader $in) => Helper::decodeWallCoralFan(Blocks::WALL_CORAL_FAN(), $in)
				->setCoralType($in->readBool(StateNames::CORAL_HANG_TYPE_BIT) ? CoralType::FIRE() : CoralType::BUBBLE()));
		$this->map(Ids::CORAL_FAN_HANG3, function(Reader $in) : Block{
			if($in->readBool(StateNames::CORAL_HANG_TYPE_BIT)){
				throw $in->badValueException(StateNames::CORAL_HANG_TYPE_BIT, "1", "This should always be zero for hang3");
			}
			return Helper::decodeWallCoralFan(Blocks::WALL_CORAL_FAN(), $in)
				->setCoralType(CoralType::HORN());
		});
		$this->map(Ids::CRACKED_DEEPSLATE_BRICKS, fn() => Blocks::CRACKED_DEEPSLATE_BRICKS());
		$this->map(Ids::CRACKED_DEEPSLATE_TILES, fn() => Blocks::CRACKED_DEEPSLATE_TILES());
		$this->map(Ids::CRACKED_NETHER_BRICKS, fn() => Blocks::CRACKED_NETHER_BRICKS());
		$this->map(Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS, fn() => Blocks::CRACKED_POLISHED_BLACKSTONE_BRICKS());
		$this->map(Ids::CRAFTING_TABLE, fn() => Blocks::CRAFTING_TABLE());
		$this->map(Ids::CRIMSON_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::CRIMSON_BUTTON(), $in));
		$this->map(Ids::CRIMSON_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::CRIMSON_DOOR(), $in));
		$this->mapSlab(Ids::CRIMSON_SLAB, Ids::CRIMSON_DOUBLE_SLAB, fn() => Blocks::CRIMSON_SLAB());
		$this->map(Ids::CRIMSON_FENCE, fn() => Blocks::CRIMSON_FENCE());
		$this->map(Ids::CRIMSON_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::CRIMSON_FENCE_GATE(), $in));
		$this->map(Ids::CRIMSON_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_HYPHAE(), false, $in));
		$this->map(Ids::CRIMSON_PLANKS, fn() => Blocks::CRIMSON_PLANKS());
		$this->map(Ids::CRIMSON_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::CRIMSON_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::CRIMSON_STAIRS, fn() => Blocks::CRIMSON_STAIRS());
		$this->map(Ids::CRIMSON_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::CRIMSON_SIGN(), $in));
		$this->map(Ids::CRIMSON_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_STEM(), false, $in));
		$this->map(Ids::CRIMSON_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::CRIMSON_TRAPDOOR(), $in));
		$this->map(Ids::CRIMSON_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::CRIMSON_WALL_SIGN(), $in));
		$this->map(Ids::CYAN_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::CYAN(), $in));
		$this->map(Ids::DARK_OAK_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::DARK_OAK_BUTTON(), $in));
		$this->map(Ids::DARK_OAK_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::DARK_OAK_DOOR(), $in));
		$this->map(Ids::DARK_OAK_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::DARK_OAK_FENCE_GATE(), $in));
		$this->map(Ids::DARK_OAK_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::DARK_OAK_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::DARK_OAK_STAIRS, fn() => Blocks::DARK_OAK_STAIRS());
		$this->map(Ids::DARK_OAK_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::DARK_OAK_TRAPDOOR(), $in));
		$this->mapStairs(Ids::DARK_PRISMARINE_STAIRS, fn() => Blocks::DARK_PRISMARINE_STAIRS());
		$this->map(Ids::DARKOAK_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::DARK_OAK_SIGN(), $in));
		$this->map(Ids::DARKOAK_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::DARK_OAK_WALL_SIGN(), $in));
		$this->map(Ids::DAYLIGHT_DETECTOR, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(false));
		$this->map(Ids::DAYLIGHT_DETECTOR_INVERTED, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(true));
		$this->map(Ids::DEADBUSH, fn() => Blocks::DEAD_BUSH());
		$this->map(Ids::DEEPSLATE, function(Reader $in) : Block{
			return Blocks::DEEPSLATE()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::DEEPSLATE_BRICKS, fn() => Blocks::DEEPSLATE_BRICKS());
		$this->mapSlab(Ids::DEEPSLATE_BRICK_SLAB, Ids::DEEPSLATE_BRICK_DOUBLE_SLAB, fn() => Blocks::DEEPSLATE_BRICK_SLAB());
		$this->mapStairs(Ids::DEEPSLATE_BRICK_STAIRS, fn() => Blocks::DEEPSLATE_BRICK_STAIRS());
		$this->map(Ids::DEEPSLATE_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::DEEPSLATE_BRICK_WALL(), $in));
		$this->map(Ids::DEEPSLATE_COAL_ORE, fn() => Blocks::DEEPSLATE_COAL_ORE());
		$this->map(Ids::DEEPSLATE_COPPER_ORE, fn() => Blocks::DEEPSLATE_COPPER_ORE());
		$this->map(Ids::DEEPSLATE_DIAMOND_ORE, fn() => Blocks::DEEPSLATE_DIAMOND_ORE());
		$this->map(Ids::DEEPSLATE_EMERALD_ORE, fn() => Blocks::DEEPSLATE_EMERALD_ORE());
		$this->map(Ids::DEEPSLATE_GOLD_ORE, fn() => Blocks::DEEPSLATE_GOLD_ORE());
		$this->map(Ids::DEEPSLATE_IRON_ORE, fn() => Blocks::DEEPSLATE_IRON_ORE());
		$this->map(Ids::DEEPSLATE_LAPIS_ORE, fn() => Blocks::DEEPSLATE_LAPIS_LAZULI_ORE());
		$this->map(Ids::DEEPSLATE_REDSTONE_ORE, fn() => Blocks::DEEPSLATE_REDSTONE_ORE()->setLit(false));
		$this->map(Ids::DEEPSLATE_TILES, fn() => Blocks::DEEPSLATE_TILES());
		$this->mapSlab(Ids::DEEPSLATE_TILE_SLAB, Ids::DEEPSLATE_TILE_DOUBLE_SLAB, fn() => Blocks::DEEPSLATE_TILE_SLAB());
		$this->mapStairs(Ids::DEEPSLATE_TILE_STAIRS, fn() => Blocks::DEEPSLATE_TILE_STAIRS());
		$this->map(Ids::DEEPSLATE_TILE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::DEEPSLATE_TILE_WALL(), $in));
		$this->map(Ids::DETECTOR_RAIL, function(Reader $in) : Block{
			return Blocks::DETECTOR_RAIL()
				->setActivated($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->map(Ids::DIAMOND_BLOCK, fn() => Blocks::DIAMOND());
		$this->map(Ids::DIAMOND_ORE, fn() => Blocks::DIAMOND_ORE());
		$this->mapStairs(Ids::DIORITE_STAIRS, fn() => Blocks::DIORITE_STAIRS());
		$this->map(Ids::DIRT, function(Reader $in) : Block{
			return Blocks::DIRT()
				->setCoarse(match($value = $in->readString(StateNames::DIRT_TYPE)){
					StringValues::DIRT_TYPE_NORMAL => false,
					StringValues::DIRT_TYPE_COARSE => true,
					default => throw $in->badValueException(StateNames::DIRT_TYPE, $value),
				});
		});
		$this->map(Ids::DOUBLE_PLANT, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::DOUBLE_PLANT_TYPE)){
				StringValues::DOUBLE_PLANT_TYPE_FERN => Blocks::LARGE_FERN(),
				StringValues::DOUBLE_PLANT_TYPE_GRASS => Blocks::DOUBLE_TALLGRASS(),
				StringValues::DOUBLE_PLANT_TYPE_PAEONIA => Blocks::PEONY(),
				StringValues::DOUBLE_PLANT_TYPE_ROSE => Blocks::ROSE_BUSH(),
				StringValues::DOUBLE_PLANT_TYPE_SUNFLOWER => Blocks::SUNFLOWER(),
				StringValues::DOUBLE_PLANT_TYPE_SYRINGA => Blocks::LILAC(),
				default => throw $in->badValueException(StateNames::DOUBLE_PLANT_TYPE, $type),
			})->setTop($in->readBool(StateNames::UPPER_BLOCK_BIT));
		});
		$this->map(Ids::DOUBLE_STONE_BLOCK_SLAB, function(Reader $in) : Block{
			$in->ignored(StateNames::TOP_SLOT_BIT); //useless for double slabs
			return Helper::mapStoneSlab1Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_STONE_BLOCK_SLAB2, function(Reader $in) : Block{
			$in->ignored(StateNames::TOP_SLOT_BIT); //useless for double slabs
			return Helper::mapStoneSlab2Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_STONE_BLOCK_SLAB3, function(Reader $in) : Block{
			$in->ignored(StateNames::TOP_SLOT_BIT); //useless for double slabs
			return Helper::mapStoneSlab3Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_STONE_BLOCK_SLAB4, function(Reader $in) : Block{
			$in->ignored(StateNames::TOP_SLOT_BIT); //useless for double slabs
			return Helper::mapStoneSlab4Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DOUBLE_WOODEN_SLAB, function(Reader $in) : Block{
			$in->ignored(StateNames::TOP_SLOT_BIT); //useless for double slabs
			return Helper::mapWoodenSlabType($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->map(Ids::DRAGON_EGG, fn() => Blocks::DRAGON_EGG());
		$this->map(Ids::DRIED_KELP_BLOCK, fn() => Blocks::DRIED_KELP());
		$this->map(Ids::ELEMENT_0, fn() => Blocks::ELEMENT_ZERO());
		$this->map(Ids::ELEMENT_1, fn() => Blocks::ELEMENT_HYDROGEN());
		$this->map(Ids::ELEMENT_10, fn() => Blocks::ELEMENT_NEON());
		$this->map(Ids::ELEMENT_100, fn() => Blocks::ELEMENT_FERMIUM());
		$this->map(Ids::ELEMENT_101, fn() => Blocks::ELEMENT_MENDELEVIUM());
		$this->map(Ids::ELEMENT_102, fn() => Blocks::ELEMENT_NOBELIUM());
		$this->map(Ids::ELEMENT_103, fn() => Blocks::ELEMENT_LAWRENCIUM());
		$this->map(Ids::ELEMENT_104, fn() => Blocks::ELEMENT_RUTHERFORDIUM());
		$this->map(Ids::ELEMENT_105, fn() => Blocks::ELEMENT_DUBNIUM());
		$this->map(Ids::ELEMENT_106, fn() => Blocks::ELEMENT_SEABORGIUM());
		$this->map(Ids::ELEMENT_107, fn() => Blocks::ELEMENT_BOHRIUM());
		$this->map(Ids::ELEMENT_108, fn() => Blocks::ELEMENT_HASSIUM());
		$this->map(Ids::ELEMENT_109, fn() => Blocks::ELEMENT_MEITNERIUM());
		$this->map(Ids::ELEMENT_11, fn() => Blocks::ELEMENT_SODIUM());
		$this->map(Ids::ELEMENT_110, fn() => Blocks::ELEMENT_DARMSTADTIUM());
		$this->map(Ids::ELEMENT_111, fn() => Blocks::ELEMENT_ROENTGENIUM());
		$this->map(Ids::ELEMENT_112, fn() => Blocks::ELEMENT_COPERNICIUM());
		$this->map(Ids::ELEMENT_113, fn() => Blocks::ELEMENT_NIHONIUM());
		$this->map(Ids::ELEMENT_114, fn() => Blocks::ELEMENT_FLEROVIUM());
		$this->map(Ids::ELEMENT_115, fn() => Blocks::ELEMENT_MOSCOVIUM());
		$this->map(Ids::ELEMENT_116, fn() => Blocks::ELEMENT_LIVERMORIUM());
		$this->map(Ids::ELEMENT_117, fn() => Blocks::ELEMENT_TENNESSINE());
		$this->map(Ids::ELEMENT_118, fn() => Blocks::ELEMENT_OGANESSON());
		$this->map(Ids::ELEMENT_12, fn() => Blocks::ELEMENT_MAGNESIUM());
		$this->map(Ids::ELEMENT_13, fn() => Blocks::ELEMENT_ALUMINUM());
		$this->map(Ids::ELEMENT_14, fn() => Blocks::ELEMENT_SILICON());
		$this->map(Ids::ELEMENT_15, fn() => Blocks::ELEMENT_PHOSPHORUS());
		$this->map(Ids::ELEMENT_16, fn() => Blocks::ELEMENT_SULFUR());
		$this->map(Ids::ELEMENT_17, fn() => Blocks::ELEMENT_CHLORINE());
		$this->map(Ids::ELEMENT_18, fn() => Blocks::ELEMENT_ARGON());
		$this->map(Ids::ELEMENT_19, fn() => Blocks::ELEMENT_POTASSIUM());
		$this->map(Ids::ELEMENT_2, fn() => Blocks::ELEMENT_HELIUM());
		$this->map(Ids::ELEMENT_20, fn() => Blocks::ELEMENT_CALCIUM());
		$this->map(Ids::ELEMENT_21, fn() => Blocks::ELEMENT_SCANDIUM());
		$this->map(Ids::ELEMENT_22, fn() => Blocks::ELEMENT_TITANIUM());
		$this->map(Ids::ELEMENT_23, fn() => Blocks::ELEMENT_VANADIUM());
		$this->map(Ids::ELEMENT_24, fn() => Blocks::ELEMENT_CHROMIUM());
		$this->map(Ids::ELEMENT_25, fn() => Blocks::ELEMENT_MANGANESE());
		$this->map(Ids::ELEMENT_26, fn() => Blocks::ELEMENT_IRON());
		$this->map(Ids::ELEMENT_27, fn() => Blocks::ELEMENT_COBALT());
		$this->map(Ids::ELEMENT_28, fn() => Blocks::ELEMENT_NICKEL());
		$this->map(Ids::ELEMENT_29, fn() => Blocks::ELEMENT_COPPER());
		$this->map(Ids::ELEMENT_3, fn() => Blocks::ELEMENT_LITHIUM());
		$this->map(Ids::ELEMENT_30, fn() => Blocks::ELEMENT_ZINC());
		$this->map(Ids::ELEMENT_31, fn() => Blocks::ELEMENT_GALLIUM());
		$this->map(Ids::ELEMENT_32, fn() => Blocks::ELEMENT_GERMANIUM());
		$this->map(Ids::ELEMENT_33, fn() => Blocks::ELEMENT_ARSENIC());
		$this->map(Ids::ELEMENT_34, fn() => Blocks::ELEMENT_SELENIUM());
		$this->map(Ids::ELEMENT_35, fn() => Blocks::ELEMENT_BROMINE());
		$this->map(Ids::ELEMENT_36, fn() => Blocks::ELEMENT_KRYPTON());
		$this->map(Ids::ELEMENT_37, fn() => Blocks::ELEMENT_RUBIDIUM());
		$this->map(Ids::ELEMENT_38, fn() => Blocks::ELEMENT_STRONTIUM());
		$this->map(Ids::ELEMENT_39, fn() => Blocks::ELEMENT_YTTRIUM());
		$this->map(Ids::ELEMENT_4, fn() => Blocks::ELEMENT_BERYLLIUM());
		$this->map(Ids::ELEMENT_40, fn() => Blocks::ELEMENT_ZIRCONIUM());
		$this->map(Ids::ELEMENT_41, fn() => Blocks::ELEMENT_NIOBIUM());
		$this->map(Ids::ELEMENT_42, fn() => Blocks::ELEMENT_MOLYBDENUM());
		$this->map(Ids::ELEMENT_43, fn() => Blocks::ELEMENT_TECHNETIUM());
		$this->map(Ids::ELEMENT_44, fn() => Blocks::ELEMENT_RUTHENIUM());
		$this->map(Ids::ELEMENT_45, fn() => Blocks::ELEMENT_RHODIUM());
		$this->map(Ids::ELEMENT_46, fn() => Blocks::ELEMENT_PALLADIUM());
		$this->map(Ids::ELEMENT_47, fn() => Blocks::ELEMENT_SILVER());
		$this->map(Ids::ELEMENT_48, fn() => Blocks::ELEMENT_CADMIUM());
		$this->map(Ids::ELEMENT_49, fn() => Blocks::ELEMENT_INDIUM());
		$this->map(Ids::ELEMENT_5, fn() => Blocks::ELEMENT_BORON());
		$this->map(Ids::ELEMENT_50, fn() => Blocks::ELEMENT_TIN());
		$this->map(Ids::ELEMENT_51, fn() => Blocks::ELEMENT_ANTIMONY());
		$this->map(Ids::ELEMENT_52, fn() => Blocks::ELEMENT_TELLURIUM());
		$this->map(Ids::ELEMENT_53, fn() => Blocks::ELEMENT_IODINE());
		$this->map(Ids::ELEMENT_54, fn() => Blocks::ELEMENT_XENON());
		$this->map(Ids::ELEMENT_55, fn() => Blocks::ELEMENT_CESIUM());
		$this->map(Ids::ELEMENT_56, fn() => Blocks::ELEMENT_BARIUM());
		$this->map(Ids::ELEMENT_57, fn() => Blocks::ELEMENT_LANTHANUM());
		$this->map(Ids::ELEMENT_58, fn() => Blocks::ELEMENT_CERIUM());
		$this->map(Ids::ELEMENT_59, fn() => Blocks::ELEMENT_PRASEODYMIUM());
		$this->map(Ids::ELEMENT_6, fn() => Blocks::ELEMENT_CARBON());
		$this->map(Ids::ELEMENT_60, fn() => Blocks::ELEMENT_NEODYMIUM());
		$this->map(Ids::ELEMENT_61, fn() => Blocks::ELEMENT_PROMETHIUM());
		$this->map(Ids::ELEMENT_62, fn() => Blocks::ELEMENT_SAMARIUM());
		$this->map(Ids::ELEMENT_63, fn() => Blocks::ELEMENT_EUROPIUM());
		$this->map(Ids::ELEMENT_64, fn() => Blocks::ELEMENT_GADOLINIUM());
		$this->map(Ids::ELEMENT_65, fn() => Blocks::ELEMENT_TERBIUM());
		$this->map(Ids::ELEMENT_66, fn() => Blocks::ELEMENT_DYSPROSIUM());
		$this->map(Ids::ELEMENT_67, fn() => Blocks::ELEMENT_HOLMIUM());
		$this->map(Ids::ELEMENT_68, fn() => Blocks::ELEMENT_ERBIUM());
		$this->map(Ids::ELEMENT_69, fn() => Blocks::ELEMENT_THULIUM());
		$this->map(Ids::ELEMENT_7, fn() => Blocks::ELEMENT_NITROGEN());
		$this->map(Ids::ELEMENT_70, fn() => Blocks::ELEMENT_YTTERBIUM());
		$this->map(Ids::ELEMENT_71, fn() => Blocks::ELEMENT_LUTETIUM());
		$this->map(Ids::ELEMENT_72, fn() => Blocks::ELEMENT_HAFNIUM());
		$this->map(Ids::ELEMENT_73, fn() => Blocks::ELEMENT_TANTALUM());
		$this->map(Ids::ELEMENT_74, fn() => Blocks::ELEMENT_TUNGSTEN());
		$this->map(Ids::ELEMENT_75, fn() => Blocks::ELEMENT_RHENIUM());
		$this->map(Ids::ELEMENT_76, fn() => Blocks::ELEMENT_OSMIUM());
		$this->map(Ids::ELEMENT_77, fn() => Blocks::ELEMENT_IRIDIUM());
		$this->map(Ids::ELEMENT_78, fn() => Blocks::ELEMENT_PLATINUM());
		$this->map(Ids::ELEMENT_79, fn() => Blocks::ELEMENT_GOLD());
		$this->map(Ids::ELEMENT_8, fn() => Blocks::ELEMENT_OXYGEN());
		$this->map(Ids::ELEMENT_80, fn() => Blocks::ELEMENT_MERCURY());
		$this->map(Ids::ELEMENT_81, fn() => Blocks::ELEMENT_THALLIUM());
		$this->map(Ids::ELEMENT_82, fn() => Blocks::ELEMENT_LEAD());
		$this->map(Ids::ELEMENT_83, fn() => Blocks::ELEMENT_BISMUTH());
		$this->map(Ids::ELEMENT_84, fn() => Blocks::ELEMENT_POLONIUM());
		$this->map(Ids::ELEMENT_85, fn() => Blocks::ELEMENT_ASTATINE());
		$this->map(Ids::ELEMENT_86, fn() => Blocks::ELEMENT_RADON());
		$this->map(Ids::ELEMENT_87, fn() => Blocks::ELEMENT_FRANCIUM());
		$this->map(Ids::ELEMENT_88, fn() => Blocks::ELEMENT_RADIUM());
		$this->map(Ids::ELEMENT_89, fn() => Blocks::ELEMENT_ACTINIUM());
		$this->map(Ids::ELEMENT_9, fn() => Blocks::ELEMENT_FLUORINE());
		$this->map(Ids::ELEMENT_90, fn() => Blocks::ELEMENT_THORIUM());
		$this->map(Ids::ELEMENT_91, fn() => Blocks::ELEMENT_PROTACTINIUM());
		$this->map(Ids::ELEMENT_92, fn() => Blocks::ELEMENT_URANIUM());
		$this->map(Ids::ELEMENT_93, fn() => Blocks::ELEMENT_NEPTUNIUM());
		$this->map(Ids::ELEMENT_94, fn() => Blocks::ELEMENT_PLUTONIUM());
		$this->map(Ids::ELEMENT_95, fn() => Blocks::ELEMENT_AMERICIUM());
		$this->map(Ids::ELEMENT_96, fn() => Blocks::ELEMENT_CURIUM());
		$this->map(Ids::ELEMENT_97, fn() => Blocks::ELEMENT_BERKELIUM());
		$this->map(Ids::ELEMENT_98, fn() => Blocks::ELEMENT_CALIFORNIUM());
		$this->map(Ids::ELEMENT_99, fn() => Blocks::ELEMENT_EINSTEINIUM());
		$this->map(Ids::EMERALD_BLOCK, fn() => Blocks::EMERALD());
		$this->map(Ids::EMERALD_ORE, fn() => Blocks::EMERALD_ORE());
		$this->map(Ids::ENCHANTING_TABLE, fn() => Blocks::ENCHANTING_TABLE());
		$this->mapStairs(Ids::END_BRICK_STAIRS, fn() => Blocks::END_STONE_BRICK_STAIRS());
		$this->map(Ids::END_BRICKS, fn() => Blocks::END_STONE_BRICKS());
		$this->map(Ids::END_PORTAL_FRAME, function(Reader $in) : Block{
			return Blocks::END_PORTAL_FRAME()
				->setEye($in->readBool(StateNames::END_PORTAL_EYE_BIT))
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::END_ROD, function(Reader $in) : Block{
			return Blocks::END_ROD()
				->setFacing($in->readEndRodFacingDirection());
		});
		$this->map(Ids::END_STONE, fn() => Blocks::END_STONE());
		$this->map(Ids::ENDER_CHEST, function(Reader $in) : Block{
			return Blocks::ENDER_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::FARMLAND, function(Reader $in) : Block{
			return Blocks::FARMLAND()
				->setWetness($in->readBoundedInt(StateNames::MOISTURIZED_AMOUNT, 0, 7));
		});
		$this->map(Ids::FENCE, function(Reader $in) : Block{
			return match($woodName = $in->readString(StateNames::WOOD_TYPE)){
				StringValues::WOOD_TYPE_OAK => Blocks::OAK_FENCE(),
				StringValues::WOOD_TYPE_SPRUCE => Blocks::SPRUCE_FENCE(),
				StringValues::WOOD_TYPE_BIRCH => Blocks::BIRCH_FENCE(),
				StringValues::WOOD_TYPE_JUNGLE => Blocks::JUNGLE_FENCE(),
				StringValues::WOOD_TYPE_ACACIA => Blocks::ACACIA_FENCE(),
				StringValues::WOOD_TYPE_DARK_OAK => Blocks::DARK_OAK_FENCE(),
				default => throw $in->badValueException(StateNames::WOOD_TYPE, $woodName),
			};
		});
		$this->map(Ids::FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::OAK_FENCE_GATE(), $in));
		$this->map(Ids::FIRE, function(Reader $in) : Block{
			return Blocks::FIRE()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::FLETCHING_TABLE, fn() => Blocks::FLETCHING_TABLE());
		$this->map(Ids::FLOWER_POT, function(Reader $in) : Block{
			$in->ignored(StateNames::UPDATE_BIT);
			return Blocks::FLOWER_POT();
		});
		$this->map(Ids::FLOWING_LAVA, fn(Reader $in) => Helper::decodeFlowingLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::FLOWING_WATER, fn(Reader $in) => Helper::decodeFlowingLiquid(Blocks::WATER(), $in));
		$this->map(Ids::FRAME, function(Reader $in) : Block{
			$in->todo(StateNames::ITEM_FRAME_PHOTO_BIT); //TODO: not sure what the point of this is
			return Blocks::ITEM_FRAME()
				->setFacing($in->readFacingDirection())
				->setHasMap($in->readBool(StateNames::ITEM_FRAME_MAP_BIT));
		});
		$this->map(Ids::FROSTED_ICE, function(Reader $in) : Block{
			return Blocks::FROSTED_ICE()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 3));
		});
		$this->map(Ids::FURNACE, function(Reader $in) : Block{
			return Blocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::GLASS, fn() => Blocks::GLASS());
		$this->map(Ids::GLASS_PANE, fn() => Blocks::GLASS_PANE());
		$this->map(Ids::GLOWINGOBSIDIAN, fn() => Blocks::GLOWING_OBSIDIAN());
		$this->map(Ids::GLOWSTONE, fn() => Blocks::GLOWSTONE());
		$this->map(Ids::GOLD_BLOCK, fn() => Blocks::GOLD());
		$this->map(Ids::GOLD_ORE, fn() => Blocks::GOLD_ORE());
		$this->map(Ids::GOLDEN_RAIL, function(Reader $in) : Block{
			return Blocks::POWERED_RAIL()
				->setPowered($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->mapStairs(Ids::GRANITE_STAIRS, fn() => Blocks::GRANITE_STAIRS());
		$this->map(Ids::GRASS, fn() => Blocks::GRASS());
		$this->map(Ids::GRASS_PATH, fn() => Blocks::GRASS_PATH());
		$this->map(Ids::GRAVEL, fn() => Blocks::GRAVEL());
		$this->map(Ids::GRAY_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::GRAY(), $in));
		$this->map(Ids::GREEN_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::GREEN(), $in));
		$this->map(Ids::HARD_GLASS, fn() => Blocks::HARDENED_GLASS());
		$this->map(Ids::HARD_GLASS_PANE, fn() => Blocks::HARDENED_GLASS_PANE());
		$this->map(Ids::HARD_STAINED_GLASS, function(Reader $in) : Block{
			return Blocks::STAINED_HARDENED_GLASS()
				->setColor($in->readColor());
		});
		$this->map(Ids::HARD_STAINED_GLASS_PANE, function(Reader $in) : Block{
			return Blocks::STAINED_HARDENED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->map(Ids::HARDENED_CLAY, fn() => Blocks::HARDENED_CLAY());
		$this->map(Ids::HAY_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::DEPRECATED);
			return Blocks::HAY_BALE()->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeWeightedPressurePlate(Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY(), $in));
		$this->map(Ids::HONEYCOMB_BLOCK, fn() => Blocks::HONEYCOMB());
		$this->map(Ids::HOPPER, function(Reader $in) : Block{
			return Blocks::HOPPER()
				->setFacing($in->readFacingWithoutUp())
				->setPowered($in->readBool(StateNames::TOGGLE_BIT));
		});
		$this->map(Ids::ICE, fn() => Blocks::ICE());
		$this->map(Ids::INFO_UPDATE, fn() => Blocks::INFO_UPDATE());
		$this->map(Ids::INFO_UPDATE2, fn() => Blocks::INFO_UPDATE2());
		$this->map(Ids::INVISIBLE_BEDROCK, fn() => Blocks::INVISIBLE_BEDROCK());
		$this->map(Ids::IRON_BARS, fn() => Blocks::IRON_BARS());
		$this->map(Ids::IRON_BLOCK, fn() => Blocks::IRON());
		$this->map(Ids::IRON_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::IRON_DOOR(), $in));
		$this->map(Ids::IRON_ORE, fn() => Blocks::IRON_ORE());
		$this->map(Ids::IRON_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::IRON_TRAPDOOR(), $in));
		$this->map(Ids::JUKEBOX, fn() => Blocks::JUKEBOX());
		$this->map(Ids::JUNGLE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::JUNGLE_BUTTON(), $in));
		$this->map(Ids::JUNGLE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::JUNGLE_DOOR(), $in));
		$this->map(Ids::JUNGLE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::JUNGLE_FENCE_GATE(), $in));
		$this->map(Ids::JUNGLE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::JUNGLE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::JUNGLE_STAIRS, fn() => Blocks::JUNGLE_STAIRS());
		$this->map(Ids::JUNGLE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::JUNGLE_SIGN(), $in));
		$this->map(Ids::JUNGLE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::JUNGLE_TRAPDOOR(), $in));
		$this->map(Ids::JUNGLE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::JUNGLE_WALL_SIGN(), $in));
		$this->map(Ids::LADDER, function(Reader $in) : Block{
			return Blocks::LADDER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::LANTERN, function(Reader $in) : Block{
			return Blocks::LANTERN()
				->setHanging($in->readBool(StateNames::HANGING));
		});
		$this->map(Ids::LAPIS_BLOCK, fn() => Blocks::LAPIS_LAZULI());
		$this->map(Ids::LAPIS_ORE, fn() => Blocks::LAPIS_LAZULI_ORE());
		$this->map(Ids::LAVA, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::LEAVES, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::OLD_LEAF_TYPE)){
					StringValues::OLD_LEAF_TYPE_BIRCH => Blocks::BIRCH_LEAVES(),
					StringValues::OLD_LEAF_TYPE_JUNGLE => Blocks::JUNGLE_LEAVES(),
					StringValues::OLD_LEAF_TYPE_OAK => Blocks::OAK_LEAVES(),
					StringValues::OLD_LEAF_TYPE_SPRUCE => Blocks::SPRUCE_LEAVES(),
					default => throw $in->badValueException(StateNames::OLD_LEAF_TYPE, $type),
				})
				->setNoDecay($in->readBool(StateNames::PERSISTENT_BIT))
				->setCheckDecay($in->readBool(StateNames::UPDATE_BIT));
		});
		$this->map(Ids::LEAVES2, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::NEW_LEAF_TYPE)){
					StringValues::NEW_LEAF_TYPE_ACACIA => Blocks::ACACIA_LEAVES(),
					StringValues::NEW_LEAF_TYPE_DARK_OAK => Blocks::DARK_OAK_LEAVES(),
					default => throw $in->badValueException(StateNames::NEW_LEAF_TYPE, $type),
				})
				->setNoDecay($in->readBool(StateNames::PERSISTENT_BIT))
				->setCheckDecay($in->readBool(StateNames::UPDATE_BIT));
		});
		$this->map(Ids::LECTERN, function(Reader $in) : Block{
			return Blocks::LECTERN()
				->setFacing($in->readLegacyHorizontalFacing())
				->setProducingSignal($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::LEVER, function(Reader $in) : Block{
			return Blocks::LEVER()
				->setActivated($in->readBool(StateNames::OPEN_BIT))
				->setFacing(match($value = $in->readString(StateNames::LEVER_DIRECTION)){
					StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH => LeverFacing::DOWN_AXIS_Z(),
					StringValues::LEVER_DIRECTION_DOWN_EAST_WEST => LeverFacing::DOWN_AXIS_X(),
					StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH => LeverFacing::UP_AXIS_Z(),
					StringValues::LEVER_DIRECTION_UP_EAST_WEST => LeverFacing::UP_AXIS_X(),
					StringValues::LEVER_DIRECTION_NORTH => LeverFacing::NORTH(),
					StringValues::LEVER_DIRECTION_SOUTH => LeverFacing::SOUTH(),
					StringValues::LEVER_DIRECTION_WEST => LeverFacing::WEST(),
					StringValues::LEVER_DIRECTION_EAST => LeverFacing::EAST(),
					default => throw $in->badValueException(StateNames::LEVER_DIRECTION, $value),
				});
		});
		$this->map(Ids::LIGHT_BLOCK, function(Reader $in) : Block{
			return Blocks::LIGHT()
				->setLightLevel($in->readBoundedInt(StateNames::BLOCK_LIGHT_LEVEL, Light::MIN_LIGHT_LEVEL, Light::MAX_LIGHT_LEVEL));
		});
		$this->map(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::LIGHT_BLUE(), $in));
		$this->map(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeWeightedPressurePlate(Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), $in));
		$this->map(Ids::LIME_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::LIME(), $in));
		$this->map(Ids::LIT_BLAST_FURNACE, function(Reader $in) : Block{
			return Blocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_DEEPSLATE_REDSTONE_ORE, fn() => Blocks::DEEPSLATE_REDSTONE_ORE()->setLit(true));
		$this->map(Ids::LIT_FURNACE, function(Reader $in) : Block{
			return Blocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_PUMPKIN, function(Reader $in) : Block{
			return Blocks::LIT_PUMPKIN()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::LIT_REDSTONE_LAMP, function() : Block{
			return Blocks::REDSTONE_LAMP()
				->setPowered(true);
		});
		$this->map(Ids::LIT_REDSTONE_ORE, function() : Block{
			return Blocks::REDSTONE_ORE()
				->setLit(true);
		});
		$this->map(Ids::LIT_SMOKER, function(Reader $in) : Block{
			return Blocks::SMOKER()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LOG, fn(Reader $in) => Helper::decodeLog(match($type = $in->readString(StateNames::OLD_LOG_TYPE)){
			StringValues::OLD_LOG_TYPE_BIRCH => Blocks::BIRCH_LOG(),
			StringValues::OLD_LOG_TYPE_JUNGLE => Blocks::JUNGLE_LOG(),
			StringValues::OLD_LOG_TYPE_OAK => Blocks::OAK_LOG(),
			StringValues::OLD_LOG_TYPE_SPRUCE => Blocks::SPRUCE_LOG(),
			default => throw $in->badValueException(StateNames::OLD_LOG_TYPE, $type),
		}, false, $in));
		$this->map(Ids::LOG2, fn(Reader $in) => Helper::decodeLog(match($type = $in->readString(StateNames::NEW_LOG_TYPE)){
			StringValues::NEW_LOG_TYPE_ACACIA => Blocks::ACACIA_LOG(),
			StringValues::NEW_LOG_TYPE_DARK_OAK => Blocks::DARK_OAK_LOG(),
			default => throw $in->badValueException(StateNames::NEW_LOG_TYPE, $type),
		}, false, $in));
		$this->map(Ids::LOOM, function(Reader $in) : Block{
			return Blocks::LOOM()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::MAGENTA_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::MAGENTA(), $in));
		$this->map(Ids::MAGMA, fn() => Blocks::MAGMA());
		$this->map(Ids::MANGROVE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::MANGROVE_BUTTON(), $in));
		$this->map(Ids::MANGROVE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::MANGROVE_DOOR(), $in));
		$this->mapSlab(Ids::MANGROVE_SLAB, Ids::MANGROVE_DOUBLE_SLAB, fn() => Blocks::MANGROVE_SLAB());
		$this->map(Ids::MANGROVE_FENCE, fn() => Blocks::MANGROVE_FENCE());
		$this->map(Ids::MANGROVE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::MANGROVE_FENCE_GATE(), $in));
		$this->map(Ids::MANGROVE_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_LOG(), false, $in));
		$this->map(Ids::MANGROVE_PLANKS, fn() => Blocks::MANGROVE_PLANKS());
		$this->map(Ids::MANGROVE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::MANGROVE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::MANGROVE_STAIRS, fn() => Blocks::MANGROVE_STAIRS());
		$this->map(Ids::MANGROVE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::MANGROVE_SIGN(), $in));
		$this->map(Ids::MANGROVE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::MANGROVE_TRAPDOOR(), $in));
		$this->map(Ids::MANGROVE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::MANGROVE_WALL_SIGN(), $in));
		$this->map(Ids::MANGROVE_WOOD, function(Reader $in){
			$in->ignored(StateNames::STRIPPED_BIT); //this is also ignored by vanilla
			return Helper::decodeLog(Blocks::MANGROVE_WOOD(), false, $in);
		});
		$this->map(Ids::MELON_BLOCK, fn() => Blocks::MELON());
		$this->map(Ids::MELON_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::MELON_STEM(), $in));
		$this->map(Ids::MOB_SPAWNER, fn() => Blocks::MONSTER_SPAWNER());
		$this->map(Ids::MONSTER_EGG, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::MONSTER_EGG_STONE_TYPE)){
				StringValues::MONSTER_EGG_STONE_TYPE_CHISELED_STONE_BRICK => Blocks::INFESTED_CHISELED_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_COBBLESTONE => Blocks::INFESTED_COBBLESTONE(),
				StringValues::MONSTER_EGG_STONE_TYPE_CRACKED_STONE_BRICK => Blocks::INFESTED_CRACKED_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_MOSSY_STONE_BRICK => Blocks::INFESTED_MOSSY_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_STONE => Blocks::INFESTED_STONE(),
				StringValues::MONSTER_EGG_STONE_TYPE_STONE_BRICK => Blocks::INFESTED_STONE_BRICK(),
				default => throw $in->badValueException(StateNames::MONSTER_EGG_STONE_TYPE, $type),
			};
		});
		$this->map(Ids::MOSSY_COBBLESTONE, fn() => Blocks::MOSSY_COBBLESTONE());
		$this->mapStairs(Ids::MOSSY_COBBLESTONE_STAIRS, fn() => Blocks::MOSSY_COBBLESTONE_STAIRS());
		$this->mapStairs(Ids::MOSSY_STONE_BRICK_STAIRS, fn() => Blocks::MOSSY_STONE_BRICK_STAIRS());
		$this->map(Ids::MUD_BRICKS, fn() => Blocks::MUD_BRICKS());
		$this->mapSlab(Ids::MUD_BRICK_SLAB, Ids::MUD_BRICK_DOUBLE_SLAB, fn() => Blocks::MUD_BRICK_SLAB());
		$this->mapStairs(Ids::MUD_BRICK_STAIRS, fn() => Blocks::MUD_BRICK_STAIRS());
		$this->map(Ids::MUD_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::MUD_BRICK_WALL(), $in));
		$this->map(Ids::MYCELIUM, fn() => Blocks::MYCELIUM());
		$this->map(Ids::NETHER_BRICK, fn() => Blocks::NETHER_BRICKS());
		$this->map(Ids::NETHER_BRICK_FENCE, fn() => Blocks::NETHER_BRICK_FENCE());
		$this->mapStairs(Ids::NETHER_BRICK_STAIRS, fn() => Blocks::NETHER_BRICK_STAIRS());
		$this->map(Ids::NETHER_GOLD_ORE, fn() => Blocks::NETHER_GOLD_ORE());
		$this->map(Ids::NETHER_WART, function(Reader $in) : Block{
			return Blocks::NETHER_WART()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 3));
		});
		$this->map(Ids::NETHER_WART_BLOCK, fn() => Blocks::NETHER_WART_BLOCK());
		$this->map(Ids::NETHERRACK, fn() => Blocks::NETHERRACK());
		$this->map(Ids::NETHERREACTOR, fn() => Blocks::NETHER_REACTOR_CORE());
		$this->mapStairs(Ids::NORMAL_STONE_STAIRS, fn() => Blocks::STONE_STAIRS());
		$this->map(Ids::NOTEBLOCK, fn() => Blocks::NOTE_BLOCK());
		$this->mapStairs(Ids::OAK_STAIRS, fn() => Blocks::OAK_STAIRS());
		$this->map(Ids::OBSIDIAN, fn() => Blocks::OBSIDIAN());
		$this->map(Ids::ORANGE_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::ORANGE(), $in));
		$this->map(Ids::PACKED_ICE, fn() => Blocks::PACKED_ICE());
		$this->map(Ids::PINK_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::PINK(), $in));
		$this->map(Ids::PLANKS, function(Reader $in) : Block{
			return match($woodName = $in->readString(StateNames::WOOD_TYPE)){
				StringValues::WOOD_TYPE_OAK => Blocks::OAK_PLANKS(),
				StringValues::WOOD_TYPE_SPRUCE => Blocks::SPRUCE_PLANKS(),
				StringValues::WOOD_TYPE_BIRCH => Blocks::BIRCH_PLANKS(),
				StringValues::WOOD_TYPE_JUNGLE => Blocks::JUNGLE_PLANKS(),
				StringValues::WOOD_TYPE_ACACIA => Blocks::ACACIA_PLANKS(),
				StringValues::WOOD_TYPE_DARK_OAK => Blocks::DARK_OAK_PLANKS(),
				default => throw $in->badValueException(StateNames::WOOD_TYPE, $woodName),
			};
		});
		$this->map(Ids::PODZOL, fn() => Blocks::PODZOL());
		$this->mapStairs(Ids::POLISHED_ANDESITE_STAIRS, fn() => Blocks::POLISHED_ANDESITE_STAIRS());
		$this->map(Ids::POLISHED_BASALT, function(Reader $in) : Block{
			return Blocks::POLISHED_BASALT()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::POLISHED_BLACKSTONE, fn() => Blocks::POLISHED_BLACKSTONE());
		$this->map(Ids::POLISHED_BLACKSTONE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::POLISHED_BLACKSTONE_BUTTON(), $in));
		$this->mapSlab(Ids::POLISHED_BLACKSTONE_SLAB, Ids::POLISHED_BLACKSTONE_DOUBLE_SLAB, fn() => Blocks::POLISHED_BLACKSTONE_SLAB());
		$this->map(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::POLISHED_BLACKSTONE_STAIRS, fn() => Blocks::POLISHED_BLACKSTONE_STAIRS());
		$this->map(Ids::POLISHED_BLACKSTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::POLISHED_BLACKSTONE_WALL(), $in));
		$this->map(Ids::POLISHED_BLACKSTONE_BRICKS, fn() => Blocks::POLISHED_BLACKSTONE_BRICKS());
		$this->mapSlab(Ids::POLISHED_BLACKSTONE_BRICK_SLAB, Ids::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB, fn() => Blocks::POLISHED_BLACKSTONE_BRICK_SLAB());
		$this->mapStairs(Ids::POLISHED_BLACKSTONE_BRICK_STAIRS, fn() => Blocks::POLISHED_BLACKSTONE_BRICK_STAIRS());
		$this->map(Ids::POLISHED_BLACKSTONE_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::POLISHED_BLACKSTONE_BRICK_WALL(), $in));
		$this->map(Ids::POLISHED_DEEPSLATE, fn() => Blocks::POLISHED_DEEPSLATE());
		$this->mapSlab(Ids::POLISHED_DEEPSLATE_SLAB, Ids::POLISHED_DEEPSLATE_DOUBLE_SLAB, fn() => Blocks::POLISHED_DEEPSLATE_SLAB());
		$this->mapStairs(Ids::POLISHED_DEEPSLATE_STAIRS, fn() => Blocks::POLISHED_DEEPSLATE_STAIRS());
		$this->map(Ids::POLISHED_DEEPSLATE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::POLISHED_DEEPSLATE_WALL(), $in));
		$this->mapStairs(Ids::POLISHED_DIORITE_STAIRS, fn() => Blocks::POLISHED_DIORITE_STAIRS());
		$this->mapStairs(Ids::POLISHED_GRANITE_STAIRS, fn() => Blocks::POLISHED_GRANITE_STAIRS());
		$this->map(Ids::PORTAL, function(Reader $in) : Block{
			return Blocks::NETHER_PORTAL()
				->setAxis(match($value = $in->readString(StateNames::PORTAL_AXIS)){
					StringValues::PORTAL_AXIS_UNKNOWN => Axis::X,
					StringValues::PORTAL_AXIS_X => Axis::X,
					StringValues::PORTAL_AXIS_Z => Axis::Z,
					default => throw $in->badValueException(StateNames::PORTAL_AXIS, $value),
				});
		});
		$this->map(Ids::POTATOES, fn(Reader $in) => Helper::decodeCrops(Blocks::POTATOES(), $in));
		$this->map(Ids::POWERED_COMPARATOR, fn(Reader $in) => Helper::decodeComparator(Blocks::REDSTONE_COMPARATOR(), $in));
		$this->map(Ids::POWERED_REPEATER, fn(Reader $in) => Helper::decodeRepeater(Blocks::REDSTONE_REPEATER(), $in)
				->setPowered(true));
		$this->map(Ids::PRISMARINE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::PRISMARINE_BLOCK_TYPE)){
				StringValues::PRISMARINE_BLOCK_TYPE_BRICKS => Blocks::PRISMARINE_BRICKS(),
				StringValues::PRISMARINE_BLOCK_TYPE_DARK => Blocks::DARK_PRISMARINE(),
				StringValues::PRISMARINE_BLOCK_TYPE_DEFAULT => Blocks::PRISMARINE(),
				default => throw $in->badValueException(StateNames::PRISMARINE_BLOCK_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::PRISMARINE_BRICKS_STAIRS, fn() => Blocks::PRISMARINE_BRICKS_STAIRS());
		$this->mapStairs(Ids::PRISMARINE_STAIRS, fn() => Blocks::PRISMARINE_STAIRS());
		$this->map(Ids::PUMPKIN, function(Reader $in) : Block{
			$in->ignored(StateNames::DIRECTION); //obsolete
			return Blocks::PUMPKIN();
		});
		$this->map(Ids::PUMPKIN_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::PUMPKIN_STEM(), $in));
		$this->map(Ids::PURPLE_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::PURPLE(), $in));
		$this->map(Ids::PURPUR_BLOCK, function(Reader $in) : Block{
			$type = $in->readString(StateNames::CHISEL_TYPE);
			if($type === StringValues::CHISEL_TYPE_LINES){
				return Blocks::PURPUR_PILLAR()->setAxis($in->readPillarAxis());
			}else{
				$in->ignored(StateNames::PILLAR_AXIS); //axis only applies to pillars
				return match($type){
					StringValues::CHISEL_TYPE_CHISELED, //TODO: bug in MCPE
					StringValues::CHISEL_TYPE_SMOOTH, //TODO: bug in MCPE
					StringValues::CHISEL_TYPE_DEFAULT => Blocks::PURPUR(),
					default => throw $in->badValueException(StateNames::CHISEL_TYPE, $type),
				};
			}
		});
		$this->mapStairs(Ids::PURPUR_STAIRS, fn() => Blocks::PURPUR_STAIRS());
		$this->map(Ids::QUARTZ_BLOCK, function(Reader $in) : Block{
			switch($type = $in->readString(StateNames::CHISEL_TYPE)){
				case StringValues::CHISEL_TYPE_CHISELED:
					return Blocks::CHISELED_QUARTZ()->setAxis($in->readPillarAxis());
				case StringValues::CHISEL_TYPE_DEFAULT:
					$in->ignored(StateNames::PILLAR_AXIS);
					return Blocks::QUARTZ();
				case StringValues::CHISEL_TYPE_LINES:
					return Blocks::QUARTZ_PILLAR()->setAxis($in->readPillarAxis());
				case StringValues::CHISEL_TYPE_SMOOTH:
					$in->ignored(StateNames::PILLAR_AXIS);
					return Blocks::SMOOTH_QUARTZ();
				default:
					return throw $in->badValueException(StateNames::CHISEL_TYPE, $type);
			}
		});
		$this->map(Ids::QUARTZ_BRICKS, fn() => Blocks::QUARTZ_BRICKS());
		$this->map(Ids::QUARTZ_ORE, fn() => Blocks::NETHER_QUARTZ_ORE());
		$this->mapStairs(Ids::QUARTZ_STAIRS, fn() => Blocks::QUARTZ_STAIRS());
		$this->map(Ids::RAIL, function(Reader $in) : Block{
			return Blocks::RAIL()
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 9));
		});
		$this->map(Ids::RAW_COPPER_BLOCK, fn() => Blocks::RAW_COPPER());
		$this->map(Ids::RAW_GOLD_BLOCK, fn() => Blocks::RAW_GOLD());
		$this->map(Ids::RAW_IRON_BLOCK, fn() => Blocks::RAW_IRON());
		$this->map(Ids::RED_FLOWER, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::FLOWER_TYPE)){
				StringValues::FLOWER_TYPE_ALLIUM => Blocks::ALLIUM(),
				StringValues::FLOWER_TYPE_CORNFLOWER => Blocks::CORNFLOWER(),
				StringValues::FLOWER_TYPE_HOUSTONIA => Blocks::AZURE_BLUET(), //wtf ???
				StringValues::FLOWER_TYPE_LILY_OF_THE_VALLEY => Blocks::LILY_OF_THE_VALLEY(),
				StringValues::FLOWER_TYPE_ORCHID => Blocks::BLUE_ORCHID(),
				StringValues::FLOWER_TYPE_OXEYE => Blocks::OXEYE_DAISY(),
				StringValues::FLOWER_TYPE_POPPY => Blocks::POPPY(),
				StringValues::FLOWER_TYPE_TULIP_ORANGE => Blocks::ORANGE_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_PINK => Blocks::PINK_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_RED => Blocks::RED_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_WHITE => Blocks::WHITE_TULIP(),
				default => throw $in->badValueException(StateNames::FLOWER_TYPE, $type),
			};
		});
		$this->map(Ids::RED_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::RED(), $in));
		$this->map(Ids::RED_MUSHROOM, fn() => Blocks::RED_MUSHROOM());
		$this->map(Ids::RED_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::RED_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::RED_NETHER_BRICK, fn() => Blocks::RED_NETHER_BRICKS());
		$this->mapStairs(Ids::RED_NETHER_BRICK_STAIRS, fn() => Blocks::RED_NETHER_BRICK_STAIRS());
		$this->map(Ids::RED_SANDSTONE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::SAND_STONE_TYPE)){
				StringValues::SAND_STONE_TYPE_CUT => Blocks::CUT_RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_DEFAULT => Blocks::RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_HEIROGLYPHS => Blocks::CHISELED_RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_SMOOTH => Blocks::SMOOTH_RED_SANDSTONE(),
				default => throw $in->badValueException(StateNames::SAND_STONE_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::RED_SANDSTONE_STAIRS, fn() => Blocks::RED_SANDSTONE_STAIRS());
		$this->map(Ids::REDSTONE_BLOCK, fn() => Blocks::REDSTONE());
		$this->map(Ids::REDSTONE_LAMP, function() : Block{
			return Blocks::REDSTONE_LAMP()
				->setPowered(false);
		});
		$this->map(Ids::REDSTONE_ORE, function() : Block{
			return Blocks::REDSTONE_ORE()
				->setLit(false);
		});
		$this->map(Ids::REDSTONE_TORCH, function(Reader $in) : Block{
			return Blocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(true);
		});
		$this->map(Ids::REDSTONE_WIRE, function(Reader $in) : Block{
			return Blocks::REDSTONE_WIRE()
				->setOutputSignalStrength($in->readBoundedInt(StateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::REEDS, function(Reader $in) : Block{
			return Blocks::SUGARCANE()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::RESERVED6, fn() => Blocks::RESERVED6());
		$this->map(Ids::SAND, function(Reader $in) : Block{
			return match($value = $in->readString(StateNames::SAND_TYPE)){
				StringValues::SAND_TYPE_NORMAL => Blocks::SAND(),
				StringValues::SAND_TYPE_RED => Blocks::RED_SAND(),
				default => throw $in->badValueException(StateNames::SAND_TYPE, $value),
			};
		});
		$this->map(Ids::SANDSTONE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::SAND_STONE_TYPE)){
				StringValues::SAND_STONE_TYPE_CUT => Blocks::CUT_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_DEFAULT => Blocks::SANDSTONE(),
				StringValues::SAND_STONE_TYPE_HEIROGLYPHS => Blocks::CHISELED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_SMOOTH => Blocks::SMOOTH_SANDSTONE(),
				default => throw $in->badValueException(StateNames::SAND_STONE_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::SANDSTONE_STAIRS, fn() => Blocks::SANDSTONE_STAIRS());
		$this->map(Ids::SAPLING, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::SAPLING_TYPE)){
					StringValues::SAPLING_TYPE_ACACIA => Blocks::ACACIA_SAPLING(),
					StringValues::SAPLING_TYPE_BIRCH => Blocks::BIRCH_SAPLING(),
					StringValues::SAPLING_TYPE_DARK_OAK => Blocks::DARK_OAK_SAPLING(),
					StringValues::SAPLING_TYPE_JUNGLE => Blocks::JUNGLE_SAPLING(),
					StringValues::SAPLING_TYPE_OAK => Blocks::OAK_SAPLING(),
					StringValues::SAPLING_TYPE_SPRUCE => Blocks::SPRUCE_SAPLING(),
					default => throw $in->badValueException(StateNames::SAPLING_TYPE, $type),
				})
				->setReady($in->readBool(StateNames::AGE_BIT));
		});
		$this->map(Ids::SEA_LANTERN, fn() => Blocks::SEA_LANTERN());
		$this->map(Ids::SEA_PICKLE, function(Reader $in) : Block{
			return Blocks::SEA_PICKLE()
				->setCount($in->readBoundedInt(StateNames::CLUSTER_COUNT, 0, 3) + 1)
				->setUnderwater(!$in->readBool(StateNames::DEAD_BIT));
		});
		$this->map(Ids::SHROOMLIGHT, fn() => Blocks::SHROOMLIGHT());
		$this->map(Ids::SHULKER_BOX, function(Reader $in) : Block{
			return Blocks::DYED_SHULKER_BOX()
				->setColor($in->readColor());
		});
		$this->map(Ids::SILVER_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::LIGHT_GRAY(), $in));
		$this->map(Ids::SKULL, function(Reader $in) : Block{
			return Blocks::MOB_HEAD()
				->setFacing($in->readFacingWithoutDown());
		});
		$this->map(Ids::SLIME, fn() => Blocks::SLIME());
		$this->map(Ids::SMOKER, function(Reader $in) : Block{
			return Blocks::SMOKER()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::SMOOTH_BASALT, fn() => Blocks::SMOOTH_BASALT());
		$this->mapStairs(Ids::SMOOTH_QUARTZ_STAIRS, fn() => Blocks::SMOOTH_QUARTZ_STAIRS());
		$this->mapStairs(Ids::SMOOTH_RED_SANDSTONE_STAIRS, fn() => Blocks::SMOOTH_RED_SANDSTONE_STAIRS());
		$this->mapStairs(Ids::SMOOTH_SANDSTONE_STAIRS, fn() => Blocks::SMOOTH_SANDSTONE_STAIRS());
		$this->map(Ids::SMOOTH_STONE, fn() => Blocks::SMOOTH_STONE());
		$this->map(Ids::SNOW, fn() => Blocks::SNOW());
		$this->map(Ids::SNOW_LAYER, function(Reader $in) : Block{
			$in->ignored(StateNames::COVERED_BIT); //seems to be useless
			return Blocks::SNOW_LAYER()->setLayers($in->readBoundedInt(StateNames::HEIGHT, 0, 7) + 1);
		});
		$this->map(Ids::SOUL_FIRE, function(Reader $in) : Block{
			$in->ignored(StateNames::AGE); //this is useless for soul fire, since it doesn't have the logic associated
			return Blocks::SOUL_FIRE();
		});
		$this->map(Ids::SOUL_LANTERN, function(Reader $in) : Block{
			return Blocks::SOUL_LANTERN()
				->setHanging($in->readBool(StateNames::HANGING));
		});
		$this->map(Ids::SOUL_SAND, fn() => Blocks::SOUL_SAND());
		$this->map(Ids::SOUL_SOIL, fn() => Blocks::SOUL_SOIL());
		$this->map(Ids::SOUL_TORCH, function(Reader $in) : Block{
			return Blocks::SOUL_TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::SPONGE, function(Reader $in) : Block{
			return Blocks::SPONGE()->setWet(match($type = $in->readString(StateNames::SPONGE_TYPE)){
				StringValues::SPONGE_TYPE_DRY => false,
				StringValues::SPONGE_TYPE_WET => true,
				default => throw $in->badValueException(StateNames::SPONGE_TYPE, $type),
			});
		});
		$this->map(Ids::SPRUCE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::SPRUCE_BUTTON(), $in));
		$this->map(Ids::SPRUCE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::SPRUCE_DOOR(), $in));
		$this->map(Ids::SPRUCE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::SPRUCE_FENCE_GATE(), $in));
		$this->map(Ids::SPRUCE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::SPRUCE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::SPRUCE_STAIRS, fn() => Blocks::SPRUCE_STAIRS());
		$this->map(Ids::SPRUCE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::SPRUCE_SIGN(), $in));
		$this->map(Ids::SPRUCE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::SPRUCE_TRAPDOOR(), $in));
		$this->map(Ids::SPRUCE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::SPRUCE_WALL_SIGN(), $in));
		$this->map(Ids::STAINED_GLASS, function(Reader $in) : Block{
			return Blocks::STAINED_GLASS()
				->setColor($in->readColor());
		});
		$this->map(Ids::STAINED_GLASS_PANE, function(Reader $in) : Block{
			return Blocks::STAINED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->map(Ids::STAINED_HARDENED_CLAY, function(Reader $in) : Block{
			return Blocks::STAINED_CLAY()
				->setColor($in->readColor());
		});
		$this->map(Ids::STANDING_BANNER, function(Reader $in) : Block{
			return Blocks::BANNER()
				->setRotation($in->readBoundedInt(StateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::OAK_SIGN(), $in));
		$this->map(Ids::STONE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::STONE_TYPE)){
				StringValues::STONE_TYPE_ANDESITE => Blocks::ANDESITE(),
				StringValues::STONE_TYPE_ANDESITE_SMOOTH => Blocks::POLISHED_ANDESITE(),
				StringValues::STONE_TYPE_DIORITE => Blocks::DIORITE(),
				StringValues::STONE_TYPE_DIORITE_SMOOTH => Blocks::POLISHED_DIORITE(),
				StringValues::STONE_TYPE_GRANITE => Blocks::GRANITE(),
				StringValues::STONE_TYPE_GRANITE_SMOOTH => Blocks::POLISHED_GRANITE(),
				StringValues::STONE_TYPE_STONE => Blocks::STONE(),
				default => throw $in->badValueException(StateNames::STONE_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::STONE_BRICK_STAIRS, fn() => Blocks::STONE_BRICK_STAIRS());
		$this->map(Ids::STONE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::STONE_BUTTON(), $in));
		$this->map(Ids::STONE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::STONE_PRESSURE_PLATE(), $in));
		$this->map(Ids::STONE_BLOCK_SLAB, fn(Reader $in) => Helper::mapStoneSlab1Type($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::STONE_BLOCK_SLAB2, fn(Reader $in) => Helper::mapStoneSlab2Type($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::STONE_BLOCK_SLAB3, fn(Reader $in) => Helper::mapStoneSlab3Type($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::STONE_BLOCK_SLAB4, fn(Reader $in) => Helper::mapStoneSlab4Type($in)->setSlabType($in->readSlabPosition()));
		$this->mapStairs(Ids::STONE_STAIRS, fn() => Blocks::COBBLESTONE_STAIRS());
		$this->map(Ids::STONEBRICK, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::STONE_BRICK_TYPE)){
				StringValues::STONE_BRICK_TYPE_SMOOTH, //TODO: bug in vanilla
				StringValues::STONE_BRICK_TYPE_DEFAULT => Blocks::STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_CHISELED => Blocks::CHISELED_STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_CRACKED => Blocks::CRACKED_STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_MOSSY => Blocks::MOSSY_STONE_BRICKS(),
				default => throw $in->badValueException(StateNames::STONE_BRICK_TYPE, $type),
			};
		});
		$this->map(Ids::STONECUTTER, fn() => Blocks::LEGACY_STONECUTTER());
		$this->map(Ids::STONECUTTER_BLOCK, function(Reader $in) : Block{
			return Blocks::STONECUTTER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::STRIPPED_ACACIA_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::ACACIA_LOG(), true, $in));
		$this->map(Ids::STRIPPED_BIRCH_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::BIRCH_LOG(), true, $in));
		$this->map(Ids::STRIPPED_CRIMSON_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_HYPHAE(), true, $in));
		$this->map(Ids::STRIPPED_CRIMSON_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_STEM(), true, $in));
		$this->map(Ids::STRIPPED_DARK_OAK_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::DARK_OAK_LOG(), true, $in));
		$this->map(Ids::STRIPPED_JUNGLE_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::JUNGLE_LOG(), true, $in));
		$this->map(Ids::STRIPPED_MANGROVE_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_LOG(), true, $in));
		$this->map(Ids::STRIPPED_MANGROVE_WOOD, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_WOOD(), true, $in));
		$this->map(Ids::STRIPPED_OAK_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::OAK_LOG(), true, $in));
		$this->map(Ids::STRIPPED_SPRUCE_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::SPRUCE_LOG(), true, $in));
		$this->map(Ids::STRIPPED_WARPED_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_HYPHAE(), true, $in));
		$this->map(Ids::STRIPPED_WARPED_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_STEM(), true, $in));
		$this->map(Ids::SWEET_BERRY_BUSH, function(Reader $in) : Block{
			//berry bush only wants 0-3, but it can be bigger in MCPE due to misuse of GROWTH state which goes up to 7
			$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
			return Blocks::SWEET_BERRY_BUSH()
				->setAge(min($growth, SweetBerryBush::STAGE_MATURE));
		});
		$this->map(Ids::TALLGRASS, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::TALL_GRASS_TYPE)){
				StringValues::TALL_GRASS_TYPE_DEFAULT, StringValues::TALL_GRASS_TYPE_SNOW, StringValues::TALL_GRASS_TYPE_TALL => Blocks::TALL_GRASS(),
				StringValues::TALL_GRASS_TYPE_FERN => Blocks::FERN(),
				default => throw $in->badValueException(StateNames::TALL_GRASS_TYPE, $type),
			};
		});
		$this->map(Ids::TINTED_GLASS, fn() => Blocks::TINTED_GLASS());
		$this->map(Ids::TNT, function(Reader $in) : Block{
			return Blocks::TNT()
				->setUnstable($in->readBool(StateNames::EXPLODE_BIT))
				->setWorksUnderwater($in->readBool(StateNames::ALLOW_UNDERWATER_BIT));
		});
		$this->map(Ids::TORCH, function(Reader $in) : Block{
			return Blocks::TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::OAK_TRAPDOOR(), $in));
		$this->map(Ids::TRAPPED_CHEST, function(Reader $in) : Block{
			return Blocks::TRAPPED_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::TRIP_WIRE, function(Reader $in) : Block{
			return Blocks::TRIPWIRE()
				->setConnected($in->readBool(StateNames::ATTACHED_BIT))
				->setDisarmed($in->readBool(StateNames::DISARMED_BIT))
				->setSuspended($in->readBool(StateNames::SUSPENDED_BIT))
				->setTriggered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::TRIPWIRE_HOOK, function(Reader $in) : Block{
			return Blocks::TRIPWIRE_HOOK()
				->setConnected($in->readBool(StateNames::ATTACHED_BIT))
				->setFacing($in->readLegacyHorizontalFacing())
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::TUFF, fn() => Blocks::TUFF());
		$this->map(Ids::UNDERWATER_TORCH, function(Reader $in) : Block{
			return Blocks::UNDERWATER_TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::UNDYED_SHULKER_BOX, fn() => Blocks::SHULKER_BOX());
		$this->map(Ids::UNLIT_REDSTONE_TORCH, function(Reader $in) : Block{
			return Blocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(false);
		});
		$this->map(Ids::UNPOWERED_COMPARATOR, fn(Reader $in) => Helper::decodeComparator(Blocks::REDSTONE_COMPARATOR(), $in));
		$this->map(Ids::UNPOWERED_REPEATER, fn(Reader $in) => Helper::decodeRepeater(Blocks::REDSTONE_REPEATER(), $in)
				->setPowered(false));
		$this->map(Ids::VINE, function(Reader $in) : Block{
			$vineDirectionFlags = $in->readBoundedInt(StateNames::VINE_DIRECTION_BITS, 0, 15);
			return Blocks::VINES()
				->setFace(Facing::NORTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_NORTH) !== 0)
				->setFace(Facing::SOUTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_SOUTH) !== 0)
				->setFace(Facing::WEST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_WEST) !== 0)
				->setFace(Facing::EAST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_EAST) !== 0);
		});
		$this->map(Ids::WALL_BANNER, function(Reader $in) : Block{
			return Blocks::WALL_BANNER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::OAK_WALL_SIGN(), $in));
		$this->map(Ids::WARPED_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::WARPED_BUTTON(), $in));
		$this->map(Ids::WARPED_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::WARPED_DOOR(), $in));
		$this->mapSlab(Ids::WARPED_SLAB, Ids::WARPED_DOUBLE_SLAB, fn() => Blocks::WARPED_SLAB());
		$this->map(Ids::WARPED_FENCE, fn() => Blocks::WARPED_FENCE());
		$this->map(Ids::WARPED_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::WARPED_FENCE_GATE(), $in));
		$this->map(Ids::WARPED_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_HYPHAE(), false, $in));
		$this->map(Ids::WARPED_PLANKS, fn() => Blocks::WARPED_PLANKS());
		$this->map(Ids::WARPED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::WARPED_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::WARPED_STAIRS, fn() => Blocks::WARPED_STAIRS());
		$this->map(Ids::WARPED_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::WARPED_SIGN(), $in));
		$this->map(Ids::WARPED_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_STEM(), false, $in));
		$this->map(Ids::WARPED_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::WARPED_TRAPDOOR(), $in));
		$this->map(Ids::WARPED_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::WARPED_WALL_SIGN(), $in));
		$this->map(Ids::WATER, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::WATER(), $in));
		$this->map(Ids::WATERLILY, fn() => Blocks::LILY_PAD());
		$this->map(Ids::WEB, fn() => Blocks::COBWEB());
		$this->map(Ids::WHEAT, fn(Reader $in) => Helper::decodeCrops(Blocks::WHEAT(), $in));
		$this->map(Ids::WHITE_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::WHITE(), $in));
		$this->map(Ids::WOOD, fn(Reader $in) : Block => Helper::decodeLog(match($woodType = $in->readString(StateNames::WOOD_TYPE)){
			StringValues::WOOD_TYPE_ACACIA => Blocks::ACACIA_WOOD(),
			StringValues::WOOD_TYPE_BIRCH => Blocks::BIRCH_WOOD(),
			StringValues::WOOD_TYPE_DARK_OAK => Blocks::DARK_OAK_WOOD(),
			StringValues::WOOD_TYPE_JUNGLE => Blocks::JUNGLE_WOOD(),
			StringValues::WOOD_TYPE_OAK => Blocks::OAK_WOOD(),
			StringValues::WOOD_TYPE_SPRUCE => Blocks::SPRUCE_WOOD(),
			default => throw $in->badValueException(StateNames::WOOD_TYPE, $woodType),
		}, $in->readBool(StateNames::STRIPPED_BIT), $in));
		$this->map(Ids::WOODEN_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::OAK_BUTTON(), $in));
		$this->map(Ids::WOODEN_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::OAK_DOOR(), $in));
		$this->map(Ids::WOODEN_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::OAK_PRESSURE_PLATE(), $in));
		$this->map(Ids::WOODEN_SLAB, fn(Reader $in) => Helper::mapWoodenSlabType($in)->setSlabType($in->readSlabPosition()));
		$this->map(Ids::WOOL, function(Reader $in) : Block{
			return Blocks::WOOL()
				->setColor($in->readColor());
		});
		$this->map(Ids::YELLOW_FLOWER, fn() => Blocks::DANDELION());
		$this->map(Ids::YELLOW_GLAZED_TERRACOTTA, fn(Reader $in) => Helper::decodeGlazedTerracotta(DyeColor::YELLOW(), $in));
		//$this->map(Ids::ALLOW, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::AMETHYST_CLUSTER, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::AZALEA, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::AZALEA_LEAVES, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::AZALEA_LEAVES_FLOWERED, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BEE_NEST, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * honey_level (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::BEEHIVE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * honey_level (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::BIG_DRIPLEAF, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * big_dripleaf_head (ByteTag) = 0, 1
			 * big_dripleaf_tilt (StringTag) = full_tilt, none, partial_tilt, unstable
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::BLACK_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLACK_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLUE_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BLUE_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BORDER_BLOCK, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BROWN_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BROWN_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BUBBLE_COLUMN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * drag_down (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::BUDDING_AMETHYST, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CALCITE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CAMERA, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CAMPFIRE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * extinguished (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CARTOGRAPHY_TABLE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CAULDRON, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * cauldron_liquid (StringTag) = lava, powder_snow, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->map(Ids::CAVE_VINES, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::CAVE_VINES_BODY_WITH_BERRIES, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::CAVE_VINES_HEAD_WITH_BERRIES, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * growing_plant_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::CHAIN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::CHAIN_COMMAND_BLOCK, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CHISELED_DEEPSLATE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CHISELED_NETHER_BRICKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CHORUS_FLOWER, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * age (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CHORUS_PLANT, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CLIENT_REQUEST_PLACEHOLDER_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::COBBLED_DEEPSLATE_WALL, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::COMMAND_BLOCK, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::COMPOSTER, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * composter_fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8
			 */
		//});
		//$this->map(Ids::CONDUIT, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COPPER_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::COPPER_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRACKED_DEEPSLATE_BRICKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRACKED_DEEPSLATE_TILES, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRACKED_NETHER_BRICKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_BUTTON, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CRIMSON_DOOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_FENCE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_FENCE_GATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_FUNGUS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_HYPHAE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::CRIMSON_NYLIUM, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_PLANKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_PRESSURE_PLATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::CRIMSON_ROOTS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CRIMSON_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::CRIMSON_STANDING_SIGN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::CRIMSON_STEM, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::CRIMSON_TRAPDOOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CRIMSON_WALL_SIGN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::CRYING_OBSIDIAN, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::CYAN_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::CYAN_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICK_WALL, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_BRICKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_COAL_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_COPPER_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_DIAMOND_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_EMERALD_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_GOLD_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_IRON_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_LAPIS_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_REDSTONE_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILE_WALL, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DEEPSLATE_TILES, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DENY, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DIRT_WITH_ROOTS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DISPENSER, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::DRIPSTONE_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::DROPPER, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::END_GATEWAY, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::END_PORTAL, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::EXPOSED_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::EXPOSED_CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::EXPOSED_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::EXPOSED_CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::EXPOSED_DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::FLOWERING_AZALEA, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::FROG_SPAWN, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::GILDED_BLACKSTONE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::GLOW_FRAME, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * item_frame_map_bit (ByteTag) = 0, 1
			 * item_frame_photo_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GLOW_LICHEN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * multi_face_direction_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63
			 */
		//});
		//$this->map(Ids::GRAY_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GRAY_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GREEN_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GREEN_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::GRINDSTONE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * attachment (StringTag) = hanging, multiple, side, standing
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::HANGING_ROOTS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::HONEY_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::HONEYCOMB_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::INFESTED_DEEPSLATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::JIGSAW, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * rotation (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::KELP, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * kelp_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::LARGE_AMETHYST_BUD, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::LAVA_CAULDRON, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * cauldron_liquid (StringTag) = lava, powder_snow, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->map(Ids::LIGHT_BLUE_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHT_BLUE_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHT_GRAY_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHT_GRAY_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIGHTNING_ROD, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::LIME_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIME_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::LIT_DEEPSLATE_REDSTONE_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::LODESTONE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MAGENTA_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MAGENTA_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MANGROVE_BUTTON, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::MANGROVE_DOOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MANGROVE_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MANGROVE_FENCE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MANGROVE_FENCE_GATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MANGROVE_LEAVES, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * persistent_bit (ByteTag) = 0, 1
			 * update_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MANGROVE_LOG, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::MANGROVE_PLANKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MANGROVE_PRESSURE_PLATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::MANGROVE_PROPAGULE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * hanging (ByteTag) = 0, 1
			 * propagule_stage (IntTag) = 0, 1, 2, 3, 4
			 */
		//});
		//$this->map(Ids::MANGROVE_ROOTS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MANGROVE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MANGROVE_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::MANGROVE_STANDING_SIGN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::MANGROVE_TRAPDOOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MANGROVE_WALL_SIGN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::MANGROVE_WOOD, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 * stripped_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MEDIUM_AMETHYST_BUD, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::MOSS_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MOSS_CARPET, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MOVING_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MUD, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MUD_BRICK_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MUD_BRICK_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MUD_BRICK_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::MUD_BRICK_WALL, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::MUD_BRICKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::MUDDY_MANGROVE_ROOTS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::NETHER_GOLD_ORE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::NETHER_SPROUTS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::NETHERITE_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::OBSERVER, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::OCHRE_FROGLIGHT, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::ORANGE_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::ORANGE_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::OXIDIZED_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::OXIDIZED_CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::OXIDIZED_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::OXIDIZED_CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::OXIDIZED_DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PACKED_MUD, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::PEARLESCENT_FROGLIGHT, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::PINK_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PINK_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PISTON, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::PISTON_ARM_COLLISION, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::POINTED_DRIPSTONE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * dripstone_thickness (StringTag) = base, frustum, merge, middle, tip
			 * hanging (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::POLISHED_DEEPSLATE_WALL, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * wall_connection_type_east (StringTag) = none, short, tall
			 * wall_connection_type_north (StringTag) = none, short, tall
			 * wall_connection_type_south (StringTag) = none, short, tall
			 * wall_connection_type_west (StringTag) = none, short, tall
			 * wall_post_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::POWDER_SNOW, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::PURPLE_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::PURPLE_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::QUARTZ_BRICKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::RED_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::RED_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::REINFORCED_DEEPSLATE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::REPEATING_COMMAND_BLOCK, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::RESPAWN_ANCHOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * respawn_anchor_charge (IntTag) = 0, 1, 2, 3, 4
			 */
		//});
		//$this->map(Ids::SCAFFOLDING, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * stability (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 * stability_check (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SCULK_CATALYST, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * bloom (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK_SENSOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK_SHRIEKER, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * active (ByteTag) = 0, 1
			 * can_summon (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SCULK_VEIN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * multi_face_direction_bits (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63
			 */
		//});
		//$this->map(Ids::SEAGRASS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * sea_grass_type (StringTag) = default, double_bot, double_top
			 */
		//});
		//$this->map(Ids::SHROOMLIGHT, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SMALL_AMETHYST_BUD, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::SMALL_DRIPLEAF_BLOCK, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SMITHING_TABLE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SOUL_CAMPFIRE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * extinguished (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SOUL_FIRE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::SOUL_LANTERN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * hanging (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::SOUL_SOIL, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::SOUL_TORCH, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * torch_facing_direction (StringTag) = east, north, south, top, unknown, west
			 */
		//});
		//$this->map(Ids::SPORE_BLOSSOM, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::STICKY_PISTON, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::STICKY_PISTON_ARM_COLLISION, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::STRIPPED_CRIMSON_HYPHAE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_CRIMSON_STEM, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_MANGROVE_LOG, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_MANGROVE_WOOD, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_WARPED_HYPHAE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRIPPED_WARPED_STEM, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::STRUCTURE_BLOCK, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * structure_block_type (StringTag) = corner, data, export, invalid, load, save
			 */
		//});
		//$this->map(Ids::STRUCTURE_VOID, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * structure_void_type (StringTag) = air, void
			 */
		//});
		//$this->map(Ids::TARGET, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::TINTED_GLASS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::TUFF, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::TURTLE_EGG, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * cracked_state (StringTag) = cracked, max_cracked, no_cracks
			 * turtle_egg_count (StringTag) = four_egg, one_egg, three_egg, two_egg
			 */
		//});
		//$this->map(Ids::TWISTING_VINES, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * twisting_vines_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::UNKNOWN, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::VERDANT_FROGLIGHT, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::WARPED_BUTTON, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * button_pressed_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::WARPED_DOOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * door_hinge_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 * upper_block_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_DOUBLE_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_FENCE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_FENCE_GATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * in_wall_bit (ByteTag) = 0, 1
			 * open_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_FUNGUS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_HYPHAE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::WARPED_NYLIUM, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_PLANKS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_PRESSURE_PLATE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * redstone_signal (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::WARPED_ROOTS, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WARPED_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WARPED_STANDING_SIGN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * ground_sign_direction (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->map(Ids::WARPED_STEM, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * pillar_axis (StringTag) = x, y, z
			 */
		//});
		//$this->map(Ids::WARPED_TRAPDOOR, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * direction (IntTag) = 0, 1, 2, 3
			 * open_bit (ByteTag) = 0, 1
			 * upside_down_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WARPED_WALL_SIGN, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->map(Ids::WARPED_WART_BLOCK, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_EXPOSED_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_EXPOSED_CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_EXPOSED_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_EXPOSED_CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_WEATHERED_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_WEATHERED_CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WAXED_WEATHERED_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WAXED_WEATHERED_CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WEATHERED_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WEATHERED_CUT_COPPER, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::WEATHERED_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WEATHERED_CUT_COPPER_STAIRS, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * upside_down_bit (ByteTag) = 0, 1
			 * weirdo_direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->map(Ids::WEATHERED_DOUBLE_CUT_COPPER_SLAB, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * top_slot_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WEEPING_VINES, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * weeping_vines_age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25
			 */
		//});
		//$this->map(Ids::WHITE_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WHITE_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::WITHER_ROSE, function(Reader $in) : Block{
			/* TODO: Un-implemented block */
		//});
		//$this->map(Ids::YELLOW_CANDLE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * candles (IntTag) = 0, 1, 2, 3
			 * lit (ByteTag) = 0, 1
			 */
		//});
		//$this->map(Ids::YELLOW_CANDLE_CAKE, function(Reader $in) : Block{
			/*
			 * TODO: Un-implemented block
			 * lit (ByteTag) = 0, 1
			 */
		//});
	}

	/** @throws BlockStateDeserializeException */
	public function deserializeBlock(BlockStateData $blockStateData) : Block{
		$id = $blockStateData->getName();
		if(!array_key_exists($id, $this->deserializeFuncs)){
			throw new UnsupportedBlockStateException("Unknown block ID \"$id\"");
		}
		$reader = new Reader($blockStateData);
		$block = $this->deserializeFuncs[$id]($reader);
		$reader->checkUnreadProperties();
		return $block;
	}
}
