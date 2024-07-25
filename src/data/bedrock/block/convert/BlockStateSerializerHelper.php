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

final class BlockStateSerializerHelper{
	public static function encodeButton(Button $block, Writer $out) : Writer{
		return $out
			->writeFacingDirection($block->getFacing())
			->writeBool(BlockStateNames::BUTTON_PRESSED_BIT, $block->isPressed());
	}

	public static function encodeCandle(Candle $block, Writer $out) : Writer{
		return $out
			->writeBool(StateNames::LIT, $block->isLit())
			->writeInt(StateNames::CANDLES, $block->getCount() - 1);
	}

	public static function encodeChemistryTable(ChemistryTable $block, string $chemistryTableType, Writer $out) : Writer{
		return $out
			->writeString(BlockStateNames::CHEMISTRY_TABLE_TYPE, $chemistryTableType)
			->writeLegacyHorizontalFacing(Facing::opposite($block->getFacing()));
	}

	public static function encodeCrops(Crops $block, Writer $out) : Writer{
		return $out->writeInt(BlockStateNames::GROWTH, $block->getAge());
	}

	public static function encodeColoredTorch(Torch $block, bool $highBit, Writer $out) : Writer{
		return $out
			->writeBool(BlockStateNames::COLOR_BIT, $highBit)
			->writeTorchFacing($block->getFacing());
	}

	public static function encodeCauldron(string $liquid, int $fillLevel) : Writer{
		return Writer::create(Ids::CAULDRON)
			->writeString(BlockStateNames::CAULDRON_LIQUID, $liquid)
			->writeInt(BlockStateNames::FILL_LEVEL, $fillLevel);
	}

	public static function selectCopperId(CopperOxidation $oxidation, string $noneId, string $exposedId, string $weatheredId, string $oxidizedId) : string{
		return match($oxidation){
			CopperOxidation::NONE => $noneId,
			CopperOxidation::EXPOSED => $exposedId,
			CopperOxidation::WEATHERED => $weatheredId,
			CopperOxidation::OXIDIZED => $oxidizedId,
		};
	}

	public static function encodeDoor(Door $block, Writer $out) : Writer{
		return $out
			->writeBool(BlockStateNames::UPPER_BLOCK_BIT, $block->isTop())
			->writeLegacyHorizontalFacing(Facing::rotateY($block->getFacing(), true))
			->writeBool(BlockStateNames::DOOR_HINGE_BIT, $block->isHingeRight())
			->writeBool(BlockStateNames::OPEN_BIT, $block->isOpen());
	}

	public static function encodeDoublePlant(DoublePlant $block, string $doublePlantType, Writer $out) : Writer{
		return $out
			->writeBool(BlockStateNames::UPPER_BLOCK_BIT, $block->isTop())
			->writeString(BlockStateNames::DOUBLE_PLANT_TYPE, $doublePlantType);
	}

	public static function encodeFenceGate(FenceGate $block, Writer $out) : Writer{
		return $out
			->writeLegacyHorizontalFacing($block->getFacing())
			->writeBool(BlockStateNames::IN_WALL_BIT, $block->isInWall())
			->writeBool(BlockStateNames::OPEN_BIT, $block->isOpen());
	}

	public static function encodeFloorSign(FloorSign $block, Writer $out) : Writer{
		return $out
			->writeInt(BlockStateNames::GROUND_SIGN_DIRECTION, $block->getRotation());
	}

	public static function encodeFurnace(Furnace $block, string $unlitId, string $litId) : Writer{
		return Writer::create($block->isLit() ? $litId : $unlitId)
			->writeCardinalHorizontalFacing($block->getFacing());
	}

	public static function encodeItemFrame(ItemFrame $block, string $id) : Writer{
		return Writer::create($id)
			->writeBool(StateNames::ITEM_FRAME_MAP_BIT, $block->hasMap())
			->writeBool(StateNames::ITEM_FRAME_PHOTO_BIT, false)
			->writeFacingDirection($block->getFacing());
	}

	public static function encodeLeaves(Leaves $block, Writer $out) : Writer{
		return $out
			->writeBool(BlockStateNames::PERSISTENT_BIT, $block->isNoDecay())
			->writeBool(BlockStateNames::UPDATE_BIT, $block->isCheckDecay());
	}

	public static function encodeLiquid(Liquid $block, string $stillId, string $flowingId) : Writer{
		return Writer::create($block->isStill() ? $stillId : $flowingId)
			->writeInt(BlockStateNames::LIQUID_DEPTH, $block->getDecay() | ($block->isFalling() ? 0x8 : 0));
	}

	public static function encodeLog(Wood $block, string $unstrippedId, string $strippedId) : Writer{
		$out = $block->isStripped() ?
			Writer::create($strippedId) :
			Writer::create($unstrippedId);
		return $out
			->writePillarAxis($block->getAxis());
	}

	public static function encodeMushroomBlock(RedMushroomBlock $block, Writer $out) : Writer{
		return $out
			->writeInt(BlockStateNames::HUGE_MUSHROOM_BITS, MushroomBlockTypeIdMap::getInstance()->toId($block->getMushroomBlockType()));
	}

