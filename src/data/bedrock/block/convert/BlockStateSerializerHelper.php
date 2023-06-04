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

use pocketmine\block\Button;
use pocketmine\block\Candle;
use pocketmine\block\ChemistryTable;
use pocketmine\block\Crops;
use pocketmine\block\Door;
use pocketmine\block\DoublePlant;
use pocketmine\block\FenceGate;
use pocketmine\block\FloorSign;
use pocketmine\block\Furnace;
use pocketmine\block\ItemFrame;
use pocketmine\block\Leaves;
use pocketmine\block\Liquid;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\Sapling;
use pocketmine\block\SimplePressurePlate;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Stem;
use pocketmine\block\Torch;
use pocketmine\block\Trapdoor;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\SlabType;
use pocketmine\block\Wall;
use pocketmine\block\WallSign;
use pocketmine\block\Wood;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use pocketmine\data\bedrock\MushroomBlockTypeIdMap;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;

final class BlockStateSerializerHelper{

	public static function encodeAllSidedLog(Wood $block) : BlockStateWriter{
		return BlockStateWriter::create(Ids::WOOD)
			->writeBool(BlockStateNames::STRIPPED_BIT, $block->isStripped())
			->writePillarAxis($block->getAxis())
			->writeLegacyWoodType($block->getWoodType());
	}

	public static function encodeButton(Button $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeFacingDirection($block->getFacing())
			->writeBool(BlockStateNames::BUTTON_PRESSED_BIT, $block->isPressed());
	}

	public static function encodeCandle(Candle $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeBool(StateNames::LIT, $block->isLit())
			->writeInt(StateNames::CANDLES, $block->getCount() - 1);
	}

	public static function encodeChemistryTable(ChemistryTable $block, string $chemistryTableType, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeString(BlockStateNames::CHEMISTRY_TABLE_TYPE, $chemistryTableType)
			->writeLegacyHorizontalFacing(Facing::opposite($block->getFacing()));
	}

	public static function encodeCrops(Crops $block, BlockStateWriter $out) : BlockStateWriter{
		return $out->writeInt(BlockStateNames::GROWTH, $block->getAge());
	}

	public static function encodeColoredTorch(Torch $block, bool $highBit, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeBool(BlockStateNames::COLOR_BIT, $highBit)
			->writeTorchFacing($block->getFacing());
	}

	public static function encodeCauldron(string $liquid, int $fillLevel, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeString(BlockStateNames::CAULDRON_LIQUID, $liquid)
			->writeInt(BlockStateNames::FILL_LEVEL, $fillLevel);
	}

	public static function selectCopperId(CopperOxidation $oxidation, string $noneId, string $exposedId, string $weatheredId, string $oxidizedId) : string{
		return match($oxidation){
			CopperOxidation::NONE() => $noneId,
			CopperOxidation::EXPOSED() => $exposedId,
			CopperOxidation::WEATHERED() => $weatheredId,
			CopperOxidation::OXIDIZED() => $oxidizedId,
			default => throw new AssumptionFailedError("Unhandled copper oxidation " . $oxidation->name())
		};
	}

	public static function encodeDoor(Door $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeBool(BlockStateNames::UPPER_BLOCK_BIT, $block->isTop())
			->writeLegacyHorizontalFacing(Facing::rotateY($block->getFacing(), true))
			->writeBool(BlockStateNames::DOOR_HINGE_BIT, $block->isHingeRight())
			->writeBool(BlockStateNames::OPEN_BIT, $block->isOpen());
	}

	public static function encodeDoublePlant(DoublePlant $block, string $doublePlantType, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeBool(BlockStateNames::UPPER_BLOCK_BIT, $block->isTop())
			->writeString(BlockStateNames::DOUBLE_PLANT_TYPE, $doublePlantType);
	}

	public static function encodeFenceGate(FenceGate $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeLegacyHorizontalFacing($block->getFacing())
			->writeBool(BlockStateNames::IN_WALL_BIT, $block->isInWall())
			->writeBool(BlockStateNames::OPEN_BIT, $block->isOpen());
	}

