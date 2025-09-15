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

namespace pocketmine\data\bedrock\block\convert\property;

use pocketmine\block\Bamboo;
use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DripleafState;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\data\bedrock\block\BlockLegacyMetadata as LegacyMeta;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\utils\SingletonTrait;

final class ValueMappings{
	use SingletonTrait; //???

	/** @phpstan-var EnumFromRawStateMap<DyeColor, string> */
	public readonly EnumFromRawStateMap $dyeColor;
	/** @phpstan-var EnumFromRawStateMap<DyeColor, string> */
	public readonly EnumFromRawStateMap $dyeColorWithSilver;
	/** @phpstan-var EnumFromRawStateMap<MobHeadType, string> */
	public readonly EnumFromRawStateMap $mobHeadType;
	/** @phpstan-var EnumFromRawStateMap<FroglightType, string> */
	public readonly EnumFromRawStateMap $froglightType;
	/** @phpstan-var EnumFromRawStateMap<DirtType, string> */
	public readonly EnumFromRawStateMap $dirtType;

	/** @phpstan-var EnumFromRawStateMap<DripleafState, string> */
	public readonly EnumFromRawStateMap $dripleafState;
	/** @phpstan-var EnumFromRawStateMap<BellAttachmentType, string> */
	public readonly EnumFromRawStateMap $bellAttachmentType;
	/** @phpstan-var EnumFromRawStateMap<LeverFacing, string> */
	public readonly EnumFromRawStateMap $leverFacing;

	/** @phpstan-var EnumFromRawStateMap<MushroomBlockType, int> */
	public readonly EnumFromRawStateMap $mushroomBlockType;

	/** @phpstan-var IntFromRawStateMap<string> */
	public readonly IntFromRawStateMap $cardinalDirection;
	/** @phpstan-var IntFromRawStateMap<string> */
	public readonly IntFromRawStateMap $blockFace;
	/** @phpstan-var IntFromRawStateMap<string> */
	public readonly IntFromRawStateMap $pillarAxis;
	/** @phpstan-var IntFromRawStateMap<string> */
	public readonly IntFromRawStateMap $torchFacing;
	/** @phpstan-var IntFromRawStateMap<string> */
	public readonly IntFromRawStateMap $portalAxis;
	/** @phpstan-var IntFromRawStateMap<string> */
	public readonly IntFromRawStateMap $bambooLeafSize;

	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $horizontalFacing5Minus;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $horizontalFacingSWNE;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $horizontalFacingSWNEInverted;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $horizontalFacingCoral;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $horizontalFacingClassic;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $facing;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $facingEndRod;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $coralAxis;

	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $facingExceptDown;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $facingExceptUp;
	/** @phpstan-var IntFromRawStateMap<int> */
	public readonly IntFromRawStateMap $facingStem;

