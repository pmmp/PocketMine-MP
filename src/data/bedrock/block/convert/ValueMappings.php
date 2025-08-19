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
use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DripleafState;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MobHeadType;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\utils\SingletonTrait;

final class ValueMappings{
	use SingletonTrait; //???

	/** @var EnumFromStringStateMap<DyeColor> */
	public readonly EnumFromStringStateMap $dyeColor;
	/** @var EnumFromStringStateMap<DyeColor> */
	public readonly EnumFromStringStateMap $dyeColorWithSilver;
	/** @var EnumFromStringStateMap<MobHeadType> */
	public readonly EnumFromStringStateMap $mobHeadType;
	/** @var EnumFromStringStateMap<FroglightType> */
	public readonly EnumFromStringStateMap $froglightType;
	/** @var EnumFromStringStateMap<DirtType> */
	public readonly EnumFromStringStateMap $dirtType;

	/** @var EnumFromStringStateMap<DripleafState> */
	public readonly EnumFromStringStateMap $dripleafState;
	/** @var EnumFromStringStateMap<BellAttachmentType> */
	public readonly EnumFromStringStateMap $bellAttachmentType;
	/** @var EnumFromStringStateMap<LeverFacing> */
	public readonly EnumFromStringStateMap $leverFacing;

	public readonly IntFromStringStateMap $cardinalDirection;
	public readonly IntFromStringStateMap $blockFace;
	public readonly IntFromStringStateMap $pillarAxis;
	public readonly IntFromStringStateMap $torchFacing;
	public readonly IntFromStringStateMap $portalAxis;
	public readonly IntFromStringStateMap $bambooLeafSize;

	public readonly IntFromIntStateMap $horizontalFacing5Minus;
	public readonly IntFromIntStateMap $horizontalFacingSWNE;
	public readonly IntFromIntStateMap $horizontalFacingCoral;
	public readonly IntFromIntStateMap $horizontalFacingClassic;
	public readonly IntFromIntStateMap $facing;
	public readonly IntFromIntStateMap $coralAxis;

	public readonly IntFromIntStateMap $facingExceptDown;
	public readonly IntFromIntStateMap $facingExceptUp;