	public static function encodeFloorSign(FloorSign $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeInt(BlockStateNames::GROUND_SIGN_DIRECTION, $block->getRotation());
	}

	public static function encodeFurnace(Furnace $block, string $unlitId, string $litId) : BlockStateWriter{
		return BlockStateWriter::create($block->isLit() ? $litId : $unlitId)
			->writeHorizontalFacing($block->getFacing());
	}

	public static function encodeItemFrame(ItemFrame $block, string $id) : BlockStateWriter{
		return Writer::create($id)
			->writeBool(StateNames::ITEM_FRAME_MAP_BIT, $block->hasMap())
			->writeBool(StateNames::ITEM_FRAME_PHOTO_BIT, false)
			->writeFacingDirection($block->getFacing());
	}

	public static function encodeLeaves(Leaves $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeBool(BlockStateNames::PERSISTENT_BIT, $block->isNoDecay())
			->writeBool(BlockStateNames::UPDATE_BIT, $block->isCheckDecay());
	}

	public static function encodeLeaves1(Leaves $block, string $type) : BlockStateWriter{
		return self::encodeLeaves($block, BlockStateWriter::create(Ids::LEAVES)
			->writeString(BlockStateNames::OLD_LEAF_TYPE, $type));
	}

	public static function encodeLeaves2(Leaves $block, string $type) : BlockStateWriter{
		return self::encodeLeaves($block, BlockStateWriter::create(Ids::LEAVES2)
			->writeString(BlockStateNames::NEW_LEAF_TYPE, $type));
	}

	public static function encodeLiquid(Liquid $block, string $stillId, string $flowingId) : BlockStateWriter{
		return BlockStateWriter::create($block->isStill() ? $stillId : $flowingId)
			->writeInt(BlockStateNames::LIQUID_DEPTH, $block->getDecay() | ($block->isFalling() ? 0x8 : 0));
	}

	public static function encodeLog(Wood $block, string $unstrippedId, string $strippedId) : BlockStateWriter{
		$out = $block->isStripped() ?
			BlockStateWriter::create($strippedId) :
			BlockStateWriter::create($unstrippedId);
		return $out
			->writePillarAxis($block->getAxis());
	}

	public static function encodeMushroomBlock(RedMushroomBlock $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeInt(BlockStateNames::HUGE_MUSHROOM_BITS, MushroomBlockTypeIdMap::getInstance()->toId($block->getMushroomBlockType()));
	}

	public static function encodeQuartz(string $type, int $axis) : BlockStateWriter{
		return BlockStateWriter::create(Ids::QUARTZ_BLOCK)
			->writeString(BlockStateNames::CHISEL_TYPE, $type)
			->writePillarAxis($axis); //this isn't needed for all types, but we have to write it anyway
	}

	public static function encodeRedFlower(string $type) : BlockStateWriter{
		return BlockStateWriter::create(Ids::RED_FLOWER)->writeString(BlockStateNames::FLOWER_TYPE, $type);
	}

	public static function encodeSandstone(string $id, string $type) : BlockStateWriter{
		return BlockStateWriter::create($id)->writeString(BlockStateNames::SAND_STONE_TYPE, $type);
	}

	public static function encodeSapling(Sapling $block, string $type) : BlockStateWriter{
		return BlockStateWriter::create(Ids::SAPLING)
			->writeBool(BlockStateNames::AGE_BIT, $block->isReady())
			->writeString(BlockStateNames::SAPLING_TYPE, $type);
	}

	public static function encodeSimplePressurePlate(SimplePressurePlate $block, BlockStateWriter $out) : BlockStateWriter{
		//TODO: not sure what the deal is here ... seems like a mojang bug / artifact of bad implementation?
		//best to keep this separate from weighted plates anyway...
		return $out
			->writeInt(BlockStateNames::REDSTONE_SIGNAL, $block->isPressed() ? 15 : 0);
	}