	public function __construct(){
		//flattened ID components - we can't generate constants for these
		$this->dyeColor = EnumFromRawStateMap::string(DyeColor::class, fn(DyeColor $case) => match ($case) {
			DyeColor::BLACK => "black",
			DyeColor::BLUE => "blue",
			DyeColor::BROWN => "brown",
			DyeColor::CYAN => "cyan",
			DyeColor::GRAY => "gray",
			DyeColor::GREEN => "green",
			DyeColor::LIGHT_BLUE => "light_blue",
			DyeColor::LIGHT_GRAY => "light_gray",
			DyeColor::LIME => "lime",
			DyeColor::MAGENTA => "magenta",
			DyeColor::ORANGE => "orange",
			DyeColor::PINK => "pink",
			DyeColor::PURPLE => "purple",
			DyeColor::RED => "red",
			DyeColor::WHITE => "white",
			DyeColor::YELLOW => "yellow"
		});
		$this->dyeColorWithSilver = EnumFromRawStateMap::string(DyeColor::class, fn(DyeColor $case) => match ($case) {
			DyeColor::LIGHT_GRAY => "silver",
			default => $this->dyeColor->valueToRaw($case)
		});

		$this->mobHeadType = EnumFromRawStateMap::string(MobHeadType::class, fn(MobHeadType $case) => match ($case) {
			MobHeadType::CREEPER => Ids::CREEPER_HEAD,
			MobHeadType::DRAGON => Ids::DRAGON_HEAD,
			MobHeadType::PIGLIN => Ids::PIGLIN_HEAD,
			MobHeadType::PLAYER => Ids::PLAYER_HEAD,
			MobHeadType::SKELETON => Ids::SKELETON_SKULL,
			MobHeadType::WITHER_SKELETON => Ids::WITHER_SKELETON_SKULL,
			MobHeadType::ZOMBIE => Ids::ZOMBIE_HEAD
		});
		$this->froglightType = EnumFromRawStateMap::string(FroglightType::class, fn(FroglightType $case) => match ($case) {
			FroglightType::OCHRE => Ids::OCHRE_FROGLIGHT,
			FroglightType::PEARLESCENT => Ids::PEARLESCENT_FROGLIGHT,
			FroglightType::VERDANT => Ids::VERDANT_FROGLIGHT,
		});
		$this->dirtType = EnumFromRawStateMap::string(DirtType::class, fn(DirtType $case) => match ($case) {
			DirtType::NORMAL => Ids::DIRT,
			DirtType::COARSE => Ids::COARSE_DIRT,
			DirtType::ROOTED => Ids::DIRT_WITH_ROOTS,
		});

		//state value mappings
		$this->dripleafState = EnumFromRawStateMap::string(DripleafState::class, fn(DripleafState $case) => match ($case) {
			DripleafState::STABLE => StringValues::BIG_DRIPLEAF_TILT_NONE,
			DripleafState::UNSTABLE => StringValues::BIG_DRIPLEAF_TILT_UNSTABLE,
			DripleafState::PARTIAL_TILT => StringValues::BIG_DRIPLEAF_TILT_PARTIAL_TILT,
			DripleafState::FULL_TILT => StringValues::BIG_DRIPLEAF_TILT_FULL_TILT
		});
		$this->bellAttachmentType = EnumFromRawStateMap::string(BellAttachmentType::class, fn(BellAttachmentType $case) => match ($case) {
			BellAttachmentType::FLOOR => StringValues::ATTACHMENT_STANDING,
			BellAttachmentType::CEILING => StringValues::ATTACHMENT_HANGING,
			BellAttachmentType::ONE_WALL => StringValues::ATTACHMENT_SIDE,
			BellAttachmentType::TWO_WALLS => StringValues::ATTACHMENT_MULTIPLE,
		});
		$this->leverFacing = EnumFromRawStateMap::string(LeverFacing::class, fn(LeverFacing $case) => match ($case) {
			LeverFacing::DOWN_AXIS_Z => StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH,
			LeverFacing::DOWN_AXIS_X => StringValues::LEVER_DIRECTION_DOWN_EAST_WEST,
			LeverFacing::UP_AXIS_Z => StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH,
			LeverFacing::UP_AXIS_X => StringValues::LEVER_DIRECTION_UP_EAST_WEST,
			LeverFacing::NORTH => StringValues::LEVER_DIRECTION_NORTH,
			LeverFacing::SOUTH => StringValues::LEVER_DIRECTION_SOUTH,
			LeverFacing::WEST => StringValues::LEVER_DIRECTION_WEST,
			LeverFacing::EAST => StringValues::LEVER_DIRECTION_EAST
		});

		$this->mushroomBlockType = EnumFromRawStateMap::int(
			MushroomBlockType::class,
			fn(MushroomBlockType $case) => match ($case) {
				MushroomBlockType::PORES => LegacyMeta::MUSHROOM_BLOCK_ALL_PORES,
				MushroomBlockType::CAP_NORTHWEST => LegacyMeta::MUSHROOM_BLOCK_CAP_NORTHWEST_CORNER,
				MushroomBlockType::CAP_NORTH => LegacyMeta::MUSHROOM_BLOCK_CAP_NORTH_SIDE,
				MushroomBlockType::CAP_NORTHEAST => LegacyMeta::MUSHROOM_BLOCK_CAP_NORTHEAST_CORNER,
				MushroomBlockType::CAP_WEST => LegacyMeta::MUSHROOM_BLOCK_CAP_WEST_SIDE,
				MushroomBlockType::CAP_MIDDLE => LegacyMeta::MUSHROOM_BLOCK_CAP_TOP_ONLY,
				MushroomBlockType::CAP_EAST => LegacyMeta::MUSHROOM_BLOCK_CAP_EAST_SIDE,
				MushroomBlockType::CAP_SOUTHWEST => LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTHWEST_CORNER,
				MushroomBlockType::CAP_SOUTH => LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTH_SIDE,
				MushroomBlockType::CAP_SOUTHEAST => LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTHEAST_CORNER,
				MushroomBlockType::ALL_CAP => LegacyMeta::MUSHROOM_BLOCK_ALL_CAP,
			},
			fn(MushroomBlockType $case) => match ($case) {
				MushroomBlockType::ALL_CAP => [11, 12, 13],
				default => []
			}
		);

		$this->cardinalDirection = IntFromRawStateMap::string([
			Facing::NORTH => StringValues::MC_CARDINAL_DIRECTION_NORTH,
			Facing::SOUTH => StringValues::MC_CARDINAL_DIRECTION_SOUTH,
			Facing::WEST => StringValues::MC_CARDINAL_DIRECTION_WEST,
			Facing::EAST => StringValues::MC_CARDINAL_DIRECTION_EAST,
		]);
		$this->blockFace = IntFromRawStateMap::string([
			Facing::DOWN => StringValues::MC_BLOCK_FACE_DOWN,
			Facing::UP => StringValues::MC_BLOCK_FACE_UP,
			Facing::NORTH => StringValues::MC_BLOCK_FACE_NORTH,
			Facing::SOUTH => StringValues::MC_BLOCK_FACE_SOUTH,
			Facing::WEST => StringValues::MC_BLOCK_FACE_WEST,
			Facing::EAST => StringValues::MC_BLOCK_FACE_EAST,
		]);
		$this->pillarAxis = IntFromRawStateMap::string([
			Axis::X => StringValues::PILLAR_AXIS_X,
			Axis::Y => StringValues::PILLAR_AXIS_Y,
			Axis::Z => StringValues::PILLAR_AXIS_Z
		]);
		$this->torchFacing = IntFromRawStateMap::string([
			//TODO: horizontal directions are flipped (MCPE bug: https://bugs.mojang.com/browse/MCPE-152036)
			Facing::WEST => StringValues::TORCH_FACING_DIRECTION_EAST,
			Facing::SOUTH => StringValues::TORCH_FACING_DIRECTION_NORTH,
			Facing::NORTH => StringValues::TORCH_FACING_DIRECTION_SOUTH,
			Facing::UP => StringValues::TORCH_FACING_DIRECTION_TOP,
			Facing::EAST => StringValues::TORCH_FACING_DIRECTION_WEST,
		], deserializeAliases: [
			Facing::UP => StringValues::TORCH_FACING_DIRECTION_UNKNOWN //should be illegal, but still supported
		]);
		$this->portalAxis = IntFromRawStateMap::string([
			Axis::X => StringValues::PORTAL_AXIS_X,
			Axis::Z => StringValues::PORTAL_AXIS_Z,
		], deserializeAliases: [
			Axis::X => StringValues::PORTAL_AXIS_UNKNOWN,
		]);
		$this->bambooLeafSize = IntFromRawStateMap::string([
			Bamboo::NO_LEAVES => StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES,
			Bamboo::SMALL_LEAVES => StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES,
			Bamboo::LARGE_LEAVES => StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES,
		]);

		$this->horizontalFacing5Minus = IntFromRawStateMap::int([
			Facing::EAST => 0,
			Facing::WEST => 1,
			Facing::SOUTH => 2,
			Facing::NORTH => 3
		]);
		$this->horizontalFacingSWNE = IntFromRawStateMap::int([
			Facing::SOUTH => 0,
			Facing::WEST => 1,
			Facing::NORTH => 2,
			Facing::EAST => 3
		]);
		$this->horizontalFacingSWNEInverted = IntFromRawStateMap::int([
			Facing::NORTH => 0,
			Facing::EAST => 1,
			Facing::SOUTH => 2,
			Facing::WEST => 3,
		]);
		$this->horizontalFacingCoral = IntFromRawStateMap::int([
			Facing::WEST => 0,
			Facing::EAST => 1,
			Facing::NORTH => 2,
			Facing::SOUTH => 3
		]);
		$horizontalFacingClassicTable = [
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			Facing::WEST => 4,
			Facing::EAST => 5
		];
		$this->horizontalFacingClassic = IntFromRawStateMap::int($horizontalFacingClassicTable, deserializeAliases: [
			Facing::NORTH => [0, 1] //should be illegal but still technically possible
		]);

		$this->facing = IntFromRawStateMap::int([
			Facing::DOWN => 0,
			Facing::UP => 1
		] + $horizontalFacingClassicTable);

		//end rods have all the horizontal facing values opposite to classic facing
		$this->facingEndRod = IntFromRawStateMap::int([
			Facing::DOWN => 0,
			Facing::UP => 1,
			Facing::SOUTH => 2,
			Facing::NORTH => 3,
			Facing::EAST => 4,
			Facing::WEST => 5,
		]);

		$this->coralAxis = IntFromRawStateMap::int([
			Axis::X => 0,
			Axis::Z => 1,
		]);

		//TODO: shitty copy pasta job, we can do this better but this is good enough for now
		$this->facingExceptDown = IntFromRawStateMap::int(
			[Facing::UP => 1] + $horizontalFacingClassicTable,
			deserializeAliases: [Facing::UP => 0]);
		$this->facingExceptUp = IntFromRawStateMap::int(
			[Facing::DOWN => 0] + $horizontalFacingClassicTable,
			deserializeAliases: [Facing::DOWN => 1]
		);

		//In PM, we use Facing::UP to indicate that the stem is not attached to a pumpkin/melon, since this makes the
		//most intuitive sense (the stem is pointing at the sky). However, Bedrock uses the DOWN state for this, which
		//is absurd, and I refuse to make our API similarly absurd.
		$this->facingStem = IntFromRawStateMap::int(
			[Facing::UP => 0] + $horizontalFacingClassicTable,
			deserializeAliases: [Facing::UP => 1]
		);
	}
}