	public function __construct(){
		//flattened ID components - we can't generate constants for these
		$this->dyeColor = new EnumFromStringStateMap(DyeColor::class, fn(DyeColor $case) => match ($case) {
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
		$this->dyeColorWithSilver = new EnumFromStringStateMap(DyeColor::class, fn(DyeColor $case) => match ($case) {
			DyeColor::LIGHT_GRAY => "silver",
			default => $this->dyeColor->enumToValue($case)
		});

		$this->mobHeadType = new EnumFromStringStateMap(MobHeadType::class, fn(MobHeadType $case) => match ($case) {
			MobHeadType::CREEPER => Ids::CREEPER_HEAD,
			MobHeadType::DRAGON => Ids::DRAGON_HEAD,
			MobHeadType::PIGLIN => Ids::PIGLIN_HEAD,
			MobHeadType::PLAYER => Ids::PLAYER_HEAD,
			MobHeadType::SKELETON => Ids::SKELETON_SKULL,
			MobHeadType::WITHER_SKELETON => Ids::WITHER_SKELETON_SKULL,
			MobHeadType::ZOMBIE => Ids::ZOMBIE_HEAD
		});
		$this->froglightType = new EnumFromStringStateMap(FroglightType::class, fn(FroglightType $case) => match ($case) {
			FroglightType::OCHRE => Ids::OCHRE_FROGLIGHT,
			FroglightType::PEARLESCENT => Ids::PEARLESCENT_FROGLIGHT,
			FroglightType::VERDANT => Ids::VERDANT_FROGLIGHT,
		});
		$this->dirtType = new EnumFromStringStateMap(DirtType::class, fn(DirtType $case) => match ($case) {
			DirtType::NORMAL => Ids::DIRT,
			DirtType::COARSE => Ids::COARSE_DIRT,
			DirtType::ROOTED => Ids::DIRT_WITH_ROOTS,
		});

		//state value mappings
		$this->dripleafState = new EnumFromStringStateMap(DripleafState::class, fn(DripleafState $case) => match ($case) {
			DripleafState::STABLE => StringValues::BIG_DRIPLEAF_TILT_NONE,
			DripleafState::UNSTABLE => StringValues::BIG_DRIPLEAF_TILT_UNSTABLE,
			DripleafState::PARTIAL_TILT => StringValues::BIG_DRIPLEAF_TILT_PARTIAL_TILT,
			DripleafState::FULL_TILT => StringValues::BIG_DRIPLEAF_TILT_FULL_TILT
		});
		$this->bellAttachmentType = new EnumFromStringStateMap(BellAttachmentType::class, fn(BellAttachmentType $case) => match ($case) {
			BellAttachmentType::FLOOR => StringValues::ATTACHMENT_STANDING,
			BellAttachmentType::CEILING => StringValues::ATTACHMENT_HANGING,
			BellAttachmentType::ONE_WALL => StringValues::ATTACHMENT_SIDE,
			BellAttachmentType::TWO_WALLS => StringValues::ATTACHMENT_MULTIPLE,
		});
		$this->leverFacing = new EnumFromStringStateMap(LeverFacing::class, fn(LeverFacing $case) => match ($case) {
			LeverFacing::DOWN_AXIS_Z => StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH,
			LeverFacing::DOWN_AXIS_X => StringValues::LEVER_DIRECTION_DOWN_EAST_WEST,
			LeverFacing::UP_AXIS_Z => StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH,
			LeverFacing::UP_AXIS_X => StringValues::LEVER_DIRECTION_UP_EAST_WEST,
			LeverFacing::NORTH => StringValues::LEVER_DIRECTION_NORTH,
			LeverFacing::SOUTH => StringValues::LEVER_DIRECTION_SOUTH,
			LeverFacing::WEST => StringValues::LEVER_DIRECTION_WEST,
			LeverFacing::EAST => StringValues::LEVER_DIRECTION_EAST
		});

		$this->cardinalDirection = new IntFromStringStateMap([
			Facing::NORTH => StringValues::MC_CARDINAL_DIRECTION_NORTH,
			Facing::SOUTH => StringValues::MC_CARDINAL_DIRECTION_SOUTH,
			Facing::WEST => StringValues::MC_CARDINAL_DIRECTION_WEST,
			Facing::EAST => StringValues::MC_CARDINAL_DIRECTION_EAST,
		]);
		$this->blockFace = new IntFromStringStateMap([
			Facing::DOWN => StringValues::MC_BLOCK_FACE_DOWN,
			Facing::UP => StringValues::MC_BLOCK_FACE_UP,
			Facing::NORTH => StringValues::MC_BLOCK_FACE_NORTH,
			Facing::SOUTH => StringValues::MC_BLOCK_FACE_SOUTH,
			Facing::WEST => StringValues::MC_BLOCK_FACE_WEST,
			Facing::EAST => StringValues::MC_BLOCK_FACE_EAST,
		]);
		$this->pillarAxis = new IntFromStringStateMap([
			Axis::X => StringValues::PILLAR_AXIS_X,
			Axis::Y => StringValues::PILLAR_AXIS_Y,
			Axis::Z => StringValues::PILLAR_AXIS_Z
		]);
		$this->torchFacing = new IntFromStringStateMap([
			//TODO: horizontal directions are flipped (MCPE bug: https://bugs.mojang.com/browse/MCPE-152036)
			Facing::WEST => StringValues::TORCH_FACING_DIRECTION_EAST,
			Facing::SOUTH => StringValues::TORCH_FACING_DIRECTION_NORTH,
			Facing::NORTH => StringValues::TORCH_FACING_DIRECTION_SOUTH,
			Facing::UP => StringValues::TORCH_FACING_DIRECTION_TOP,
			Facing::EAST => StringValues::TORCH_FACING_DIRECTION_WEST,
		], deserializeAliases: [
			Facing::UP => StringValues::TORCH_FACING_DIRECTION_UNKNOWN //should be illegal, but still supported
		]);
		$this->portalAxis = new IntFromStringStateMap([
			Axis::X => StringValues::PORTAL_AXIS_X,
			Axis::Z => StringValues::PORTAL_AXIS_Z,
		], deserializeAliases: [
			Axis::X => StringValues::PORTAL_AXIS_UNKNOWN,
		]);
		$this->bambooLeafSize = new IntFromStringStateMap([
			Bamboo::NO_LEAVES => StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES,
			Bamboo::SMALL_LEAVES => StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES,
			Bamboo::LARGE_LEAVES => StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES,
		]);

		$this->horizontalFacing5Minus = new IntFromIntStateMap([
			Facing::EAST => 0,
			Facing::WEST => 1,
			Facing::SOUTH => 2,
			Facing::NORTH => 3
		]);
		$this->horizontalFacingSWNE = new IntFromIntStateMap([
			Facing::SOUTH => 0,
			Facing::WEST => 1,
			Facing::NORTH => 2,
			Facing::EAST => 3
		]);
		$this->horizontalFacingCoral = new IntFromIntStateMap([
			Facing::WEST => 0,
			Facing::EAST => 1,
			Facing::NORTH => 2,
			Facing::SOUTH => 3
		]);
		$this->horizontalFacingClassic = new IntFromIntStateMap([
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			Facing::WEST => 4,
			Facing::EAST => 5
		], deserializeAliases: [
			Facing::NORTH => [0, 1] //should be illegal but still technically possible
		]);

		$this->facing = new IntFromIntStateMap([
			Facing::DOWN => 0,
			Facing::UP => 1,
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			Facing::WEST => 4,
			Facing::EAST => 5
		]);
		$this->coralAxis = new IntFromIntStateMap([
			Axis::X => 0,
			Axis::Z => 1,
		]);

		//TODO: shitty copy pasta job, we can do this better but this is good enough for now
		$this->facingExceptDown = new IntFromIntStateMap([
			Facing::UP => 1,
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			Facing::WEST => 4,
			Facing::EAST => 5
		], deserializeAliases: [
			Facing::UP => 0
		]);
		$this->facingExceptUp = new IntFromIntStateMap([
			Facing::DOWN => 0,
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			Facing::WEST => 4,
			Facing::EAST => 5
		], deserializeAliases: [
			Facing::DOWN => 1
		]);
	}
}