	public static function encodeSlab(Slab $block, string $singleId, string $doubleId) : BlockStateWriter{
		$slabType = $block->getSlabType();
		return BlockStateWriter::create($slabType->equals(SlabType::DOUBLE()) ? $doubleId : $singleId)

			//this is (intentionally) also written for double slabs (as zero) to maintain bug parity with MCPE
			->writeBool(BlockStateNames::TOP_SLOT_BIT, $slabType->equals(SlabType::TOP()));
	}

	public static function encodeStairs(Stair $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeBool(BlockStateNames::UPSIDE_DOWN_BIT, $block->isUpsideDown())
			->writeWeirdoHorizontalFacing($block->getFacing());
	}

	public static function encodeStem(Stem $block, BlockStateWriter $out) : BlockStateWriter{
		return self::encodeCrops($block, $out)
			->writeHorizontalFacing(Facing::NORTH); //TODO: PM impl doesn't support this yet
	}

	public static function encodeStone(string $type) : BlockStateWriter{
		return BlockStateWriter::create(Ids::STONE)
			->writeString(BlockStateNames::STONE_TYPE, $type);
	}

	public static function encodeStoneBricks(string $type) : BlockStateWriter{
		return BlockStateWriter::create(Ids::STONEBRICK)
			->writeString(BlockStateNames::STONE_BRICK_TYPE, $type);
	}

	private static function encodeStoneSlab(Slab $block, string $singleId, string $doubleId, string $typeKey, string $typeValue) : BlockStateWriter{
		return self::encodeSlab($block, $singleId, $doubleId)
			->writeString($typeKey, $typeValue);
	}

	public static function encodeStoneSlab1(Slab $block, string $typeValue) : BlockStateWriter{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB, Ids::DOUBLE_STONE_BLOCK_SLAB, BlockStateNames::STONE_SLAB_TYPE, $typeValue);
	}

	public static function encodeStoneSlab2(Slab $block, string $typeValue) : BlockStateWriter{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB2, Ids::DOUBLE_STONE_BLOCK_SLAB2, BlockStateNames::STONE_SLAB_TYPE_2, $typeValue);
	}

	public static function encodeStoneSlab3(Slab $block, string $typeValue) : BlockStateWriter{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB3, Ids::DOUBLE_STONE_BLOCK_SLAB3, BlockStateNames::STONE_SLAB_TYPE_3, $typeValue);
	}

	public static function encodeStoneSlab4(Slab $block, string $typeValue) : BlockStateWriter{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB4, Ids::DOUBLE_STONE_BLOCK_SLAB4, BlockStateNames::STONE_SLAB_TYPE_4, $typeValue);
	}

	public static function encodeTrapdoor(Trapdoor $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->write5MinusHorizontalFacing($block->getFacing())
			->writeBool(BlockStateNames::UPSIDE_DOWN_BIT, $block->isTop())
			->writeBool(BlockStateNames::OPEN_BIT, $block->isOpen());
	}

	public static function encodeWall(Wall $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeBool(BlockStateNames::WALL_POST_BIT, $block->isPost())
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_EAST, $block->getConnection(Facing::EAST))
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_NORTH, $block->getConnection(Facing::NORTH))
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_SOUTH, $block->getConnection(Facing::SOUTH))
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_WEST, $block->getConnection(Facing::WEST));
	}

	public static function encodeLegacyWall(Wall $block, string $type) : BlockStateWriter{
		return self::encodeWall($block, BlockStateWriter::create(Ids::COBBLESTONE_WALL))
			->writeString(BlockStateNames::WALL_BLOCK_TYPE, $type);
	}

	public static function encodeWallSign(WallSign $block, BlockStateWriter $out) : BlockStateWriter{
		return $out
			->writeHorizontalFacing($block->getFacing());
	}

	public static function encodeWoodenSlab(Slab $block, string $typeValue) : BlockStateWriter{
		return self::encodeSlab($block, Ids::WOODEN_SLAB, Ids::DOUBLE_WOODEN_SLAB)
			->writeString(BlockStateNames::WOOD_TYPE, $typeValue);
	}
}