	public static function encodeQuartz(string $type, int $axis) : Writer{
		return Writer::create(Ids::QUARTZ_BLOCK)
			->writeString(BlockStateNames::CHISEL_TYPE, $type)
			->writePillarAxis($axis); //this isn't needed for all types, but we have to write it anyway
	}

	public static function encodeSandstone(string $id, string $type) : Writer{
		return Writer::create($id)->writeString(BlockStateNames::SAND_STONE_TYPE, $type);
	}

	public static function encodeSapling(Sapling $block, Writer $out) : Writer{
		return $out
			->writeBool(BlockStateNames::AGE_BIT, $block->isReady());
	}

	public static function encodeSimplePressurePlate(SimplePressurePlate $block, Writer $out) : Writer{
		//TODO: not sure what the deal is here ... seems like a mojang bug / artifact of bad implementation?
		//best to keep this separate from weighted plates anyway...
		return $out
			->writeInt(BlockStateNames::REDSTONE_SIGNAL, $block->isPressed() ? 15 : 0);
	}

	public static function encodeSlab(Slab $block, string $singleId, string $doubleId) : Writer{
		$slabType = $block->getSlabType();
		return Writer::create($slabType === SlabType::DOUBLE ? $doubleId : $singleId)
			//this is (intentionally) also written for double slabs (as zero) to maintain bug parity with MCPE
			->writeSlabPosition($slabType === SlabType::DOUBLE ? SlabType::BOTTOM : $slabType);
	}

	public static function encodeStairs(Stair $block, Writer $out) : Writer{
		return $out
			->writeBool(BlockStateNames::UPSIDE_DOWN_BIT, $block->isUpsideDown())
			->writeWeirdoHorizontalFacing($block->getFacing());
	}

	public static function encodeStem(Stem $block, Writer $out) : Writer{
		//In PM, we use Facing::UP to indicate that the stem is not attached to a pumpkin/melon, since this makes the
		//most intuitive sense (the stem is pointing at the sky). However, Bedrock uses the DOWN state for this, which
		//is absurd, and I refuse to make our API similarly absurd.
		$facing = $block->getFacing();
		return self::encodeCrops($block, $out)
			->writeFacingWithoutUp($facing === Facing::UP ? Facing::DOWN : $facing);
	}

	public static function encodeStoneBricks(string $type) : Writer{
		return Writer::create(Ids::STONEBRICK)
			->writeString(BlockStateNames::STONE_BRICK_TYPE, $type);
	}

	private static function encodeStoneSlab(Slab $block, string $singleId, string $doubleId, string $typeKey, string $typeValue) : Writer{
		return self::encodeSlab($block, $singleId, $doubleId)
			->writeString($typeKey, $typeValue);
	}

	public static function encodeStoneSlab1(Slab $block, string $typeValue) : Writer{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB, Ids::DOUBLE_STONE_BLOCK_SLAB, BlockStateNames::STONE_SLAB_TYPE, $typeValue);
	}

	public static function encodeStoneSlab2(Slab $block, string $typeValue) : Writer{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB2, Ids::DOUBLE_STONE_BLOCK_SLAB2, BlockStateNames::STONE_SLAB_TYPE_2, $typeValue);
	}

	public static function encodeStoneSlab3(Slab $block, string $typeValue) : Writer{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB3, Ids::DOUBLE_STONE_BLOCK_SLAB3, BlockStateNames::STONE_SLAB_TYPE_3, $typeValue);
	}

	public static function encodeStoneSlab4(Slab $block, string $typeValue) : Writer{
		return self::encodeStoneSlab($block, Ids::STONE_BLOCK_SLAB4, Ids::DOUBLE_STONE_BLOCK_SLAB4, BlockStateNames::STONE_SLAB_TYPE_4, $typeValue);
	}

	public static function encodeTrapdoor(Trapdoor $block, Writer $out) : Writer{
		return $out
			->write5MinusHorizontalFacing($block->getFacing())
			->writeBool(BlockStateNames::UPSIDE_DOWN_BIT, $block->isTop())
			->writeBool(BlockStateNames::OPEN_BIT, $block->isOpen());
	}

	public static function encodeWall(Wall $block, Writer $out) : Writer{
		return $out
			->writeBool(BlockStateNames::WALL_POST_BIT, $block->isPost())
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_EAST, $block->getConnection(Facing::EAST))
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_NORTH, $block->getConnection(Facing::NORTH))
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_SOUTH, $block->getConnection(Facing::SOUTH))
			->writeWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_WEST, $block->getConnection(Facing::WEST));
	}

	public static function encodeLegacyWall(Wall $block, string $type) : Writer{
		return self::encodeWall($block, Writer::create(Ids::COBBLESTONE_WALL))
			->writeString(BlockStateNames::WALL_BLOCK_TYPE, $type);
	}

	public static function encodeWallSign(WallSign $block, Writer $out) : Writer{
		return $out
			->writeHorizontalFacing($block->getFacing());
	}
}
