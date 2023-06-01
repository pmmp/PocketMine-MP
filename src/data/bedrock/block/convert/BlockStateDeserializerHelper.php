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

use pocketmine\block\Block;
use pocketmine\block\Button;
use pocketmine\block\Candle;
use pocketmine\block\Copper;
use pocketmine\block\CopperSlab;
use pocketmine\block\CopperStairs;
use pocketmine\block\Crops;
use pocketmine\block\DaylightSensor;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\FloorCoralFan;
use pocketmine\block\FloorSign;
use pocketmine\block\ItemFrame;
use pocketmine\block\Leaves;
use pocketmine\block\Liquid;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\SimplePressurePlate;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Stem;
use pocketmine\block\Trapdoor;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wall;
use pocketmine\block\WallCoralFan;
use pocketmine\block\WallSign;
use pocketmine\block\WeightedPressurePlate;
use pocketmine\block\Wood;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\MushroomBlockTypeIdMap;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;

final class BlockStateDeserializerHelper{

	/** @throws BlockStateDeserializeException */
	public static function decodeButton(Button $block, BlockStateReader $in) : Button{
		return $block
			->setFacing($in->readFacingDirection())
			->setPressed($in->readBool(BlockStateNames::BUTTON_PRESSED_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeCandle(Candle $block, BlockStateReader $in) : Candle{
		return $block
			->setCount($in->readBoundedInt(StateNames::CANDLES, 0, 3) + 1)
			->setLit($in->readBool(StateNames::LIT));
	}

	/**
	 * @phpstan-template TCrops of Crops
	 * @phpstan-param TCrops $block
	 * @phpstan-return TCrops
	 *
	 * @throws BlockStateDeserializeException
	 */
	public static function decodeCrops(Crops $block, BlockStateReader $in) : Crops{
		return $block->setAge($in->readBoundedInt(BlockStateNames::GROWTH, 0, 7));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeComparator(RedstoneComparator $block, BlockStateReader $in) : RedstoneComparator{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setPowered($in->readBool(BlockStateNames::OUTPUT_LIT_BIT))
			->setSubtractMode($in->readBool(BlockStateNames::OUTPUT_SUBTRACT_BIT));
	}

	/**
	 * @phpstan-template TBlock of Copper|CopperSlab|CopperStairs
	 *
	 * @phpstan-param TBlock $block
	 * @phpstan-return TBlock
	 */
	public static function decodeCopper(Copper|CopperSlab|CopperStairs $block, CopperOxidation $oxidation) : Copper|CopperSlab|CopperStairs{
		$block->setOxidation($oxidation);
		$block->setWaxed(false);
		return $block;
	}

	/**
	 * @phpstan-template TBlock of Copper|CopperSlab|CopperStairs
	 *
	 * @phpstan-param TBlock $block
	 * @phpstan-return TBlock
	 */
	public static function decodeWaxedCopper(Copper|CopperSlab|CopperStairs $block, CopperOxidation $oxidation) : Copper|CopperSlab|CopperStairs{
		$block->setOxidation($oxidation);
		$block->setWaxed(true);
		return $block;
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDaylightSensor(DaylightSensor $block, BlockStateReader $in) : DaylightSensor{
		return $block
			->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDoor(Door $block, BlockStateReader $in) : Door{
		//TODO: check if these need any special treatment to get the appropriate data to both halves of the door
		return $block
			->setTop($in->readBool(BlockStateNames::UPPER_BLOCK_BIT))
			->setFacing(Facing::rotateY($in->readLegacyHorizontalFacing(), false))
			->setHingeRight($in->readBool(BlockStateNames::DOOR_HINGE_BIT))
			->setOpen($in->readBool(BlockStateNames::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeFenceGate(FenceGate $block, BlockStateReader $in) : FenceGate{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setInWall($in->readBool(BlockStateNames::IN_WALL_BIT))
			->setOpen($in->readBool(BlockStateNames::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeFloorCoralFan(FloorCoralFan $block, BlockStateReader $in) : FloorCoralFan{
		return $block
			->setCoralType($in->readCoralType())
			->setAxis(match($in->readBoundedInt(BlockStateNames::CORAL_FAN_DIRECTION, 0, 1)){
				0 => Axis::X,
				1 => Axis::Z,
				default => throw new AssumptionFailedError("readBoundedInt() should have prevented this"),
			});
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeFloorSign(FloorSign $block, BlockStateReader $in) : FloorSign{
		return $block
			->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
	}

	public static function decodeItemFrame(ItemFrame $block, BlockStateReader $in) : ItemFrame{
		$in->todo(StateNames::ITEM_FRAME_PHOTO_BIT); //TODO: not sure what the point of this is
		return $block
			->setFacing($in->readFacingDirection())
			->setHasMap($in->readBool(StateNames::ITEM_FRAME_MAP_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLeaves(Leaves $block, BlockStateReader $in) : Leaves{
		return $block
			->setNoDecay($in->readBool(StateNames::PERSISTENT_BIT))
			->setCheckDecay($in->readBool(StateNames::UPDATE_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLiquid(Liquid $block, BlockStateReader $in, bool $still) : Liquid{
		$fluidHeightState = $in->readBoundedInt(BlockStateNames::LIQUID_DEPTH, 0, 15);
		return $block
			->setDecay($fluidHeightState & 0x7)
			->setFalling(($fluidHeightState & 0x8) !== 0)
			->setStill($still);
	}

	public static function decodeFlowingLiquid(Liquid $block, BlockStateReader $in) : Liquid{
		return self::decodeLiquid($block, $in, false);
	}

	public static function decodeStillLiquid(Liquid $block, BlockStateReader $in) : Liquid{
		return self::decodeLiquid($block, $in, true);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLog(Wood $block, bool $stripped, BlockStateReader $in) : Wood{
		return $block
			->setAxis($in->readPillarAxis())
			->setStripped($stripped);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeMushroomBlock(RedMushroomBlock $block, BlockStateReader $in) : Block{
		switch($type = $in->readBoundedInt(BlockStateNames::HUGE_MUSHROOM_BITS, 0, 15)){
			case BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM: return VanillaBlocks::ALL_SIDED_MUSHROOM_STEM();
			case BlockLegacyMetadata::MUSHROOM_BLOCK_STEM: return VanillaBlocks::MUSHROOM_STEM();
			default:
				//invalid types get left as default
				$type = MushroomBlockTypeIdMap::getInstance()->fromId($type);
				return $type !== null ? $block->setMushroomBlockType($type) : $block;
		}
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeRepeater(RedstoneRepeater $block, BlockStateReader $in) : RedstoneRepeater{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setDelay($in->readBoundedInt(BlockStateNames::REPEATER_DELAY, 0, 3) + 1);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeSimplePressurePlate(SimplePressurePlate $block, BlockStateReader $in) : SimplePressurePlate{
		//TODO: not sure what the deal is here ... seems like a mojang bug / artifact of bad implementation?
		//best to keep this separate from weighted plates anyway...
		return $block->setPressed($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15) !== 0);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeStairs(Stair $block, BlockStateReader $in) : Stair{
		return $block
			->setUpsideDown($in->readBool(BlockStateNames::UPSIDE_DOWN_BIT))
			->setFacing($in->readWeirdoHorizontalFacing());
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeStem(Stem $block, BlockStateReader $in) : Stem{
		//TODO: our stems don't support facings yet (facing_direction)
		$in->todo(BlockStateNames::FACING_DIRECTION);
		return self::decodeCrops($block, $in);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeTrapdoor(Trapdoor $block, BlockStateReader $in) : Trapdoor{
		return $block
			->setFacing($in->read5MinusHorizontalFacing())
			->setTop($in->readBool(BlockStateNames::UPSIDE_DOWN_BIT))
			->setOpen($in->readBool(BlockStateNames::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeWall(Wall $block, BlockStateReader $in) : Wall{
		$block->setPost($in->readBool(BlockStateNames::WALL_POST_BIT));
		$block->setConnection(Facing::NORTH, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_NORTH));
		$block->setConnection(Facing::SOUTH, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_SOUTH));
		$block->setConnection(Facing::WEST, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_WEST));
		$block->setConnection(Facing::EAST, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_EAST));

		return $block;
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeWallCoralFan(WallCoralFan $block, BlockStateReader $in) : WallCoralFan{
		return $block
			->setDead($in->readBool(BlockStateNames::DEAD_BIT))
			->setFacing($in->readCoralFacing());
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeWallSign(WallSign $block, BlockStateReader $in) : WallSign{
		return $block
			->setFacing($in->readHorizontalFacing());
	}

	public static function decodeWeightedPressurePlate(WeightedPressurePlate $block, BlockStateReader $in) : WeightedPressurePlate{
		return $block
			->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
	}

	/** @throws BlockStateDeserializeException */
	public static function mapLegacyWallType(BlockStateReader $in) : Wall{
		return self::decodeWall(match($type = $in->readString(BlockStateNames::WALL_BLOCK_TYPE)){
			StringValues::WALL_BLOCK_TYPE_ANDESITE => VanillaBlocks::ANDESITE_WALL(),
			StringValues::WALL_BLOCK_TYPE_BRICK => VanillaBlocks::BRICK_WALL(),
			StringValues::WALL_BLOCK_TYPE_COBBLESTONE => VanillaBlocks::COBBLESTONE_WALL(),
			StringValues::WALL_BLOCK_TYPE_DIORITE => VanillaBlocks::DIORITE_WALL(),
			StringValues::WALL_BLOCK_TYPE_END_BRICK => VanillaBlocks::END_STONE_BRICK_WALL(),
			StringValues::WALL_BLOCK_TYPE_GRANITE => VanillaBlocks::GRANITE_WALL(),
			StringValues::WALL_BLOCK_TYPE_MOSSY_COBBLESTONE => VanillaBlocks::MOSSY_COBBLESTONE_WALL(),
			StringValues::WALL_BLOCK_TYPE_MOSSY_STONE_BRICK => VanillaBlocks::MOSSY_STONE_BRICK_WALL(),
			StringValues::WALL_BLOCK_TYPE_NETHER_BRICK => VanillaBlocks::NETHER_BRICK_WALL(),
			StringValues::WALL_BLOCK_TYPE_PRISMARINE => VanillaBlocks::PRISMARINE_WALL(),
			StringValues::WALL_BLOCK_TYPE_RED_NETHER_BRICK => VanillaBlocks::RED_NETHER_BRICK_WALL(),
			StringValues::WALL_BLOCK_TYPE_RED_SANDSTONE => VanillaBlocks::RED_SANDSTONE_WALL(),
			StringValues::WALL_BLOCK_TYPE_SANDSTONE => VanillaBlocks::SANDSTONE_WALL(),
			StringValues::WALL_BLOCK_TYPE_STONE_BRICK => VanillaBlocks::STONE_BRICK_WALL(),
			default => throw $in->badValueException(BlockStateNames::WALL_BLOCK_TYPE, $type),
		}, $in);
	}

	/** @throws BlockStateDeserializeException */
	public static function mapStoneSlab1Type(BlockStateReader $in) : Slab{
		//* stone_slab_type (StringTag) = brick, cobblestone, nether_brick, quartz, sandstone, smooth_stone, stone_brick, wood
		return match($type = $in->readString(BlockStateNames::STONE_SLAB_TYPE)){
			StringValues::STONE_SLAB_TYPE_BRICK => VanillaBlocks::BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_COBBLESTONE => VanillaBlocks::COBBLESTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_NETHER_BRICK => VanillaBlocks::NETHER_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_QUARTZ => VanillaBlocks::QUARTZ_SLAB(),
			StringValues::STONE_SLAB_TYPE_SANDSTONE => VanillaBlocks::SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_SMOOTH_STONE => VanillaBlocks::SMOOTH_STONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_STONE_BRICK => VanillaBlocks::STONE_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_WOOD => VanillaBlocks::FAKE_WOODEN_SLAB(),
			default => throw $in->badValueException(BlockStateNames::STONE_SLAB_TYPE, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	public static function mapStoneSlab2Type(BlockStateReader $in) : Slab{
		// * stone_slab_type_2 (StringTag) = mossy_cobblestone, prismarine_brick, prismarine_dark, prismarine_rough, purpur, red_nether_brick, red_sandstone, smooth_sandstone
		return match($type = $in->readString(BlockStateNames::STONE_SLAB_TYPE_2)){
			StringValues::STONE_SLAB_TYPE_2_MOSSY_COBBLESTONE => VanillaBlocks::MOSSY_COBBLESTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PRISMARINE_BRICK => VanillaBlocks::PRISMARINE_BRICKS_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PRISMARINE_DARK => VanillaBlocks::DARK_PRISMARINE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PRISMARINE_ROUGH => VanillaBlocks::PRISMARINE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_PURPUR => VanillaBlocks::PURPUR_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_RED_NETHER_BRICK => VanillaBlocks::RED_NETHER_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_RED_SANDSTONE => VanillaBlocks::RED_SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_2_SMOOTH_SANDSTONE => VanillaBlocks::SMOOTH_SANDSTONE_SLAB(),
			default => throw $in->badValueException(BlockStateNames::STONE_SLAB_TYPE_2, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	public static function mapStoneSlab3Type(BlockStateReader $in) : Slab{
		// * stone_slab_type_3 (StringTag) = andesite, diorite, end_stone_brick, granite, polished_andesite, polished_diorite, polished_granite, smooth_red_sandstone
		return match($type = $in->readString(BlockStateNames::STONE_SLAB_TYPE_3)){
			StringValues::STONE_SLAB_TYPE_3_ANDESITE => VanillaBlocks::ANDESITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_DIORITE => VanillaBlocks::DIORITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_END_STONE_BRICK => VanillaBlocks::END_STONE_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_GRANITE => VanillaBlocks::GRANITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_POLISHED_ANDESITE => VanillaBlocks::POLISHED_ANDESITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_POLISHED_DIORITE => VanillaBlocks::POLISHED_DIORITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_POLISHED_GRANITE => VanillaBlocks::POLISHED_GRANITE_SLAB(),
			StringValues::STONE_SLAB_TYPE_3_SMOOTH_RED_SANDSTONE => VanillaBlocks::SMOOTH_RED_SANDSTONE_SLAB(),
			default => throw $in->badValueException(BlockStateNames::STONE_SLAB_TYPE_3, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	public static function mapStoneSlab4Type(BlockStateReader $in) : Slab{
		// * stone_slab_type_4 (StringTag) = cut_red_sandstone, cut_sandstone, mossy_stone_brick, smooth_quartz, stone
		return match($type = $in->readString(BlockStateNames::STONE_SLAB_TYPE_4)){
			StringValues::STONE_SLAB_TYPE_4_CUT_RED_SANDSTONE => VanillaBlocks::CUT_RED_SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_CUT_SANDSTONE => VanillaBlocks::CUT_SANDSTONE_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_MOSSY_STONE_BRICK => VanillaBlocks::MOSSY_STONE_BRICK_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_SMOOTH_QUARTZ => VanillaBlocks::SMOOTH_QUARTZ_SLAB(),
			StringValues::STONE_SLAB_TYPE_4_STONE => VanillaBlocks::STONE_SLAB(),
			default => throw $in->badValueException(BlockStateNames::STONE_SLAB_TYPE_4, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	public static function mapWoodenSlabType(BlockStateReader $in) : Slab{
		// * wood_type (StringTag) = acacia, birch, dark_oak, jungle, oak, spruce
		return match($type = $in->readString(BlockStateNames::WOOD_TYPE)){
			StringValues::WOOD_TYPE_ACACIA => VanillaBlocks::ACACIA_SLAB(),
			StringValues::WOOD_TYPE_BIRCH => VanillaBlocks::BIRCH_SLAB(),
			StringValues::WOOD_TYPE_DARK_OAK => VanillaBlocks::DARK_OAK_SLAB(),
			StringValues::WOOD_TYPE_JUNGLE => VanillaBlocks::JUNGLE_SLAB(),
			StringValues::WOOD_TYPE_OAK => VanillaBlocks::OAK_SLAB(),
			StringValues::WOOD_TYPE_SPRUCE => VanillaBlocks::SPRUCE_SLAB(),
			default => throw $in->badValueException(BlockStateNames::WOOD_TYPE, $type),
		};
	}
}
