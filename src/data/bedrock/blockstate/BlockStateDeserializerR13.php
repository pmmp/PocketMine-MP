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
use pocketmine\block\Button;
use pocketmine\block\Crops;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\FloorCoralFan;
use pocketmine\block\GlazedTerracotta;
use pocketmine\block\Liquid;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\SimplePressurePlate;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\Trapdoor;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\SlabType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wall;
use pocketmine\data\bedrock\blockstate\BlockStateStringValuesR13 as StringValues;
use pocketmine\data\bedrock\MushroomBlockTypeIdMap;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\AssumptionFailedError;
use function array_key_exists;
use function min;

final class BlockStateDeserializerR13{

	/**
	 * @var \Closure[]
	 * @phpstan-var array<string, \Closure(BlockStateReader $in) : Block>
	 */
	private array $deserializeFuncs = [];

	/** @phpstan-param \Closure(BlockStateReader) : Block $c */
	private function mapId(string $id, \Closure $c) : void{
		if(array_key_exists($id, $this->deserializeFuncs)){
			throw new \InvalidArgumentException("Deserializer is already assigned for \"$id\"");
		}
		$this->deserializeFuncs[$id] = $c;
	}

	/** @phpstan-param \Closure(BlockStateReader) : Block $c */
	private function mapVanilla(string $minecraftId, \Closure $c) : void{
		$this->mapId("minecraft:$minecraftId", $c);
	}

	/** @throws BlockStateDeserializeException */
	private function decodeButton(Button $block, BlockStateReader $in) : Button{
		return $block
			->setFacing($in->readFacingDirection())
			->setPressed($in->readBool(BlockStateNamesR13::BUTTON_PRESSED_BIT));
	}

	/** @throws BlockStateDeserializeException */
	private function decodeComparator(RedstoneComparator $block, BlockStateReader $in) : RedstoneComparator{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setPowered($in->readBool(BlockStateNamesR13::OUTPUT_LIT_BIT))
			->setSubtractMode($in->readBool(BlockStateNamesR13::OUTPUT_SUBTRACT_BIT));
	}

	/** @throws BlockStateDeserializeException */
	private function decodeCrops(Crops $block, BlockStateReader $in) : Crops{
		return $block->setAge($in->readBoundedInt(BlockStateNamesR13::GROWTH, 0, 7));
	}

	/** @throws BlockStateDeserializeException */
	private function decodeDoor(Door $block, BlockStateReader $in) : Door{
		//TODO: check if these need any special treatment to get the appropriate data to both halves of the door
		return $block
			->setTop($in->readBool(BlockStateNamesR13::UPPER_BLOCK_BIT))
			->setFacing(Facing::rotateY($in->readLegacyHorizontalFacing(), false))
			->setHingeRight($in->readBool(BlockStateNamesR13::DOOR_HINGE_BIT))
			->setOpen($in->readBool(BlockStateNamesR13::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	private function decodeFenceGate(FenceGate $block, BlockStateReader $in) : FenceGate{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setInWall($in->readBool(BlockStateNamesR13::IN_WALL_BIT))
			->setOpen($in->readBool(BlockStateNamesR13::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	private function decodeFloorCoralFan(BlockStateReader $in) : FloorCoralFan{
		return VanillaBlocks::CORAL_FAN()
			->setCoralType($in->readCoralType())
			->setAxis(match($in->readBoundedInt(BlockStateNamesR13::CORAL_FAN_DIRECTION, 0, 1)){
				0 => Axis::X,
				1 => Axis::Z,
				default => throw new AssumptionFailedError("readBoundedInt() should have prevented this"),
			});
	}

	/** @throws BlockStateDeserializeException */
	private function decodeGlazedTerracotta(GlazedTerracotta $block, BlockStateReader $in) : GlazedTerracotta{
		return $block->setFacing($in->readHorizontalFacing());
	}

	/** @throws BlockStateDeserializeException */
	private function decodeLiquid(Liquid $block, BlockStateReader $in, bool $still) : Liquid{
		$fluidHeightState = $in->readBoundedInt(BlockStateNamesR13::LIQUID_DEPTH, 0, 15);
		return $block
			->setDecay($fluidHeightState & 0x7)
			->setFalling(($fluidHeightState & 0x1) !== 0)
			->setStill($still);
	}

	private function decodeFlowingLiquid(Liquid $block, BlockStateReader $in) : Liquid{
		return $this->decodeLiquid($block, $in, false);
	}

	private function decodeStillLiquid(Liquid $block, BlockStateReader $in) : Liquid{
		return $this->decodeLiquid($block, $in, true);
	}

	/** @throws BlockStateDeserializeException */
	private function decodeMushroomBlock(RedMushroomBlock $block, BlockStateReader $in) : Block{
		switch($type = $in->readBoundedInt(BlockStateNamesR13::HUGE_MUSHROOM_BITS, 0, 15)){
			case BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM: return VanillaBlocks::ALL_SIDED_MUSHROOM_STEM();
			case BlockLegacyMetadata::MUSHROOM_BLOCK_STEM: return VanillaBlocks::MUSHROOM_STEM();
			default:
				//invalid types get left as default
				$type = MushroomBlockTypeIdMap::getInstance()->fromId($type);
				return $type !== null ? $block->setMushroomBlockType($type) : $block;
		}
	}

	/** @throws BlockStateDeserializeException */
	private function decodeRepeater(RedstoneRepeater $block, BlockStateReader $in) : RedstoneRepeater{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setDelay($in->readBoundedInt(BlockStateNamesR13::REPEATER_DELAY, 0, 3) + 1);
	}

	/** @throws BlockStateDeserializeException */
	private function decodeSimplePressurePlate(SimplePressurePlate $block, BlockStateReader $in) : SimplePressurePlate{
		//TODO: not sure what the deal is here ... seems like a mojang bug / artifact of bad implementation?
		//best to keep this separate from weighted plates anyway...
		return $block->setPressed($in->readBoundedInt(BlockStateNamesR13::REDSTONE_SIGNAL, 0, 15) !== 0);
	}

	/** @throws BlockStateDeserializeException */
	private function decodeStairs(Stair $block, BlockStateReader $in) : Stair{
		return $block
			->setUpsideDown($in->readBool(BlockStateNamesR13::UPSIDE_DOWN_BIT))
			->setFacing($in->readWeirdoHorizontalFacing());
	}

	/** @throws BlockStateDeserializeException */
	private function decodeTrapdoor(Trapdoor $block, BlockStateReader $in) : Trapdoor{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setTop($in->readBool(BlockStateNamesR13::UPSIDE_DOWN_BIT))
			->setOpen($in->readBool(BlockStateNamesR13::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	private function decodeWall(Wall $block, BlockStateReader $in) : Wall{
		//TODO: our walls don't support the full range of needed states yet
		return $block;
	}

	/** @throws BlockStateDeserializeException */
	private function mapStoneSlab1Type(BlockStateReader $in) : Slab{
		//* stone_slab_type (StringTag) = brick, cobblestone, nether_brick, quartz, sandstone, smooth_stone, stone_brick, wood
		return match($type = $in->readString(BlockStateNamesR13::STONE_SLAB_TYPE)){
			StringValues::STONE_SLAB_TYPE_BRICK => VanillaBlocks::BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_COBBLESTONE => VanillaBlocks::COBBLESTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_NETHER_BRICK => VanillaBlocks::NETHER_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_QUARTZ => VanillaBlocks::QUARTZ_SLAB(),
			StringValues::STONE_SLAB_TYPE_SANDSTONE => VanillaBlocks::SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_SMOOTH_STONE => VanillaBlocks::SMOOTH_STONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_STONE_BRICK => VanillaBlocks::STONE_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_WOOD => VanillaBlocks::FAKE_WOODEN_SLAB(),
			default => throw $in->badValueException(BlockStateNamesR13::STONE_SLAB_TYPE, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	private function mapStoneSlab2Type(BlockStateReader $in) : Slab{
		// * stone_slab_type_2 (StringTag) = mossy_cobblestone, prismarine_brick, prismarine_dark, prismarine_rough, purpur, red_nether_brick, red_sandstone, smooth_sandstone
		return match($type = $in->readString(BlockStateNamesR13::STONE_SLAB_TYPE_2)){
			StringValues::STONE_SLAB_TYPE_2_MOSSY_COBBLESTONE => VanillaBlocks::MOSSY_COBBLESTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PRISMARINE_BRICK => VanillaBlocks::PRISMARINE_BRICKS_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PRISMARINE_DARK => VanillaBlocks::DARK_PRISMARINE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PRISMARINE_ROUGH => VanillaBlocks::PRISMARINE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PURPUR => VanillaBlocks::PURPUR_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_RED_NETHER_BRICK => VanillaBlocks::RED_NETHER_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_RED_SANDSTONE => VanillaBlocks::RED_SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_SMOOTH_SANDSTONE => VanillaBlocks::SMOOTH_SANDSTONE_SLAB(),
			default => throw $in->badValueException(BlockStateNamesR13::STONE_SLAB_TYPE_2, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	private function mapStoneSlab3Type(BlockStateReader $in) : Slab{
		// * stone_slab_type_3 (StringTag) = andesite, diorite, end_stone_brick, granite, polished_andesite, polished_diorite, polished_granite, smooth_red_sandstone
		return match($type = $in->readString(BlockStateNamesR13::STONE_SLAB_TYPE_3)){
			StringValues::STONE_SLAB_TYPE_3_ANDESITE => VanillaBlocks::ANDESITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_DIORITE => VanillaBlocks::DIORITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_END_STONE_BRICK => VanillaBlocks::END_STONE_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_GRANITE => VanillaBlocks::GRANITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_POLISHED_ANDESITE => VanillaBlocks::POLISHED_ANDESITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_POLISHED_DIORITE => VanillaBlocks::POLISHED_DIORITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_POLISHED_GRANITE => VanillaBlocks::POLISHED_GRANITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_SMOOTH_RED_SANDSTONE => VanillaBlocks::SMOOTH_RED_SANDSTONE_SLAB(),
			default => throw $in->badValueException(BlockStateNamesR13::STONE_SLAB_TYPE_3, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	private function mapStoneSlab4Type(BlockStateReader $in) : Slab{
		// * stone_slab_type_4 (StringTag) = cut_red_sandstone, cut_sandstone, mossy_stone_brick, smooth_quartz, stone
		return match($type = $in->readString(BlockStateNamesR13::STONE_SLAB_TYPE_4)){
			StringValues::STONE_SLAB_TYPE_4_CUT_RED_SANDSTONE => VanillaBlocks::CUT_RED_SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_CUT_SANDSTONE => VanillaBlocks::CUT_SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_MOSSY_STONE_BRICK => VanillaBlocks::MOSSY_STONE_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_SMOOTH_QUARTZ => VanillaBlocks::SMOOTH_QUARTZ_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_STONE => VanillaBlocks::STONE_SLAB(),
			default => throw $in->badValueException(BlockStateNamesR13::STONE_SLAB_TYPE_4, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	private function mapWoodenSlabType(BlockStateReader $in) : Slab{
		// * wood_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
		return match($type = $in->readString(BlockStateNamesR13::WOOD_TYPE)){
			StringValues::WOOD_TYPE_ACACIA => VanillaBlocks::ACACIA_SLAB(),
			StringValues::WOOD_TYPE_BIRCH => VanillaBlocks::BIRCH_SLAB(),
			StringValues::WOOD_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_SLAB(),
			StringValues::WOOD_TYPE_JUNGLE => VanillaBlocks::JUNGLE_SLAB(),
			StringValues::WOOD_TYPE_OAK => VanillaBlocks::OAK_SLAB(),
			StringValues::WOOD_TYPE_SPRUCE => VanillaBlocks::SPRUCE_SLAB(),
			default => throw $in->badValueException(BlockStateNamesR13::WOOD_TYPE, $type),
		};
	}

	public function __construct(){
		$this->mapVanilla("acacia_button", fn(BlockStateReader $in) => $this->decodeButton(VanillaBlocks::ACACIA_BUTTON(), $in));
		$this->mapVanilla("acacia_door", fn(BlockStateReader $in) => $this->decodeDoor(VanillaBlocks::ACACIA_DOOR(), $in));
		$this->mapVanilla("acacia_fence_gate", fn(BlockStateReader $in) => $this->decodeFenceGate(VanillaBlocks::ACACIA_FENCE_GATE(), $in));
		$this->mapVanilla("acacia_pressure_plate", fn(BlockStateReader $in) => $this->decodeSimplePressurePlate(VanillaBlocks::ACACIA_PRESSURE_PLATE(), $in));
		$this->mapVanilla("acacia_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::ACACIA_STAIRS(), $in));
		$this->mapVanilla("acacia_standing_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::ACACIA_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNamesR13::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapVanilla("acacia_trapdoor", fn(BlockStateReader $in) => $this->decodeTrapdoor(VanillaBlocks::ACACIA_TRAPDOOR(), $in));
		$this->mapVanilla("acacia_wall_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::ACACIA_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("activator_rail", function(BlockStateReader $in) : Block{
			return VanillaBlocks::ACTIVATOR_RAIL()
				->setPowered($in->readBool(BlockStateNamesR13::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(BlockStateNamesR13::RAIL_DIRECTION, 0, 5));
		});
		$this->mapVanilla("air", fn() => VanillaBlocks::AIR());
		$this->mapVanilla("andesite_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::ANDESITE_STAIRS(), $in));
		$this->mapVanilla("anvil", function(BlockStateReader $in) : Block{
			return VanillaBlocks::ANVIL()
				->setDamage(match($value = $in->readString(BlockStateNamesR13::DAMAGE)){
					StringValues::DAMAGE_UNDAMAGED => 0,
					StringValues::DAMAGE_SLIGHTLY_DAMAGED => 1,
					StringValues::DAMAGE_VERY_DAMAGED => 2,
					StringValues::DAMAGE_BROKEN => 0,
					default => throw $in->badValueException(BlockStateNamesR13::DAMAGE, $value),
				})
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->mapVanilla("bamboo", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BAMBOO()
				->setLeafSize(match($value = $in->readString(BlockStateNamesR13::BAMBOO_LEAF_SIZE)){
					StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES => Bamboo::NO_LEAVES,
					StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES => Bamboo::SMALL_LEAVES,
					StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES => Bamboo::LARGE_LEAVES,
					default => throw $in->badValueException(BlockStateNamesR13::BAMBOO_LEAF_SIZE, $value),
				})
				->setReady($in->readBool(BlockStateNamesR13::AGE_BIT))
				->setThick(match($value = $in->readString(BlockStateNamesR13::BAMBOO_STALK_THICKNESS)){
					StringValues::BAMBOO_STALK_THICKNESS_THIN => false,
					StringValues::BAMBOO_STALK_THICKNESS_THICK => true,
					default => throw $in->badValueException(BlockStateNamesR13::BAMBOO_STALK_THICKNESS, $value),
				});
		});
		$this->mapVanilla("bamboo_sapling", function(BlockStateReader $in) : Block{
			//TODO: sapling_type intentionally ignored (its presence is a bug)
			return VanillaBlocks::BAMBOO_SAPLING()->setReady($in->readBool(BlockStateNamesR13::AGE_BIT));
		});
		$this->mapVanilla("barrel", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BARREL()
				->setFacing($in->readFacingDirection())
				->setOpen($in->readBool(BlockStateNamesR13::OPEN_BIT));
		});
		$this->mapVanilla("barrier", fn() => VanillaBlocks::BARRIER());
		$this->mapVanilla("beacon", fn() => VanillaBlocks::BEACON());
		$this->mapVanilla("bed", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BED()
				->setFacing($in->readLegacyHorizontalFacing())
				->setHead($in->readBool(BlockStateNamesR13::HEAD_PIECE_BIT))
				->setOccupied($in->readBool(BlockStateNamesR13::OCCUPIED_BIT));
		});
		$this->mapVanilla("bedrock", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BEDROCK()
				->setBurnsForever($in->readBool(BlockStateNamesR13::INFINIBURN_BIT));
		});
		$this->mapVanilla("beetroot", fn(BlockStateReader $in) => $this->decodeCrops(VanillaBlocks::BEETROOTS(), $in));
		$this->mapVanilla("bell", function(BlockStateReader $in) : Block{
			//TODO: ignored toggle_bit (appears to be internally used in MCPE only, useless for us)
			return VanillaBlocks::BELL()
				->setFacing($in->readLegacyHorizontalFacing())
				->setAttachmentType($in->readBellAttachmentType());
		});
		$this->mapVanilla("birch_button", fn(BlockStateReader $in) => $this->decodeButton(VanillaBlocks::BIRCH_BUTTON(), $in));
		$this->mapVanilla("birch_door", fn(BlockStateReader $in) => $this->decodeDoor(VanillaBlocks::BIRCH_DOOR(), $in));
		$this->mapVanilla("birch_fence_gate", fn(BlockStateReader $in) => $this->decodeFenceGate(VanillaBlocks::BIRCH_FENCE_GATE(), $in));
		$this->mapVanilla("birch_pressure_plate", fn(BlockStateReader $in) => $this->decodeSimplePressurePlate(VanillaBlocks::BIRCH_PRESSURE_PLATE(), $in));
		$this->mapVanilla("birch_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::BIRCH_STAIRS(), $in));
		$this->mapVanilla("birch_standing_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BIRCH_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNamesR13::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapVanilla("birch_trapdoor", fn(BlockStateReader $in) => $this->decodeTrapdoor(VanillaBlocks::BIRCH_TRAPDOOR(), $in));
		$this->mapVanilla("birch_wall_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BIRCH_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("black_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::BLACK_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("blast_furnace", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->mapVanilla("blue_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::BLUE_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("blue_ice", fn() => VanillaBlocks::BLUE_ICE());
		$this->mapVanilla("bone_block", function(BlockStateReader $in) : Block{
			//TODO: intentionally ignored "deprecated" blockstate (useless)
			return VanillaBlocks::BONE_BLOCK()->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("bookshelf", fn() => VanillaBlocks::BOOKSHELF());
		$this->mapVanilla("brewing_stand", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BREWING_STAND()
				->setSlot(BrewingStandSlot::EAST(), $in->readBool(BlockStateNamesR13::BREWING_STAND_SLOT_A_BIT))
				->setSlot(BrewingStandSlot::NORTHWEST(), $in->readBool(BlockStateNamesR13::BREWING_STAND_SLOT_B_BIT))
				->setSlot(BrewingStandSlot::SOUTHWEST(), $in->readBool(BlockStateNamesR13::BREWING_STAND_SLOT_C_BIT));
		});
		$this->mapVanilla("brick_block", fn() => VanillaBlocks::BRICKS());
		$this->mapVanilla("brick_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::BRICK_STAIRS(), $in));
		$this->mapVanilla("brown_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::BROWN_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("brown_mushroom", fn() => VanillaBlocks::BROWN_MUSHROOM());
		$this->mapVanilla("brown_mushroom_block", fn(BlockStateReader $in) => $this->decodeMushroomBlock(VanillaBlocks::BROWN_MUSHROOM_BLOCK(), $in));
		$this->mapVanilla("cactus", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CACTUS()
				->setAge($in->readBoundedInt(BlockStateNamesR13::AGE, 0, 15));
		});
		$this->mapVanilla("cake", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CAKE()
				->setBites($in->readBoundedInt(BlockStateNamesR13::BITE_COUNTER, 0, 6));
		});
		$this->mapVanilla("carpet", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CARPET()
				->setColor($in->readColor());
		});
		$this->mapVanilla("carrots", fn(BlockStateReader $in) => $this->decodeCrops(VanillaBlocks::CARROTS(), $in));
		$this->mapVanilla("carved_pumpkin", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CARVED_PUMPKIN()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->mapVanilla("chemical_heat", fn() => VanillaBlocks::CHEMICAL_HEAT());
		$this->mapVanilla("chemistry_table", function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNamesR13::CHEMISTRY_TABLE_TYPE)){
				StringValues::CHEMISTRY_TABLE_TYPE_COMPOUND_CREATOR => VanillaBlocks::COMPOUND_CREATOR(),
				StringValues::CHEMISTRY_TABLE_TYPE_ELEMENT_CONSTRUCTOR => VanillaBlocks::ELEMENT_CONSTRUCTOR(),
				StringValues::CHEMISTRY_TABLE_TYPE_LAB_TABLE => VanillaBlocks::LAB_TABLE(),
				StringValues::CHEMISTRY_TABLE_TYPE_MATERIAL_REDUCER => VanillaBlocks::MATERIAL_REDUCER(),
				default => throw $in->badValueException(BlockStateNamesR13::CHEMISTRY_TABLE_TYPE, $type),
			})->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->mapVanilla("chest", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("clay", fn() => VanillaBlocks::CLAY());
		$this->mapVanilla("coal_block", fn() => VanillaBlocks::COAL());
		$this->mapVanilla("coal_ore", fn() => VanillaBlocks::COAL_ORE());
		$this->mapVanilla("cobblestone", fn() => VanillaBlocks::COBBLESTONE());
		$this->mapVanilla("cobblestone_wall", fn(BlockStateReader $in) => $this->decodeWall(VanillaBlocks::COBBLESTONE_WALL(), $in));
		$this->mapVanilla("cocoa", function(BlockStateReader $in) : Block{
			return VanillaBlocks::COCOA_POD()
				->setAge($in->readBoundedInt(BlockStateNamesR13::AGE, 0, 2))
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->mapVanilla("colored_torch_bp", function(BlockStateReader $in) : Block{
			return $in->readBool(BlockStateNamesR13::COLOR_BIT) ?
				VanillaBlocks::PURPLE_TORCH()->setFacing($in->readTorchFacing()) :
				VanillaBlocks::BLUE_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->mapVanilla("colored_torch_rg", function(BlockStateReader $in) : Block{
			return $in->readBool(BlockStateNamesR13::COLOR_BIT) ?
				VanillaBlocks::GREEN_TORCH()->setFacing($in->readTorchFacing()) :
				VanillaBlocks::RED_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->mapVanilla("concrete", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CONCRETE()
				->setColor($in->readColor());
		});
		$this->mapVanilla("concretePowder", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CONCRETE_POWDER()
				->setColor($in->readColor());
		});
		$this->mapVanilla("coral", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CORAL()
				->setCoralType($in->readCoralType())
				->setDead($in->readBool(BlockStateNamesR13::DEAD_BIT));
		});
		$this->mapVanilla("coral_block", function(BlockStateReader $in) : Block{
			return VanillaBlocks::CORAL_BLOCK()
				->setCoralType($in->readCoralType())
				->setDead($in->readBool(BlockStateNamesR13::DEAD_BIT));
		});
		$this->mapVanilla("coral_fan", fn(BlockStateReader $in) => $this->decodeFloorCoralFan($in)->setDead(false));
		$this->mapVanilla("coral_fan_dead", fn(BlockStateReader $in) => $this->decodeFloorCoralFan($in)->setDead(true));
		$this->mapVanilla("coral_fan_hang", function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_CORAL_FAN()
				->setCoralType($in->readBool(BlockStateNamesR13::CORAL_HANG_TYPE_BIT) ? CoralType::BRAIN() : CoralType::TUBE())
				->setDead($in->readBool(BlockStateNamesR13::DEAD_BIT))
				->setFacing($in->readCoralFacing());
		});
		$this->mapVanilla("coral_fan_hang2", function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_CORAL_FAN()
				->setCoralType($in->readBool(BlockStateNamesR13::CORAL_HANG_TYPE_BIT) ? CoralType::FIRE() : CoralType::BUBBLE())
				->setDead($in->readBool(BlockStateNamesR13::DEAD_BIT))
				->setFacing($in->readCoralFacing());
		});
		$this->mapVanilla("coral_fan_hang3", function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_CORAL_FAN()
				->setCoralType(CoralType::HORN())
				->setDead($in->readBool(BlockStateNamesR13::DEAD_BIT))
				->setFacing($in->readCoralFacing());
		});
		$this->mapVanilla("crafting_table", fn() => VanillaBlocks::CRAFTING_TABLE());
		$this->mapVanilla("cyan_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::CYAN_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("dark_oak_button", fn(BlockStateReader $in) => $this->decodeButton(VanillaBlocks::DARK_OAK_BUTTON(), $in));
		$this->mapVanilla("dark_oak_door", fn(BlockStateReader $in) => $this->decodeDoor(VanillaBlocks::DARK_OAK_DOOR(), $in));
		$this->mapVanilla("dark_oak_fence_gate", fn(BlockStateReader $in) => $this->decodeFenceGate(VanillaBlocks::DARK_OAK_FENCE_GATE(), $in));
		$this->mapVanilla("dark_oak_pressure_plate", fn(BlockStateReader $in) => $this->decodeSimplePressurePlate(VanillaBlocks::DARK_OAK_PRESSURE_PLATE(), $in));
		$this->mapVanilla("dark_oak_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::DARK_OAK_STAIRS(), $in));
		$this->mapVanilla("dark_oak_trapdoor", fn(BlockStateReader $in) => $this->decodeTrapdoor(VanillaBlocks::DARK_OAK_TRAPDOOR(), $in));
		$this->mapVanilla("dark_prismarine_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::DARK_PRISMARINE_STAIRS(), $in));
		$this->mapVanilla("darkoak_standing_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::DARK_OAK_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNamesR13::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapVanilla("darkoak_wall_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::DARK_OAK_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("daylight_detector", function(BlockStateReader $in) : Block{
			return VanillaBlocks::DAYLIGHT_SENSOR()
				->setInverted(false)
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNamesR13::REDSTONE_SIGNAL, 0, 15));
		});
		$this->mapVanilla("daylight_detector_inverted", function(BlockStateReader $in) : Block{
			return VanillaBlocks::DAYLIGHT_SENSOR()
				->setInverted(true)
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNamesR13::REDSTONE_SIGNAL, 0, 15));
		});
		$this->mapVanilla("deadbush", fn() => VanillaBlocks::DEAD_BUSH());
		$this->mapVanilla("detector_rail", function(BlockStateReader $in) : Block{
			return VanillaBlocks::DETECTOR_RAIL()
				->setActivated($in->readBool(BlockStateNamesR13::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(BlockStateNamesR13::RAIL_DIRECTION, 0, 5));
		});
		$this->mapVanilla("diamond_block", fn() => VanillaBlocks::DIAMOND());
		$this->mapVanilla("diamond_ore", fn() => VanillaBlocks::DIAMOND_ORE());
		$this->mapVanilla("diorite_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::DIORITE_STAIRS(), $in));
		$this->mapVanilla("dirt", function(BlockStateReader $in) : Block{
			return VanillaBlocks::DIRT()
				->setCoarse(match($value = $in->readString(BlockStateNamesR13::DIRT_TYPE)){
					StringValues::DIRT_TYPE_NORMAL => false,
					StringValues::DIRT_TYPE_COARSE => true,
					default => throw $in->badValueException(BlockStateNamesR13::DIRT_TYPE, $value),
				});
		});
		$this->mapVanilla("double_plant", function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNamesR13::DOUBLE_PLANT_TYPE)){
				StringValues::DOUBLE_PLANT_TYPE_FERN => VanillaBlocks::LARGE_FERN(),
				StringValues::DOUBLE_PLANT_TYPE_GRASS => VanillaBlocks::DOUBLE_TALLGRASS(),
				StringValues::DOUBLE_PLANT_TYPE_PAEONIA => VanillaBlocks::PEONY(),
				StringValues::DOUBLE_PLANT_TYPE_ROSE => VanillaBlocks::ROSE_BUSH(),
				StringValues::DOUBLE_PLANT_TYPE_SUNFLOWER => VanillaBlocks::SUNFLOWER(),
				StringValues::DOUBLE_PLANT_TYPE_SYRINGA => VanillaBlocks::LILAC(),
				default => throw $in->badValueException(BlockStateNamesR13::DOUBLE_PLANT_TYPE, $type),
			})->setTop($in->readBool(BlockStateNamesR13::UPPER_BLOCK_BIT));
		});
		$this->mapVanilla("double_stone_slab", function(BlockStateReader $in) : Block{
			return $this->mapStoneSlab1Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->mapVanilla("double_stone_slab2", function(BlockStateReader $in) : Block{
			return $this->mapStoneSlab2Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->mapVanilla("double_stone_slab3", function(BlockStateReader $in) : Block{
			return $this->mapStoneSlab3Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->mapVanilla("double_stone_slab4", function(BlockStateReader $in) : Block{
			return $this->mapStoneSlab4Type($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->mapVanilla("double_wooden_slab", function(BlockStateReader $in) : Block{
			return $this->mapWoodenSlabType($in)->setSlabType(SlabType::DOUBLE());
		});
		$this->mapVanilla("dragon_egg", fn() => VanillaBlocks::DRAGON_EGG());
		$this->mapVanilla("dried_kelp_block", fn() => VanillaBlocks::DRIED_KELP());
		$this->mapVanilla("element_0", fn() => VanillaBlocks::ELEMENT_ZERO());
		$this->mapVanilla("element_1", fn() => VanillaBlocks::ELEMENT_HYDROGEN());
		$this->mapVanilla("element_10", fn() => VanillaBlocks::ELEMENT_NEON());
		$this->mapVanilla("element_100", fn() => VanillaBlocks::ELEMENT_FERMIUM());
		$this->mapVanilla("element_101", fn() => VanillaBlocks::ELEMENT_MENDELEVIUM());
		$this->mapVanilla("element_102", fn() => VanillaBlocks::ELEMENT_NOBELIUM());
		$this->mapVanilla("element_103", fn() => VanillaBlocks::ELEMENT_LAWRENCIUM());
		$this->mapVanilla("element_104", fn() => VanillaBlocks::ELEMENT_RUTHERFORDIUM());
		$this->mapVanilla("element_105", fn() => VanillaBlocks::ELEMENT_DUBNIUM());
		$this->mapVanilla("element_106", fn() => VanillaBlocks::ELEMENT_SEABORGIUM());
		$this->mapVanilla("element_107", fn() => VanillaBlocks::ELEMENT_BOHRIUM());
		$this->mapVanilla("element_108", fn() => VanillaBlocks::ELEMENT_HASSIUM());
		$this->mapVanilla("element_109", fn() => VanillaBlocks::ELEMENT_MEITNERIUM());
		$this->mapVanilla("element_11", fn() => VanillaBlocks::ELEMENT_SODIUM());
		$this->mapVanilla("element_110", fn() => VanillaBlocks::ELEMENT_DARMSTADTIUM());
		$this->mapVanilla("element_111", fn() => VanillaBlocks::ELEMENT_ROENTGENIUM());
		$this->mapVanilla("element_112", fn() => VanillaBlocks::ELEMENT_COPERNICIUM());
		$this->mapVanilla("element_113", fn() => VanillaBlocks::ELEMENT_NIHONIUM());
		$this->mapVanilla("element_114", fn() => VanillaBlocks::ELEMENT_FLEROVIUM());
		$this->mapVanilla("element_115", fn() => VanillaBlocks::ELEMENT_MOSCOVIUM());
		$this->mapVanilla("element_116", fn() => VanillaBlocks::ELEMENT_LIVERMORIUM());
		$this->mapVanilla("element_117", fn() => VanillaBlocks::ELEMENT_TENNESSINE());
		$this->mapVanilla("element_118", fn() => VanillaBlocks::ELEMENT_OGANESSON());
		$this->mapVanilla("element_12", fn() => VanillaBlocks::ELEMENT_MAGNESIUM());
		$this->mapVanilla("element_13", fn() => VanillaBlocks::ELEMENT_ALUMINUM());
		$this->mapVanilla("element_14", fn() => VanillaBlocks::ELEMENT_SILICON());
		$this->mapVanilla("element_15", fn() => VanillaBlocks::ELEMENT_PHOSPHORUS());
		$this->mapVanilla("element_16", fn() => VanillaBlocks::ELEMENT_SULFUR());
		$this->mapVanilla("element_17", fn() => VanillaBlocks::ELEMENT_CHLORINE());
		$this->mapVanilla("element_18", fn() => VanillaBlocks::ELEMENT_ARGON());
		$this->mapVanilla("element_19", fn() => VanillaBlocks::ELEMENT_POTASSIUM());
		$this->mapVanilla("element_2", fn() => VanillaBlocks::ELEMENT_HELIUM());
		$this->mapVanilla("element_20", fn() => VanillaBlocks::ELEMENT_CALCIUM());
		$this->mapVanilla("element_21", fn() => VanillaBlocks::ELEMENT_SCANDIUM());
		$this->mapVanilla("element_22", fn() => VanillaBlocks::ELEMENT_TITANIUM());
		$this->mapVanilla("element_23", fn() => VanillaBlocks::ELEMENT_VANADIUM());
		$this->mapVanilla("element_24", fn() => VanillaBlocks::ELEMENT_CHROMIUM());
		$this->mapVanilla("element_25", fn() => VanillaBlocks::ELEMENT_MANGANESE());
		$this->mapVanilla("element_26", fn() => VanillaBlocks::ELEMENT_IRON());
		$this->mapVanilla("element_27", fn() => VanillaBlocks::ELEMENT_COBALT());
		$this->mapVanilla("element_28", fn() => VanillaBlocks::ELEMENT_NICKEL());
		$this->mapVanilla("element_29", fn() => VanillaBlocks::ELEMENT_COPPER());
		$this->mapVanilla("element_3", fn() => VanillaBlocks::ELEMENT_LITHIUM());
		$this->mapVanilla("element_30", fn() => VanillaBlocks::ELEMENT_ZINC());
		$this->mapVanilla("element_31", fn() => VanillaBlocks::ELEMENT_GALLIUM());
		$this->mapVanilla("element_32", fn() => VanillaBlocks::ELEMENT_GERMANIUM());
		$this->mapVanilla("element_33", fn() => VanillaBlocks::ELEMENT_ARSENIC());
		$this->mapVanilla("element_34", fn() => VanillaBlocks::ELEMENT_SELENIUM());
		$this->mapVanilla("element_35", fn() => VanillaBlocks::ELEMENT_BROMINE());
		$this->mapVanilla("element_36", fn() => VanillaBlocks::ELEMENT_KRYPTON());
		$this->mapVanilla("element_37", fn() => VanillaBlocks::ELEMENT_RUBIDIUM());
		$this->mapVanilla("element_38", fn() => VanillaBlocks::ELEMENT_STRONTIUM());
		$this->mapVanilla("element_39", fn() => VanillaBlocks::ELEMENT_YTTRIUM());
		$this->mapVanilla("element_4", fn() => VanillaBlocks::ELEMENT_BERYLLIUM());
		$this->mapVanilla("element_40", fn() => VanillaBlocks::ELEMENT_ZIRCONIUM());
		$this->mapVanilla("element_41", fn() => VanillaBlocks::ELEMENT_NIOBIUM());
		$this->mapVanilla("element_42", fn() => VanillaBlocks::ELEMENT_MOLYBDENUM());
		$this->mapVanilla("element_43", fn() => VanillaBlocks::ELEMENT_TECHNETIUM());
		$this->mapVanilla("element_44", fn() => VanillaBlocks::ELEMENT_RUTHENIUM());
		$this->mapVanilla("element_45", fn() => VanillaBlocks::ELEMENT_RHODIUM());
		$this->mapVanilla("element_46", fn() => VanillaBlocks::ELEMENT_PALLADIUM());
		$this->mapVanilla("element_47", fn() => VanillaBlocks::ELEMENT_SILVER());
		$this->mapVanilla("element_48", fn() => VanillaBlocks::ELEMENT_CADMIUM());
		$this->mapVanilla("element_49", fn() => VanillaBlocks::ELEMENT_INDIUM());
		$this->mapVanilla("element_5", fn() => VanillaBlocks::ELEMENT_BORON());
		$this->mapVanilla("element_50", fn() => VanillaBlocks::ELEMENT_TIN());
		$this->mapVanilla("element_51", fn() => VanillaBlocks::ELEMENT_ANTIMONY());
		$this->mapVanilla("element_52", fn() => VanillaBlocks::ELEMENT_TELLURIUM());
		$this->mapVanilla("element_53", fn() => VanillaBlocks::ELEMENT_IODINE());
		$this->mapVanilla("element_54", fn() => VanillaBlocks::ELEMENT_XENON());
		$this->mapVanilla("element_55", fn() => VanillaBlocks::ELEMENT_CESIUM());
		$this->mapVanilla("element_56", fn() => VanillaBlocks::ELEMENT_BARIUM());
		$this->mapVanilla("element_57", fn() => VanillaBlocks::ELEMENT_LANTHANUM());
		$this->mapVanilla("element_58", fn() => VanillaBlocks::ELEMENT_CERIUM());
		$this->mapVanilla("element_59", fn() => VanillaBlocks::ELEMENT_PRASEODYMIUM());
		$this->mapVanilla("element_6", fn() => VanillaBlocks::ELEMENT_CARBON());
		$this->mapVanilla("element_60", fn() => VanillaBlocks::ELEMENT_NEODYMIUM());
		$this->mapVanilla("element_61", fn() => VanillaBlocks::ELEMENT_PROMETHIUM());
		$this->mapVanilla("element_62", fn() => VanillaBlocks::ELEMENT_SAMARIUM());
		$this->mapVanilla("element_63", fn() => VanillaBlocks::ELEMENT_EUROPIUM());
		$this->mapVanilla("element_64", fn() => VanillaBlocks::ELEMENT_GADOLINIUM());
		$this->mapVanilla("element_65", fn() => VanillaBlocks::ELEMENT_TERBIUM());
		$this->mapVanilla("element_66", fn() => VanillaBlocks::ELEMENT_DYSPROSIUM());
		$this->mapVanilla("element_67", fn() => VanillaBlocks::ELEMENT_HOLMIUM());
		$this->mapVanilla("element_68", fn() => VanillaBlocks::ELEMENT_ERBIUM());
		$this->mapVanilla("element_69", fn() => VanillaBlocks::ELEMENT_THULIUM());
		$this->mapVanilla("element_7", fn() => VanillaBlocks::ELEMENT_NITROGEN());
		$this->mapVanilla("element_70", fn() => VanillaBlocks::ELEMENT_YTTERBIUM());
		$this->mapVanilla("element_71", fn() => VanillaBlocks::ELEMENT_LUTETIUM());
		$this->mapVanilla("element_72", fn() => VanillaBlocks::ELEMENT_HAFNIUM());
		$this->mapVanilla("element_73", fn() => VanillaBlocks::ELEMENT_TANTALUM());
		$this->mapVanilla("element_74", fn() => VanillaBlocks::ELEMENT_TUNGSTEN());
		$this->mapVanilla("element_75", fn() => VanillaBlocks::ELEMENT_RHENIUM());
		$this->mapVanilla("element_76", fn() => VanillaBlocks::ELEMENT_OSMIUM());
		$this->mapVanilla("element_77", fn() => VanillaBlocks::ELEMENT_IRIDIUM());
		$this->mapVanilla("element_78", fn() => VanillaBlocks::ELEMENT_PLATINUM());
		$this->mapVanilla("element_79", fn() => VanillaBlocks::ELEMENT_GOLD());
		$this->mapVanilla("element_8", fn() => VanillaBlocks::ELEMENT_OXYGEN());
		$this->mapVanilla("element_80", fn() => VanillaBlocks::ELEMENT_MERCURY());
		$this->mapVanilla("element_81", fn() => VanillaBlocks::ELEMENT_THALLIUM());
		$this->mapVanilla("element_82", fn() => VanillaBlocks::ELEMENT_LEAD());
		$this->mapVanilla("element_83", fn() => VanillaBlocks::ELEMENT_BISMUTH());
		$this->mapVanilla("element_84", fn() => VanillaBlocks::ELEMENT_POLONIUM());
		$this->mapVanilla("element_85", fn() => VanillaBlocks::ELEMENT_ASTATINE());
		$this->mapVanilla("element_86", fn() => VanillaBlocks::ELEMENT_RADON());
		$this->mapVanilla("element_87", fn() => VanillaBlocks::ELEMENT_FRANCIUM());
		$this->mapVanilla("element_88", fn() => VanillaBlocks::ELEMENT_RADIUM());
		$this->mapVanilla("element_89", fn() => VanillaBlocks::ELEMENT_ACTINIUM());
		$this->mapVanilla("element_9", fn() => VanillaBlocks::ELEMENT_FLUORINE());
		$this->mapVanilla("element_90", fn() => VanillaBlocks::ELEMENT_THORIUM());
		$this->mapVanilla("element_91", fn() => VanillaBlocks::ELEMENT_PROTACTINIUM());
		$this->mapVanilla("element_92", fn() => VanillaBlocks::ELEMENT_URANIUM());
		$this->mapVanilla("element_93", fn() => VanillaBlocks::ELEMENT_NEPTUNIUM());
		$this->mapVanilla("element_94", fn() => VanillaBlocks::ELEMENT_PLUTONIUM());
		$this->mapVanilla("element_95", fn() => VanillaBlocks::ELEMENT_AMERICIUM());
		$this->mapVanilla("element_96", fn() => VanillaBlocks::ELEMENT_CURIUM());
		$this->mapVanilla("element_97", fn() => VanillaBlocks::ELEMENT_BERKELIUM());
		$this->mapVanilla("element_98", fn() => VanillaBlocks::ELEMENT_CALIFORNIUM());
		$this->mapVanilla("element_99", fn() => VanillaBlocks::ELEMENT_EINSTEINIUM());
		$this->mapVanilla("emerald_block", fn() => VanillaBlocks::EMERALD());
		$this->mapVanilla("emerald_ore", fn() => VanillaBlocks::EMERALD_ORE());
		$this->mapVanilla("enchanting_table", fn() => VanillaBlocks::ENCHANTING_TABLE());
		$this->mapVanilla("end_brick_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::END_STONE_BRICK_STAIRS(), $in));
		$this->mapVanilla("end_bricks", fn() => VanillaBlocks::END_STONE_BRICKS());
		$this->mapVanilla("end_portal_frame", function(BlockStateReader $in) : Block{
			return VanillaBlocks::END_PORTAL_FRAME()
				->setEye($in->readBool(BlockStateNamesR13::END_PORTAL_EYE_BIT))
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->mapVanilla("end_rod", function(BlockStateReader $in) : Block{
			return VanillaBlocks::END_ROD()
				->setFacing($in->readFacingDirection());
		});
		$this->mapVanilla("end_stone", fn() => VanillaBlocks::END_STONE());
		$this->mapVanilla("ender_chest", function(BlockStateReader $in) : Block{
			return VanillaBlocks::ENDER_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("farmland", function(BlockStateReader $in) : Block{
			return VanillaBlocks::FARMLAND()
				->setWetness($in->readBoundedInt(BlockStateNamesR13::MOISTURIZED_AMOUNT, 0, 7));
		});
		$this->mapVanilla("fence", function(BlockStateReader $in) : Block{
			return match($woodName = $in->readString(BlockStateNamesR13::WOOD_TYPE)){
				StringValues::WOOD_TYPE_OAK => VanillaBlocks::OAK_FENCE(),
				StringValues::WOOD_TYPE_SPRUCE => VanillaBlocks::SPRUCE_FENCE(),
				StringValues::WOOD_TYPE_BIRCH => VanillaBlocks::BIRCH_FENCE(),
				StringValues::WOOD_TYPE_JUNGLE => VanillaBlocks::JUNGLE_FENCE(),
				StringValues::WOOD_TYPE_ACACIA => VanillaBlocks::ACACIA_FENCE(),
				StringValues::WOOD_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_FENCE(),
				default => throw $in->badValueException(BlockStateNamesR13::WOOD_TYPE, $woodName),
			};
		});
		$this->mapVanilla("fence_gate", fn(BlockStateReader $in) => $this->decodeFenceGate(VanillaBlocks::OAK_FENCE_GATE(), $in));
		$this->mapVanilla("fire", function(BlockStateReader $in) : Block{
			return VanillaBlocks::FIRE()
				->setAge($in->readBoundedInt(BlockStateNamesR13::AGE, 0, 15));
		});
		$this->mapVanilla("flower_pot", function() : Block{
			//TODO: ignored update_bit (only useful on network to make the client actually render contents, not needed on disk)
			return VanillaBlocks::FLOWER_POT();
		});
		$this->mapVanilla("flowing_lava", fn(BlockStateReader $in) => $this->decodeFlowingLiquid(VanillaBlocks::LAVA(), $in));
		$this->mapVanilla("flowing_water", fn(BlockStateReader $in) => $this->decodeFlowingLiquid(VanillaBlocks::WATER(), $in));
		$this->mapVanilla("frame", function(BlockStateReader $in) : Block{
			//TODO: in R13 this can be any side, not just horizontal
			return VanillaBlocks::ITEM_FRAME()
				->setFacing($in->readHorizontalFacing())
				->setHasMap($in->readBool(BlockStateNamesR13::ITEM_FRAME_MAP_BIT));
		});
		$this->mapVanilla("frosted_ice", function(BlockStateReader $in) : Block{
			return VanillaBlocks::FROSTED_ICE()
				->setAge($in->readBoundedInt(BlockStateNamesR13::AGE, 0, 3));
		});
		$this->mapVanilla("furnace", function(BlockStateReader $in) : Block{
			return VanillaBlocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->mapVanilla("glass", fn() => VanillaBlocks::GLASS());
		$this->mapVanilla("glass_pane", fn() => VanillaBlocks::GLASS_PANE());
		$this->mapVanilla("glowingobsidian", fn() => VanillaBlocks::GLOWING_OBSIDIAN());
		$this->mapVanilla("glowstone", fn() => VanillaBlocks::GLOWSTONE());
		$this->mapVanilla("gold_block", fn() => VanillaBlocks::GOLD());
		$this->mapVanilla("gold_ore", fn() => VanillaBlocks::GOLD_ORE());
		$this->mapVanilla("golden_rail", function(BlockStateReader $in) : Block{
			return VanillaBlocks::POWERED_RAIL()
				->setPowered($in->readBool(BlockStateNamesR13::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(BlockStateNamesR13::RAIL_DIRECTION, 0, 5));
		});
		$this->mapVanilla("granite_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::GRANITE_STAIRS(), $in));
		$this->mapVanilla("grass", fn() => VanillaBlocks::GRASS());
		$this->mapVanilla("grass_path", fn() => VanillaBlocks::GRASS_PATH());
		$this->mapVanilla("gravel", fn() => VanillaBlocks::GRAVEL());
		$this->mapVanilla("gray_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::GRAY_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("green_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::GREEN_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("hard_glass", fn() => VanillaBlocks::HARDENED_GLASS());
		$this->mapVanilla("hard_glass_pane", fn() => VanillaBlocks::HARDENED_GLASS_PANE());
		$this->mapVanilla("hard_stained_glass", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_HARDENED_GLASS()
				->setColor($in->readColor());
		});
		$this->mapVanilla("hard_stained_glass_pane", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_HARDENED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->mapVanilla("hardened_clay", fn() => VanillaBlocks::HARDENED_CLAY());
		$this->mapVanilla("hay_block", function(BlockStateReader $in) : Block{
			//TODO: intentionally ignored "deprecated" blockstate (useless)
			return VanillaBlocks::HAY_BALE()->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("heavy_weighted_pressure_plate", function(BlockStateReader $in) : Block{
			return VanillaBlocks::WEIGHTED_PRESSURE_PLATE_HEAVY()
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNamesR13::REDSTONE_SIGNAL, 0, 15));
		});
		$this->mapVanilla("hopper", function(BlockStateReader $in) : Block{
			return VanillaBlocks::HOPPER()
				->setFacing($in->readFacingWithoutUp())
				->setPowered($in->readBool(BlockStateNamesR13::TOGGLE_BIT));
		});
		$this->mapVanilla("ice", fn() => VanillaBlocks::ICE());
		$this->mapVanilla("info_update", fn() => VanillaBlocks::INFO_UPDATE());
		$this->mapVanilla("info_update2", fn() => VanillaBlocks::INFO_UPDATE2());
		$this->mapVanilla("invisibleBedrock", fn() => VanillaBlocks::INVISIBLE_BEDROCK());
		$this->mapVanilla("iron_bars", fn() => VanillaBlocks::IRON_BARS());
		$this->mapVanilla("iron_block", fn() => VanillaBlocks::IRON());
		$this->mapVanilla("iron_door", fn(BlockStateReader $in) => $this->decodeDoor(VanillaBlocks::IRON_DOOR(), $in));
		$this->mapVanilla("iron_ore", fn() => VanillaBlocks::IRON_ORE());
		$this->mapVanilla("iron_trapdoor", fn(BlockStateReader $in) => $this->decodeTrapdoor(VanillaBlocks::IRON_TRAPDOOR(), $in));
		$this->mapVanilla("jukebox", fn() => VanillaBlocks::JUKEBOX());
		$this->mapVanilla("jungle_button", fn(BlockStateReader $in) => $this->decodeButton(VanillaBlocks::JUNGLE_BUTTON(), $in));
		$this->mapVanilla("jungle_door", fn(BlockStateReader $in) => $this->decodeDoor(VanillaBlocks::JUNGLE_DOOR(), $in));
		$this->mapVanilla("jungle_fence_gate", fn(BlockStateReader $in) => $this->decodeFenceGate(VanillaBlocks::JUNGLE_FENCE_GATE(), $in));
		$this->mapVanilla("jungle_pressure_plate", fn(BlockStateReader $in) => $this->decodeSimplePressurePlate(VanillaBlocks::JUNGLE_PRESSURE_PLATE(), $in));
		$this->mapVanilla("jungle_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::JUNGLE_STAIRS(), $in));
		$this->mapVanilla("jungle_standing_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::JUNGLE_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNamesR13::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapVanilla("jungle_trapdoor", fn(BlockStateReader $in) => $this->decodeTrapdoor(VanillaBlocks::JUNGLE_TRAPDOOR(), $in));
		$this->mapVanilla("jungle_wall_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::JUNGLE_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("ladder", function(BlockStateReader $in) : Block{
			return VanillaBlocks::LADDER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("lantern", function(BlockStateReader $in) : Block{
			return VanillaBlocks::LANTERN()
				->setHanging($in->readBool(BlockStateNamesR13::HANGING));
		});
		$this->mapVanilla("lapis_block", fn() => VanillaBlocks::LAPIS_LAZULI());
		$this->mapVanilla("lapis_ore", fn() => VanillaBlocks::LAPIS_LAZULI_ORE());
		$this->mapVanilla("lava", fn(BlockStateReader $in) => $this->decodeStillLiquid(VanillaBlocks::LAVA(), $in));
		$this->mapVanilla("leaves", function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNamesR13::OLD_LEAF_TYPE)){
					StringValues::OLD_LEAF_TYPE_BIRCH => VanillaBlocks::BIRCH_LEAVES(),
					StringValues::OLD_LEAF_TYPE_JUNGLE => VanillaBlocks::JUNGLE_LEAVES(),
					StringValues::OLD_LEAF_TYPE_OAK => VanillaBlocks::OAK_LEAVES(),
					StringValues::OLD_LEAF_TYPE_SPRUCE => VanillaBlocks::SPRUCE_LEAVES(),
					default => throw $in->badValueException(BlockStateNamesR13::OLD_LEAF_TYPE, $type),
				})
				->setNoDecay($in->readBool(BlockStateNamesR13::PERSISTENT_BIT))
				->setCheckDecay($in->readBool(BlockStateNamesR13::UPDATE_BIT));
		});
		$this->mapVanilla("leaves2", function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNamesR13::NEW_LEAF_TYPE)){
					StringValues::NEW_LEAF_TYPE_ACACIA => VanillaBlocks::ACACIA_LEAVES(),
					StringValues::NEW_LEAF_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_LEAVES(),
					default => throw $in->badValueException(BlockStateNamesR13::NEW_LEAF_TYPE, $type),
				})
				->setNoDecay($in->readBool(BlockStateNamesR13::PERSISTENT_BIT))
				->setCheckDecay($in->readBool(BlockStateNamesR13::UPDATE_BIT));
		});
		$this->mapVanilla("lever", function(BlockStateReader $in) : Block{
			return VanillaBlocks::LEVER()
				->setActivated($in->readBool(BlockStateNamesR13::OPEN_BIT))
				->setFacing(match($value = $in->readString(BlockStateNamesR13::LEVER_DIRECTION)){
					StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH => LeverFacing::DOWN_AXIS_Z(),
					StringValues::LEVER_DIRECTION_DOWN_EAST_WEST => LeverFacing::DOWN_AXIS_X(),
					StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH => LeverFacing::UP_AXIS_Z(),
					StringValues::LEVER_DIRECTION_UP_EAST_WEST => LeverFacing::UP_AXIS_X(),
					StringValues::LEVER_DIRECTION_NORTH => LeverFacing::NORTH(),
					StringValues::LEVER_DIRECTION_SOUTH => LeverFacing::SOUTH(),
					StringValues::LEVER_DIRECTION_WEST => LeverFacing::WEST(),
					StringValues::LEVER_DIRECTION_EAST => LeverFacing::EAST(),
					default => throw $in->badValueException(BlockStateNamesR13::LEVER_DIRECTION, $value),
				});
		});
		$this->mapVanilla("light_blue_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::LIGHT_BLUE_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("light_weighted_pressure_plate", function(BlockStateReader $in) : Block{
			return VanillaBlocks::WEIGHTED_PRESSURE_PLATE_LIGHT()
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNamesR13::REDSTONE_SIGNAL, 0, 15));
		});
		$this->mapVanilla("lime_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::LIME_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("lit_blast_furnace", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->mapVanilla("lit_furnace", function(BlockStateReader $in) : Block{
			return VanillaBlocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->mapVanilla("lit_pumpkin", function(BlockStateReader $in) : Block{
			return VanillaBlocks::LIT_PUMPKIN()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->mapVanilla("lit_redstone_lamp", function() : Block{
			return VanillaBlocks::REDSTONE_LAMP()
				->setPowered(true);
		});
		$this->mapVanilla("lit_redstone_ore", function() : Block{
			return VanillaBlocks::REDSTONE_ORE()
				->setLit(true);
		});
		$this->mapVanilla("lit_smoker", function(BlockStateReader $in) : Block{
			return VanillaBlocks::SMOKER()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->mapVanilla("log", function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNamesR13::OLD_LOG_TYPE)){
					StringValues::OLD_LOG_TYPE_BIRCH => VanillaBlocks::BIRCH_LOG(),
					StringValues::OLD_LOG_TYPE_JUNGLE => VanillaBlocks::JUNGLE_LOG(),
					StringValues::OLD_LOG_TYPE_OAK => VanillaBlocks::OAK_LOG(),
					StringValues::OLD_LOG_TYPE_SPRUCE => VanillaBlocks::SPRUCE_LOG(),
					default => throw $in->badValueException(BlockStateNamesR13::OLD_LOG_TYPE, $type),
				})
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("log2", function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNamesR13::NEW_LOG_TYPE)){
					StringValues::NEW_LOG_TYPE_ACACIA => VanillaBlocks::ACACIA_LOG(),
					StringValues::NEW_LOG_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_LOG(),
					default => throw $in->badValueException(BlockStateNamesR13::NEW_LOG_TYPE, $type),
				})
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("loom", function(BlockStateReader $in) : Block{
			return VanillaBlocks::LOOM()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->mapVanilla("magenta_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::MAGENTA_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("magma", fn() => VanillaBlocks::MAGMA());
		$this->mapVanilla("melon_block", fn() => VanillaBlocks::MELON());
		$this->mapVanilla("melon_stem", fn(BlockStateReader $in) => $this->decodeCrops(VanillaBlocks::MELON_STEM(), $in));
		$this->mapVanilla("mob_spawner", fn() => VanillaBlocks::MONSTER_SPAWNER());
		$this->mapVanilla("monster_egg", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::MONSTER_EGG_STONE_TYPE)){
				StringValues::MONSTER_EGG_STONE_TYPE_CHISELED_STONE_BRICK => VanillaBlocks::INFESTED_CHISELED_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_COBBLESTONE => VanillaBlocks::INFESTED_COBBLESTONE(),
				StringValues::MONSTER_EGG_STONE_TYPE_CRACKED_STONE_BRICK => VanillaBlocks::INFESTED_CRACKED_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_MOSSY_STONE_BRICK => VanillaBlocks::INFESTED_MOSSY_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_STONE => VanillaBlocks::INFESTED_STONE(),
				StringValues::MONSTER_EGG_STONE_TYPE_STONE_BRICK => VanillaBlocks::INFESTED_STONE_BRICK(),
				default => throw $in->badValueException(BlockStateNamesR13::MONSTER_EGG_STONE_TYPE, $type),
			};
		});
		$this->mapVanilla("mossy_cobblestone", fn() => VanillaBlocks::MOSSY_COBBLESTONE());
		$this->mapVanilla("mossy_cobblestone_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::MOSSY_COBBLESTONE_STAIRS(), $in));
		$this->mapVanilla("mossy_stone_brick_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::MOSSY_STONE_BRICK_STAIRS(), $in));
		$this->mapVanilla("mycelium", fn() => VanillaBlocks::MYCELIUM());
		$this->mapVanilla("nether_brick", fn() => VanillaBlocks::NETHER_BRICKS());
		$this->mapVanilla("nether_brick_fence", fn() => VanillaBlocks::NETHER_BRICK_FENCE());
		$this->mapVanilla("nether_brick_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::NETHER_BRICK_STAIRS(), $in));
		$this->mapVanilla("nether_wart", function(BlockStateReader $in) : Block{
			return VanillaBlocks::NETHER_WART()
				->setAge($in->readBoundedInt(BlockStateNamesR13::AGE, 0, 3));
		});
		$this->mapVanilla("nether_wart_block", fn() => VanillaBlocks::NETHER_WART_BLOCK());
		$this->mapVanilla("netherrack", fn() => VanillaBlocks::NETHERRACK());
		$this->mapVanilla("netherreactor", fn() => VanillaBlocks::NETHER_REACTOR_CORE());
		$this->mapVanilla("normal_stone_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::STONE_STAIRS(), $in));
		$this->mapVanilla("noteblock", fn() => VanillaBlocks::NOTE_BLOCK());
		$this->mapVanilla("oak_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::OAK_STAIRS(), $in));
		$this->mapVanilla("obsidian", fn() => VanillaBlocks::OBSIDIAN());
		$this->mapVanilla("orange_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::ORANGE_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("packed_ice", fn() => VanillaBlocks::PACKED_ICE());
		$this->mapVanilla("pink_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::PINK_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("planks", function(BlockStateReader $in) : Block{
			return match($woodName = $in->readString(BlockStateNamesR13::WOOD_TYPE)){
				StringValues::WOOD_TYPE_OAK => VanillaBlocks::OAK_PLANKS(),
				StringValues::WOOD_TYPE_SPRUCE => VanillaBlocks::SPRUCE_PLANKS(),
				StringValues::WOOD_TYPE_BIRCH => VanillaBlocks::BIRCH_PLANKS(),
				StringValues::WOOD_TYPE_JUNGLE => VanillaBlocks::JUNGLE_PLANKS(),
				StringValues::WOOD_TYPE_ACACIA => VanillaBlocks::ACACIA_PLANKS(),
				StringValues::WOOD_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_PLANKS(),
				default => throw $in->badValueException(BlockStateNamesR13::WOOD_TYPE, $woodName),
			};
		});
		$this->mapVanilla("podzol", fn() => VanillaBlocks::PODZOL());
		$this->mapVanilla("polished_andesite_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::POLISHED_ANDESITE_STAIRS(), $in));
		$this->mapVanilla("polished_diorite_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::POLISHED_DIORITE_STAIRS(), $in));
		$this->mapVanilla("polished_granite_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::POLISHED_GRANITE_STAIRS(), $in));
		$this->mapVanilla("portal", function(BlockStateReader $in) : Block{
			return VanillaBlocks::NETHER_PORTAL()
				->setAxis(match($value = $in->readString(BlockStateNamesR13::PORTAL_AXIS)){
					StringValues::PORTAL_AXIS_UNKNOWN => Axis::X,
					StringValues::PORTAL_AXIS_X => Axis::X,
					StringValues::PORTAL_AXIS_Z => Axis::Z,
					default => throw $in->badValueException(BlockStateNamesR13::PORTAL_AXIS, $value),
				});
		});
		$this->mapVanilla("potatoes", fn(BlockStateReader $in) => $this->decodeCrops(VanillaBlocks::POTATOES(), $in));
		$this->mapVanilla("powered_comparator", fn(BlockStateReader $in) => $this->decodeComparator(VanillaBlocks::REDSTONE_COMPARATOR(), $in));
		$this->mapVanilla("powered_repeater", fn(BlockStateReader $in) => $this->decodeRepeater(VanillaBlocks::REDSTONE_REPEATER(), $in)
				->setPowered(true));
		$this->mapVanilla("prismarine", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::PRISMARINE_BLOCK_TYPE)){
				StringValues::PRISMARINE_BLOCK_TYPE_BRICKS => VanillaBlocks::PRISMARINE_BRICKS(),
				StringValues::PRISMARINE_BLOCK_TYPE_DARK => VanillaBlocks::DARK_PRISMARINE(),
				StringValues::PRISMARINE_BLOCK_TYPE_DEFAULT => VanillaBlocks::PRISMARINE(),
				default => throw $in->badValueException(BlockStateNamesR13::PRISMARINE_BLOCK_TYPE, $type),
			};
		});
		$this->mapVanilla("prismarine_bricks_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::PRISMARINE_BRICKS_STAIRS(), $in));
		$this->mapVanilla("prismarine_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::PRISMARINE_STAIRS(), $in));
		$this->mapVanilla("pumpkin", function() : Block{
			//TODO: intentionally ignored "direction" property (obsolete)
			return VanillaBlocks::PUMPKIN();
		});
		$this->mapVanilla("pumpkin_stem", fn(BlockStateReader $in) => $this->decodeCrops(VanillaBlocks::PUMPKIN_STEM(), $in));
		$this->mapVanilla("purple_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::PURPLE_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("purpur_block", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::CHISEL_TYPE)){
				StringValues::CHISEL_TYPE_CHISELED, //TODO: bug in MCPE
				StringValues::CHISEL_TYPE_SMOOTH, //TODO: bug in MCPE
				StringValues::CHISEL_TYPE_DEFAULT => VanillaBlocks::PURPUR(), //TODO: axis intentionally ignored (useless)
				StringValues::CHISEL_TYPE_LINES => VanillaBlocks::PURPUR_PILLAR()->setAxis($in->readPillarAxis()),
				default => throw $in->badValueException(BlockStateNamesR13::CHISEL_TYPE, $type),
			};
		});
		$this->mapVanilla("purpur_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::PURPUR_STAIRS(), $in));
		$this->mapVanilla("quartz_block", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::CHISEL_TYPE)){
				StringValues::CHISEL_TYPE_CHISELED => VanillaBlocks::CHISELED_QUARTZ()->setAxis($in->readPillarAxis()),
				StringValues::CHISEL_TYPE_DEFAULT => VanillaBlocks::QUARTZ(), //TODO: axis intentionally ignored (useless)
				StringValues::CHISEL_TYPE_LINES => VanillaBlocks::QUARTZ_PILLAR()->setAxis($in->readPillarAxis()),
				StringValues::CHISEL_TYPE_SMOOTH => VanillaBlocks::SMOOTH_QUARTZ(), //TODO: axis intentionally ignored (useless)
				default => throw $in->badValueException(BlockStateNamesR13::CHISEL_TYPE, $type),
			};
		});
		$this->mapVanilla("quartz_ore", fn() => VanillaBlocks::NETHER_QUARTZ_ORE());
		$this->mapVanilla("quartz_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::QUARTZ_STAIRS(), $in));
		$this->mapVanilla("rail", function(BlockStateReader $in) : Block{
			return VanillaBlocks::RAIL()
				->setShape($in->readBoundedInt(BlockStateNamesR13::RAIL_DIRECTION, 0, 9));
		});
		$this->mapVanilla("red_flower", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::FLOWER_TYPE)){
				StringValues::FLOWER_TYPE_ALLIUM => VanillaBlocks::ALLIUM(),
				StringValues::FLOWER_TYPE_CORNFLOWER => VanillaBlocks::CORNFLOWER(),
				StringValues::FLOWER_TYPE_HOUSTONIA => VanillaBlocks::AZURE_BLUET(), //wtf ???
				StringValues::FLOWER_TYPE_LILY_OF_THE_VALLEY => VanillaBlocks::LILY_OF_THE_VALLEY(),
				StringValues::FLOWER_TYPE_ORCHID => VanillaBlocks::BLUE_ORCHID(),
				StringValues::FLOWER_TYPE_OXEYE => VanillaBlocks::OXEYE_DAISY(),
				StringValues::FLOWER_TYPE_POPPY => VanillaBlocks::POPPY(),
				StringValues::FLOWER_TYPE_TULIP_ORANGE => VanillaBlocks::ORANGE_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_PINK => VanillaBlocks::PINK_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_RED => VanillaBlocks::RED_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_WHITE => VanillaBlocks::WHITE_TULIP(),
				default => throw $in->badValueException(BlockStateNamesR13::FLOWER_TYPE, $type),
			};
		});
		$this->mapVanilla("red_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::RED_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("red_mushroom", fn() => VanillaBlocks::RED_MUSHROOM());
		$this->mapVanilla("red_mushroom_block", fn(BlockStateReader $in) => $this->decodeMushroomBlock(VanillaBlocks::RED_MUSHROOM_BLOCK(), $in));
		$this->mapVanilla("red_nether_brick", fn() => VanillaBlocks::RED_NETHER_BRICKS());
		$this->mapVanilla("red_nether_brick_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::RED_NETHER_BRICK_STAIRS(), $in));
		$this->mapVanilla("red_sandstone", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::SAND_STONE_TYPE)){
				StringValues::SAND_STONE_TYPE_CUT => VanillaBlocks::CUT_RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_DEFAULT => VanillaBlocks::RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_HEIROGLYPHS => VanillaBlocks::CHISELED_RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_SMOOTH => VanillaBlocks::SMOOTH_RED_SANDSTONE(),
				default => throw $in->badValueException(BlockStateNamesR13::SAND_STONE_TYPE, $type),
			};
		});
		$this->mapVanilla("red_sandstone_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::RED_SANDSTONE_STAIRS(), $in));
		$this->mapVanilla("redstone_block", fn() => VanillaBlocks::REDSTONE());
		$this->mapVanilla("redstone_lamp", function() : Block{
			return VanillaBlocks::REDSTONE_LAMP()
				->setPowered(false);
		});
		$this->mapVanilla("redstone_ore", function() : Block{
			return VanillaBlocks::REDSTONE_ORE()
				->setLit(false);
		});
		$this->mapVanilla("redstone_torch", function(BlockStateReader $in) : Block{
			return VanillaBlocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(true);
		});
		$this->mapVanilla("redstone_wire", function(BlockStateReader $in) : Block{
			return VanillaBlocks::REDSTONE_WIRE()
				->setOutputSignalStrength($in->readBoundedInt(BlockStateNamesR13::REDSTONE_SIGNAL, 0, 15));
		});
		$this->mapVanilla("reeds", function(BlockStateReader $in) : Block{
			return VanillaBlocks::SUGARCANE()
				->setAge($in->readBoundedInt(BlockStateNamesR13::AGE, 0, 15));
		});
		$this->mapVanilla("reserved6", fn() => VanillaBlocks::RESERVED6());
		$this->mapVanilla("sand", function(BlockStateReader $in) : Block{
			return match($value = $in->readString(BlockStateNamesR13::SAND_TYPE)){
				StringValues::SAND_TYPE_NORMAL => VanillaBlocks::SAND(),
				StringValues::SAND_TYPE_RED => VanillaBlocks::RED_SAND(),
				default => throw $in->badValueException(BlockStateNamesR13::SAND_TYPE, $value),
			};
		});
		$this->mapVanilla("sandstone", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::SAND_STONE_TYPE)){
				StringValues::SAND_STONE_TYPE_CUT => VanillaBlocks::CUT_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_DEFAULT => VanillaBlocks::SANDSTONE(),
				StringValues::SAND_STONE_TYPE_HEIROGLYPHS => VanillaBlocks::CHISELED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_SMOOTH => VanillaBlocks::SMOOTH_SANDSTONE(),
				default => throw $in->badValueException(BlockStateNamesR13::SAND_STONE_TYPE, $type),
			};
		});
		$this->mapVanilla("sandstone_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::SANDSTONE_STAIRS(), $in));
		$this->mapVanilla("sapling", function(BlockStateReader $in) : Block{
			return (match($type = $in->readString(BlockStateNamesR13::SAPLING_TYPE)){
					StringValues::SAPLING_TYPE_ACACIA => VanillaBlocks::ACACIA_SAPLING(),
					StringValues::SAPLING_TYPE_BIRCH => VanillaBlocks::BIRCH_SAPLING(),
					StringValues::SAPLING_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_SAPLING(),
					StringValues::SAPLING_TYPE_JUNGLE => VanillaBlocks::JUNGLE_SAPLING(),
					StringValues::SAPLING_TYPE_OAK => VanillaBlocks::OAK_SAPLING(),
					StringValues::SAPLING_TYPE_SPRUCE => VanillaBlocks::SPRUCE_SAPLING(),
					default => throw $in->badValueException(BlockStateNamesR13::SAPLING_TYPE, $type),
				})
				->setReady($in->readBool(BlockStateNamesR13::AGE_BIT));
		});
		$this->mapVanilla("seaLantern", fn() => VanillaBlocks::SEA_LANTERN());
		$this->mapVanilla("sea_pickle", function(BlockStateReader $in) : Block{
			return VanillaBlocks::SEA_PICKLE()
				->setCount($in->readBoundedInt(BlockStateNamesR13::CLUSTER_COUNT, 0, 3) + 1)
				->setUnderwater(!$in->readBool(BlockStateNamesR13::DEAD_BIT));
		});
		$this->mapVanilla("shulker_box", function(BlockStateReader $in) : Block{
			return VanillaBlocks::DYED_SHULKER_BOX()
				->setColor($in->readColor());
		});
		$this->mapVanilla("silver_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::LIGHT_GRAY_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("skull", function(BlockStateReader $in) : Block{
			return VanillaBlocks::MOB_HEAD()
				->setFacing($in->readFacingWithoutDown())
				->setNoDrops($in->readBool(BlockStateNamesR13::NO_DROP_BIT));
		});
		$this->mapVanilla("slime", fn() => VanillaBlocks::SLIME());
		$this->mapVanilla("smoker", function(BlockStateReader $in) : Block{
			return VanillaBlocks::SMOKER()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->mapVanilla("smooth_quartz_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::SMOOTH_QUARTZ_STAIRS(), $in));
		$this->mapVanilla("smooth_red_sandstone_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::SMOOTH_RED_SANDSTONE_STAIRS(), $in));
		$this->mapVanilla("smooth_sandstone_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::SMOOTH_SANDSTONE_STAIRS(), $in));
		$this->mapVanilla("smooth_stone", fn() => VanillaBlocks::SMOOTH_STONE());
		$this->mapVanilla("snow", fn() => VanillaBlocks::SNOW());
		$this->mapVanilla("snow_layer", function(BlockStateReader $in) : Block{
			//TODO: intentionally ignored covered_bit property (appears useless and we don't track it)
			return VanillaBlocks::SNOW_LAYER()->setLayers($in->readBoundedInt(BlockStateNamesR13::HEIGHT, 0, 7) + 1);
		});
		$this->mapVanilla("soul_sand", fn() => VanillaBlocks::SOUL_SAND());
		$this->mapVanilla("sponge", function(BlockStateReader $in) : Block{
			return VanillaBlocks::SPONGE()->setWet(match($type = $in->readString(BlockStateNamesR13::SPONGE_TYPE)){
				StringValues::SPONGE_TYPE_DRY => false,
				StringValues::SPONGE_TYPE_WET => true,
				default => throw $in->badValueException(BlockStateNamesR13::SPONGE_TYPE, $type),
			});
		});
		$this->mapVanilla("spruce_button", fn(BlockStateReader $in) => $this->decodeButton(VanillaBlocks::SPRUCE_BUTTON(), $in));
		$this->mapVanilla("spruce_door", fn(BlockStateReader $in) => $this->decodeDoor(VanillaBlocks::SPRUCE_DOOR(), $in));
		$this->mapVanilla("spruce_fence_gate", fn(BlockStateReader $in) => $this->decodeFenceGate(VanillaBlocks::SPRUCE_FENCE_GATE(), $in));
		$this->mapVanilla("spruce_pressure_plate", fn(BlockStateReader $in) => $this->decodeSimplePressurePlate(VanillaBlocks::SPRUCE_PRESSURE_PLATE(), $in));
		$this->mapVanilla("spruce_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::SPRUCE_STAIRS(), $in));
		$this->mapVanilla("spruce_standing_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::SPRUCE_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNamesR13::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapVanilla("spruce_trapdoor", fn(BlockStateReader $in) => $this->decodeTrapdoor(VanillaBlocks::SPRUCE_TRAPDOOR(), $in));
		$this->mapVanilla("spruce_wall_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::SPRUCE_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("stained_glass", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_GLASS()
				->setColor($in->readColor());
		});
		$this->mapVanilla("stained_glass_pane", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->mapVanilla("stained_hardened_clay", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STAINED_CLAY()
				->setColor($in->readColor());
		});
		$this->mapVanilla("standing_banner", function(BlockStateReader $in) : Block{
			return VanillaBlocks::BANNER()
				->setRotation($in->readBoundedInt(BlockStateNamesR13::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapVanilla("standing_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::OAK_SIGN()
				->setRotation($in->readBoundedInt(BlockStateNamesR13::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapVanilla("stone", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::STONE_TYPE)){
				StringValues::STONE_TYPE_ANDESITE => VanillaBlocks::ANDESITE(),
				StringValues::STONE_TYPE_ANDESITE_SMOOTH => VanillaBlocks::POLISHED_ANDESITE(),
				StringValues::STONE_TYPE_DIORITE => VanillaBlocks::DIORITE(),
				StringValues::STONE_TYPE_DIORITE_SMOOTH => VanillaBlocks::POLISHED_DIORITE(),
				StringValues::STONE_TYPE_GRANITE => VanillaBlocks::GRANITE(),
				StringValues::STONE_TYPE_GRANITE_SMOOTH => VanillaBlocks::POLISHED_GRANITE(),
				StringValues::STONE_TYPE_STONE => VanillaBlocks::STONE(),
				default => throw $in->badValueException(BlockStateNamesR13::STONE_TYPE, $type),
			};
		});
		$this->mapVanilla("stone_brick_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::STONE_BRICK_STAIRS(), $in));
		$this->mapVanilla("stone_button", fn(BlockStateReader $in) => $this->decodeButton(VanillaBlocks::STONE_BUTTON(), $in));
		$this->mapVanilla("stone_pressure_plate", fn(BlockStateReader $in) => $this->decodeSimplePressurePlate(VanillaBlocks::STONE_PRESSURE_PLATE(), $in));
		$this->mapVanilla("stone_slab", fn(BlockStateReader $in) => $this->mapStoneSlab1Type($in)->setSlabType($in->readSlabPosition()));
		$this->mapVanilla("stone_slab2", fn(BlockStateReader $in) => $this->mapStoneSlab2Type($in)->setSlabType($in->readSlabPosition()));
		$this->mapVanilla("stone_slab3", fn(BlockStateReader $in) => $this->mapStoneSlab3Type($in)->setSlabType($in->readSlabPosition()));
		$this->mapVanilla("stone_slab4", fn(BlockStateReader $in) => $this->mapStoneSlab4Type($in)->setSlabType($in->readSlabPosition()));
		$this->mapVanilla("stone_stairs", fn(BlockStateReader $in) => $this->decodeStairs(VanillaBlocks::COBBLESTONE_STAIRS(), $in));
		$this->mapVanilla("stonebrick", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::STONE_BRICK_TYPE)){
				StringValues::STONE_BRICK_TYPE_SMOOTH, //TODO: bug in vanilla
				StringValues::STONE_BRICK_TYPE_DEFAULT => VanillaBlocks::STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_CHISELED => VanillaBlocks::CHISELED_STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_CRACKED => VanillaBlocks::CRACKED_STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_MOSSY => VanillaBlocks::MOSSY_STONE_BRICKS(),
				default => throw $in->badValueException(BlockStateNamesR13::STONE_BRICK_TYPE, $type),
			};
		});
		$this->mapVanilla("stonecutter", fn() => VanillaBlocks::LEGACY_STONECUTTER());
		$this->mapVanilla("stripped_acacia_log", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_ACACIA_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("stripped_birch_log", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_BIRCH_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("stripped_dark_oak_log", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_DARK_OAK_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("stripped_jungle_log", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_JUNGLE_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("stripped_oak_log", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_OAK_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("stripped_spruce_log", function(BlockStateReader $in) : Block{
			return VanillaBlocks::STRIPPED_SPRUCE_LOG()
				->setAxis($in->readPillarAxis());
		});
		$this->mapVanilla("sweet_berry_bush", function(BlockStateReader $in) : Block{
			//berry bush only wants 0-3, but it can be bigger in MCPE due to misuse of GROWTH state which goes up to 7
			$growth = $in->readBoundedInt(BlockStateNamesR13::GROWTH, 0, 7);
			return VanillaBlocks::SWEET_BERRY_BUSH()
				->setAge(min($growth, SweetBerryBush::STAGE_MATURE));
		});
		$this->mapVanilla("tallgrass", function(BlockStateReader $in) : Block{
			return match($type = $in->readString(BlockStateNamesR13::TALL_GRASS_TYPE)){
				StringValues::TALL_GRASS_TYPE_DEFAULT, StringValues::TALL_GRASS_TYPE_SNOW, StringValues::TALL_GRASS_TYPE_TALL => VanillaBlocks::TALL_GRASS(),
				StringValues::TALL_GRASS_TYPE_FERN => VanillaBlocks::FERN(),
				default => throw $in->badValueException(BlockStateNamesR13::TALL_GRASS_TYPE, $type),
			};
		});
		$this->mapVanilla("tnt", function(BlockStateReader $in) : Block{
			return VanillaBlocks::TNT()
				->setUnstable($in->readBool(BlockStateNamesR13::EXPLODE_BIT))
				->setWorksUnderwater($in->readBool(BlockStateNamesR13::ALLOW_UNDERWATER_BIT));
		});
		$this->mapVanilla("torch", function(BlockStateReader $in) : Block{
			return VanillaBlocks::TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->mapVanilla("trapdoor", fn(BlockStateReader $in) => $this->decodeTrapdoor(VanillaBlocks::OAK_TRAPDOOR(), $in));
		$this->mapVanilla("trapped_chest", function(BlockStateReader $in) : Block{
			return VanillaBlocks::TRAPPED_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("tripWire", function(BlockStateReader $in) : Block{
			return VanillaBlocks::TRIPWIRE()
				->setConnected($in->readBool(BlockStateNamesR13::ATTACHED_BIT))
				->setDisarmed($in->readBool(BlockStateNamesR13::DISARMED_BIT))
				->setSuspended($in->readBool(BlockStateNamesR13::SUSPENDED_BIT))
				->setTriggered($in->readBool(BlockStateNamesR13::POWERED_BIT));
		});
		$this->mapVanilla("tripwire_hook", function(BlockStateReader $in) : Block{
			return VanillaBlocks::TRIPWIRE_HOOK()
				->setConnected($in->readBool(BlockStateNamesR13::ATTACHED_BIT))
				->setFacing($in->readLegacyHorizontalFacing())
				->setPowered($in->readBool(BlockStateNamesR13::POWERED_BIT));
		});
		$this->mapVanilla("underwater_torch", function(BlockStateReader $in) : Block{
			return VanillaBlocks::UNDERWATER_TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->mapVanilla("undyed_shulker_box", fn() => VanillaBlocks::SHULKER_BOX());
		$this->mapVanilla("unlit_redstone_torch", function(BlockStateReader $in) : Block{
			return VanillaBlocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(false);
		});
		$this->mapVanilla("unpowered_comparator", fn(BlockStateReader $in) => $this->decodeComparator(VanillaBlocks::REDSTONE_COMPARATOR(), $in));
		$this->mapVanilla("unpowered_repeater", fn(BlockStateReader $in) => $this->decodeRepeater(VanillaBlocks::REDSTONE_REPEATER(), $in)
				->setPowered(false));
		$this->mapVanilla("vine", function(BlockStateReader $in) : Block{
			$vineDirectionFlags = $in->readBoundedInt(BlockStateNamesR13::VINE_DIRECTION_BITS, 0, 15);
			return VanillaBlocks::VINES()
				->setFace(Facing::NORTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_NORTH) !== 0)
				->setFace(Facing::SOUTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_SOUTH) !== 0)
				->setFace(Facing::WEST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_WEST) !== 0)
				->setFace(Facing::EAST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_EAST) !== 0);
		});
		$this->mapVanilla("wall_banner", function(BlockStateReader $in) : Block{
			return VanillaBlocks::WALL_BANNER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("wall_sign", function(BlockStateReader $in) : Block{
			return VanillaBlocks::OAK_WALL_SIGN()
				->setFacing($in->readHorizontalFacing());
		});
		$this->mapVanilla("water", fn(BlockStateReader $in) => $this->decodeStillLiquid(VanillaBlocks::WATER(), $in));
		$this->mapVanilla("waterlily", fn() => VanillaBlocks::LILY_PAD());
		$this->mapVanilla("web", fn() => VanillaBlocks::COBWEB());
		$this->mapVanilla("wheat", fn(BlockStateReader $in) => $this->decodeCrops(VanillaBlocks::WHEAT(), $in));
		$this->mapVanilla("white_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::WHITE_GLAZED_TERRACOTTA(), $in));
		$this->mapVanilla("wood", function(BlockStateReader $in) : Block{
			//TODO: our impl doesn't support axis yet
			$stripped = $in->readBool(BlockStateNamesR13::STRIPPED_BIT);
			return match($woodType = $in->readString(BlockStateNamesR13::WOOD_TYPE)){
				StringValues::WOOD_TYPE_ACACIA => $stripped ? VanillaBlocks::STRIPPED_ACACIA_WOOD() : VanillaBlocks::ACACIA_WOOD(),
				StringValues::WOOD_TYPE_BIRCH => $stripped ? VanillaBlocks::STRIPPED_BIRCH_WOOD() : VanillaBlocks::BIRCH_WOOD(),
				StringValues::WOOD_TYPE_DARK_OAK => $stripped ? VanillaBlocks::STRIPPED_DARK_OAK_WOOD() : VanillaBlocks::DARK_OAK_WOOD(),
				StringValues::WOOD_TYPE_JUNGLE => $stripped ? VanillaBlocks::STRIPPED_JUNGLE_WOOD() : VanillaBlocks::JUNGLE_WOOD(),
				StringValues::WOOD_TYPE_OAK => $stripped ? VanillaBlocks::STRIPPED_OAK_WOOD() : VanillaBlocks::OAK_WOOD(),
				StringValues::WOOD_TYPE_SPRUCE => $stripped ? VanillaBlocks::STRIPPED_SPRUCE_WOOD() : VanillaBlocks::SPRUCE_WOOD(),
				default => throw $in->badValueException(BlockStateNamesR13::WOOD_TYPE, $woodType),
			};
		});
		$this->mapVanilla("wooden_button", fn(BlockStateReader $in) => $this->decodeButton(VanillaBlocks::OAK_BUTTON(), $in));
		$this->mapVanilla("wooden_door", fn(BlockStateReader $in) => $this->decodeDoor(VanillaBlocks::OAK_DOOR(), $in));
		$this->mapVanilla("wooden_pressure_plate", fn(BlockStateReader $in) => $this->decodeSimplePressurePlate(VanillaBlocks::OAK_PRESSURE_PLATE(), $in));
		$this->mapVanilla("wooden_slab", fn(BlockStateReader $in) => $this->mapWoodenSlabType($in)->setSlabType($in->readSlabPosition()));
		$this->mapVanilla("wool", function(BlockStateReader $in) : Block{
			return VanillaBlocks::WOOL()
				->setColor($in->readColor());
		});
		$this->mapVanilla("yellow_flower", fn() => VanillaBlocks::DANDELION());
		$this->mapVanilla("yellow_glazed_terracotta", fn(BlockStateReader $in) => $this->decodeGlazedTerracotta(VanillaBlocks::YELLOW_GLAZED_TERRACOTTA(), $in));
		//$this->mapVanilla("bubble_column", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * drag_down (ByteTag) = 0, 1
			 */
		//});
		//$this->mapVanilla("camera", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("campfire", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * direction (IntTag) = 0, 1, 2, 3
			 * extinguished (ByteTag) = 0, 1
			 */
		//});
		//$this->mapVanilla("cartography_table", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("cauldron", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * cauldron_liquid (StringTag) = lava, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->mapVanilla("chain_command_block", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("chorus_flower", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * age (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("chorus_plant", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("command_block", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("composter", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * composter_fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8
			 */
		//});
		//$this->mapVanilla("conduit", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("dispenser", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->mapVanilla("dropper", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * triggered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->mapVanilla("end_gateway", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("end_portal", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("fletching_table", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("grindstone", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * attachment (StringTag) = hanging, multiple, side, standing
			 * direction (IntTag) = 0, 1, 2, 3
			 */
		//});
		//$this->mapVanilla("jigsaw", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("kelp", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * age (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->mapVanilla("lava_cauldron", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * cauldron_liquid (StringTag) = lava, water
			 * fill_level (IntTag) = 0, 1, 2, 3, 4, 5, 6
			 */
		//});
		//$this->mapVanilla("lectern", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * direction (IntTag) = 0, 1, 2, 3
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->mapVanilla("light_block", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * block_light_level (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
			 */
		//});
		//$this->mapVanilla("movingBlock", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("observer", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 * powered_bit (ByteTag) = 0, 1
			 */
		//});
		//$this->mapVanilla("piston", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("pistonArmCollision", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("repeating_command_block", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * conditional_bit (ByteTag) = 0, 1
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("scaffolding", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * stability (IntTag) = 0, 1, 2, 3, 4, 5, 6, 7
			 * stability_check (ByteTag) = 0, 1
			 */
		//});
		//$this->mapVanilla("seagrass", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * sea_grass_type (StringTag) = default, double_bot, double_top
			 */
		//});
		//$this->mapVanilla("smithing_table", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
		//});
		//$this->mapVanilla("stickyPistonArmCollision", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("sticky_piston", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("stonecutter_block", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * facing_direction (IntTag) = 0, 1, 2, 3, 4, 5
			 */
		//});
		//$this->mapVanilla("structure_block", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * structure_block_type (StringTag) = corner, data, export, invalid, load, save
			 */
		//});
		//$this->mapVanilla("structure_void", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * structure_void_type (StringTag) = air, void
			 */
		//});
		//$this->mapVanilla("turtle_egg", function(BlockStateReader $in) : Block{
			/* TODO: parse properties
			 * cracked_state (StringTag) = cracked, max_cracked, no_cracks
			 * turtle_egg_count (StringTag) = four_egg, one_egg, three_egg, two_egg
			 */
		//});
		//$this->mapVanilla("wither_rose", function(BlockStateReader $in) : Block{
			//TODO: un-implemented block
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