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

use pocketmine\block\Button;
use pocketmine\block\Door;
use pocketmine\block\DoublePlant;
use pocketmine\block\FenceGate;
use pocketmine\block\ItemFrame;
use pocketmine\block\Liquid;
use pocketmine\block\SimplePressurePlate;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Stem;
use pocketmine\block\Torch;
use pocketmine\block\Trapdoor;
use pocketmine\block\utils\Ageable;
use pocketmine\block\utils\AnalogRedstoneSignalEmitter;
use pocketmine\block\utils\AnyFacing;
use pocketmine\block\utils\Colored;
use pocketmine\block\utils\CopperMaterial;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\CoralMaterial;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\Lightable;
use pocketmine\block\utils\MultiFacing;
use pocketmine\block\utils\PillarRotation;
use pocketmine\block\utils\SignLikeRotation;
use pocketmine\block\utils\SlabType;
use pocketmine\block\Wall;
use pocketmine\block\Wood;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues;
use pocketmine\data\bedrock\block\convert\non;
use pocketmine\math\Facing;
use pocketmine\utils\SingletonTrait;

final class CommonProperties{
	use SingletonTrait;

	/** @phpstan-var ValueFromStringProperty<AnyFacing, int> */
	public readonly ValueFromStringProperty $blockFace;
	/** @phpstan-var ValueFromStringProperty<PillarRotation, int> */
	public readonly ValueFromStringProperty $pillarAxis;
	/** @phpstan-var ValueFromStringProperty<Torch, int> */
	public readonly ValueFromStringProperty $torchFacing;

	/** @phpstan-var ValueFromStringProperty<HorizontalFacing, int> */
	public readonly ValueFromStringProperty $horizontalFacingCardinal;
	/** @phpstan-var ValueFromIntProperty<HorizontalFacing, int> */
	public readonly ValueFromIntProperty $horizontalFacingSWNE;
	/** @phpstan-var ValueFromIntProperty<HorizontalFacing, int> */
	public readonly ValueFromIntProperty $horizontalFacingSWNEInverted;
	/** @phpstan-var ValueFromIntProperty<HorizontalFacing, int> */
	public readonly ValueFromIntProperty $horizontalFacingClassic;

	/** @phpstan-var ValueFromIntProperty<AnyFacing, int> */
	public readonly ValueFromIntProperty $anyFacingClassic;

	/** @phpstan-var OptionSetFromIntProperty<MultiFacing, int> */
	public readonly OptionSetFromIntProperty $multiFacingFlags;

	/** @phpstan-var IntProperty<SignLikeRotation> */
	public readonly IntProperty $floorSignLikeRotation;

	/** @phpstan-var IntProperty<AnalogRedstoneSignalEmitter> */
	public readonly IntProperty $analogRedstoneSignal;

	/** @phpstan-var IntProperty<Ageable> */
	public readonly IntProperty $cropAgeMax7;
	/** @phpstan-var BoolProperty<DoublePlant> */
	public readonly BoolProperty $doublePlantHalf;

	/** @phpstan-var IntProperty<Liquid> */
	public readonly IntProperty $liquidData;

	/** @phpstan-var BoolProperty<Lightable> */
	public readonly BoolProperty $lit;

	public readonly DummyProperty $dummyCardinalDirection;
	public readonly DummyProperty $dummyPillarAxis;

	/** @phpstan-var ValueFromStringProperty<Colored, DyeColor> */
	public readonly ValueFromStringProperty $dyeColorIdInfix;

	/** @phpstan-var BoolFromStringProperty<Lightable> */
	public readonly BoolFromStringProperty $litIdInfix;

	/** @phpstan-var BoolFromStringProperty<Slab> */
	public readonly BoolFromStringProperty $slabIdInfix;
	/** @phpstan-var BoolFromStringProperty<Slab> */
	public readonly BoolFromStringProperty $slabPositionProperty;

	/**
	 * @var StringProperty[]
	 * @phpstan-var non-empty-list<string|StringProperty<CoralMaterial>>
	 */
	public readonly array $coralIdPrefixes;
	/**
	 * @var StringProperty[]
	 * @phpstan-var non-empty-list<string|StringProperty<CopperMaterial>>
	 */
	public readonly array $copperIdPrefixes;

	/**
	 * @var StringProperty[]
	 * @phpstan-var non-empty-list<string|StringProperty<contravariant Lightable>>
	 */
	public readonly array $furnaceIdPrefixes;

	/**
	 * @var StringProperty[]|string[]
	 * @phpstan-var non-empty-list<string|StringProperty<contravariant Liquid>>
	 */
	public readonly array $liquidIdPrefixes;

	/**
	 * @var StringProperty[]
	 * @phpstan-var non-empty-list<string|StringProperty<contravariant Wood>>
	 */
	public readonly array $woodIdPrefixes;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Button>>
	 */
	public readonly array $buttonProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Lightable & HorizontalFacing>>
	 */
	public readonly array $campfireProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Door>>
	 */
	public readonly array $doorProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant FenceGate>>
	 */
	public readonly array $fenceGateProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant ItemFrame>>
	 */
	public readonly array $itemFrameProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant SimplePressurePlate>>
	 */
	public readonly array $simplePressurePlateProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Stair>>
	 */
	public readonly array $stairProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Stem>>
	 */
	public readonly array $stemProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Trapdoor>>
	 */
	public readonly array $trapdoorProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Wall>>
	 */
	public readonly array $wallProperties;

	private function __construct(){
		$vm = ValueMappings::getInstance();

		$hfGet = fn(HorizontalFacing $v) => $v->getFacing();
		$hfSet = fn(HorizontalFacing $v, int $facing) => $v->setFacing($facing);
		$this->horizontalFacingCardinal = new ValueFromStringProperty(StateNames::MC_CARDINAL_DIRECTION, $vm->cardinalDirection, $hfGet, $hfSet);

		$this->blockFace = new ValueFromStringProperty(
			StateNames::MC_BLOCK_FACE,
			$vm->blockFace,
			fn(AnyFacing $b) => $b->getFacing(),
			fn(AnyFacing $b, int $v) => $b->setFacing($v)
		);

		$this->pillarAxis = new ValueFromStringProperty(
			StateNames::PILLAR_AXIS,
			$vm->pillarAxis,
			fn(PillarRotation $b) => $b->getAxis(),
			fn(PillarRotation $b, int $v) => $b->setAxis($v)
		);

		$this->torchFacing = new ValueFromStringProperty(
			StateNames::TORCH_FACING_DIRECTION,
			$vm->torchFacing,
			fn(Torch $b) => $b->getFacing(),
			fn(Torch $b, int $v) => $b->setFacing($v)
		);

		$this->horizontalFacingSWNE = new ValueFromIntProperty(StateNames::DIRECTION, $vm->horizontalFacingSWNE, $hfGet, $hfSet);
		$this->horizontalFacingSWNEInverted = new ValueFromIntProperty(StateNames::DIRECTION, $vm->horizontalFacingSWNEInverted, $hfGet, $hfSet);
		$this->horizontalFacingClassic = new ValueFromIntProperty(StateNames::FACING_DIRECTION, $vm->horizontalFacingClassic, $hfGet, $hfSet);

		$this->anyFacingClassic = new ValueFromIntProperty(
			StateNames::FACING_DIRECTION,
			$vm->facing,
			fn(AnyFacing $b) => $b->getFacing(),
			fn(AnyFacing $b, int $v) => $b->setFacing($v)
		);

		$this->multiFacingFlags = new OptionSetFromIntProperty(
			StateNames::MULTI_FACE_DIRECTION_BITS,
			IntFromRawStateMap::int([
				Facing::DOWN => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_DOWN,
				Facing::UP => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_UP,
				Facing::NORTH => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_NORTH,
				Facing::SOUTH => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_SOUTH,
				Facing::WEST => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_WEST,
				Facing::EAST => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_EAST
			]),
			fn(MultiFacing $b) => $b->getFaces(),
			fn(MultiFacing $b, array $v) => $b->setFaces($v)
		);

		$this->floorSignLikeRotation = new IntProperty(StateNames::GROUND_SIGN_DIRECTION, 0, 15, fn(SignLikeRotation $b) => $b->getRotation(), fn(SignLikeRotation $b, int $v) => $b->setRotation($v));

		$this->analogRedstoneSignal = new IntProperty(StateNames::REDSTONE_SIGNAL, 0, 15, fn(AnalogRedstoneSignalEmitter $b) => $b->getOutputSignalStrength(), fn(AnalogRedstoneSignalEmitter $b, int $v) => $b->setOutputSignalStrength($v));

		$this->cropAgeMax7 = new IntProperty(StateNames::GROWTH, 0, 7, fn(Ageable $b) => $b->getAge(), fn(Ageable $b, int $v) => $b->setAge($v));
		$this->doublePlantHalf = new BoolProperty(StateNames::UPPER_BLOCK_BIT, fn(DoublePlant $b) => $b->isTop(), fn(DoublePlant $b, bool $v) => $b->setTop($v));

		$fallingFlag = BlockLegacyMetadata::LIQUID_FALLING_FLAG;
		$this->liquidData = new IntProperty(
			StateNames::LIQUID_DEPTH,
			0,
			15,
			fn(Liquid $b) => $b->getDecay() | ($b->isFalling() ? $fallingFlag : 0),
			fn(Liquid $b, int $v) => $b->setDecay($v & ~$fallingFlag)->setFalling(($v & $fallingFlag) !== 0)
		);

		$this->lit = new BoolProperty(StateNames::LIT, fn(Lightable $b) => $b->isLit(), fn(Lightable $b, bool $v) => $b->setLit($v));

		$this->dummyCardinalDirection = new DummyProperty(StateNames::MC_CARDINAL_DIRECTION, BlockStateStringValues::MC_CARDINAL_DIRECTION_SOUTH);
		$this->dummyPillarAxis = new DummyProperty(StateNames::PILLAR_AXIS, BlockStateStringValues::PILLAR_AXIS_Y);

		$this->dyeColorIdInfix = new ValueFromStringProperty("color", $vm->dyeColor, fn(Colored $b) => $b->getColor(), fn(Colored $b, DyeColor $v) => $b->setColor($v));
		$this->litIdInfix = new BoolFromStringProperty("lit", "", "lit_", fn(Lightable $b) => $b->isLit(), fn(Lightable $b, bool $v) => $b->setLit($v));

		$this->slabIdInfix = new BoolFromStringProperty(
			"double",
			"",
			"double_",
			fn(Slab $b) => $b->getSlabType() === SlabType::DOUBLE,

			//we don't know this is actually a bottom slab yet but we don't have enough information to set the
			//correct type in this handler
			//BOTTOM serves as a signal value for the state deserializer to decide whether to ignore the
			//upper_block_bit property
			fn(Slab $b, bool $v) => $b->setSlabType($v ? SlabType::DOUBLE : SlabType::BOTTOM)
		);
		$this->slabPositionProperty = new BoolFromStringProperty(
			StateNames::MC_VERTICAL_HALF,
			BlockStateStringValues::MC_VERTICAL_HALF_BOTTOM,
			BlockStateStringValues::MC_VERTICAL_HALF_TOP,
			fn(Slab $b) => $b->getSlabType() === SlabType::TOP,

			//Ignore the value for double slabs (should be set by ID component before this is reached)
			fn(Slab $b, bool $v) => $b->getSlabType() !== SlabType::DOUBLE ? $b->setSlabType($v ? SlabType::TOP : SlabType::BOTTOM) : null
		);

		$this->coralIdPrefixes = [
			"minecraft:",
			new BoolFromStringProperty("dead", "", "dead_", fn(CoralMaterial $b) => $b->isDead(), fn(CoralMaterial $b, bool $v) => $b->setDead($v)),
			new ValueFromStringProperty("type", EnumFromRawStateMap::string(CoralType::class, fn(CoralType $case) => match ($case) {
				CoralType::BRAIN => "brain",
				CoralType::BUBBLE => "bubble",
				CoralType::FIRE => "fire",
				CoralType::HORN => "horn",
				CoralType::TUBE => "tube"
			}), fn(CoralMaterial $b) => $b->getCoralType(), fn(CoralMaterial $b, CoralType $v) => $b->setCoralType($v)),
		];
		$this->copperIdPrefixes = [
			"minecraft:",
			new BoolFromStringProperty("waxed", "", "waxed_", fn(CopperMaterial $b) => $b->isWaxed(), fn(CopperMaterial $b, bool $v) => $b->setWaxed($v)),
			new ValueFromStringProperty("oxidation", EnumFromRawStateMap::string(CopperOxidation::class, fn(CopperOxidation $case) => match ($case) {
				CopperOxidation::NONE => "",
				CopperOxidation::EXPOSED => "exposed_",
				CopperOxidation::WEATHERED => "weathered_",
				CopperOxidation::OXIDIZED => "oxidized_",
			}), fn(CopperMaterial $b) => $b->getOxidation(), fn(CopperMaterial $b, CopperOxidation $v) => $b->setOxidation($v))
		];

		$this->furnaceIdPrefixes = ["minecraft:", $this->litIdInfix];

		$this->liquidIdPrefixes = [
			"minecraft:",
			new BoolFromStringProperty("still", "flowing_", "", fn(Liquid $b) => $b->isStill(), fn(Liquid $b, bool $v) => $b->setStill($v))
		];

		$this->woodIdPrefixes = [
			"minecraft:",
			new BoolFromStringProperty("stripped", "", "stripped_", fn(Wood $b) => $b->isStripped(), fn(Wood $b, bool $v) => $b->setStripped($v)),
		];

		$this->buttonProperties = [
			$this->anyFacingClassic,
			new BoolProperty(StateNames::BUTTON_PRESSED_BIT, fn(Button $b) => $b->isPressed(), fn(Button $b, bool $v) => $b->setPressed($v)),
		];

		$this->campfireProperties = [
			$this->horizontalFacingCardinal,
			new BoolProperty(StateNames::EXTINGUISHED, fn(Lightable $b) => $b->isLit(), fn(Lightable $b, bool $v) => $b->setLit($v), inverted: true),
		];

		//TODO: check if these need any special treatment to get the appropriate data to both halves of the door
		$this->doorProperties = [
			new BoolProperty(StateNames::UPPER_BLOCK_BIT, fn(Door $b) => $b->isTop(), fn(Door $b, bool $v) => $b->setTop($v)),
			new BoolProperty(StateNames::DOOR_HINGE_BIT, fn(Door $b) => $b->isHingeRight(), fn(Door $b, bool $v) => $b->setHingeRight($v)),
			new BoolProperty(StateNames::OPEN_BIT, fn(Door $b) => $b->isOpen(), fn(Door $b, bool $v) => $b->setOpen($v)),
			new ValueFromStringProperty(
				StateNames::MC_CARDINAL_DIRECTION,
				IntFromRawStateMap::string([
					//a door facing "east" is actually facing north - thanks mojang
					Facing::NORTH => BlockStateStringValues::MC_CARDINAL_DIRECTION_EAST,
					Facing::EAST => BlockStateStringValues::MC_CARDINAL_DIRECTION_SOUTH,
					Facing::SOUTH => BlockStateStringValues::MC_CARDINAL_DIRECTION_WEST,
					Facing::WEST => BlockStateStringValues::MC_CARDINAL_DIRECTION_NORTH
				]),
				fn(HorizontalFacing $b) => $b->getFacing(),
				fn(HorizontalFacing $b, int $v) => $b->setFacing($v)
			)
		];

		$this->fenceGateProperties = [
			new BoolProperty(StateNames::IN_WALL_BIT, fn(FenceGate $b) => $b->isInWall(), fn(FenceGate $b, bool $v) => $b->setInWall($v)),
			new BoolProperty(StateNames::OPEN_BIT, fn(FenceGate $b) => $b->isOpen(), fn(FenceGate $b, bool $v) => $b->setOpen($v)),
			$this->horizontalFacingCardinal,
		];

		$this->itemFrameProperties = [
			new DummyProperty(StateNames::ITEM_FRAME_PHOTO_BIT, false), //TODO: not sure what the point of this is
			new BoolProperty(StateNames::ITEM_FRAME_MAP_BIT, fn(ItemFrame $b) => $b->hasMap(), fn(ItemFrame $b, bool $v) => $b->setHasMap($v)),
			$this->anyFacingClassic
		];

		$this->simplePressurePlateProperties = [
			//TODO: not sure what the deal is here ... seems like a mojang bug / artifact of bad implementation?
			//best to keep this separate from weighted plates anyway...
			new IntProperty(
				StateNames::REDSTONE_SIGNAL,
				0,
				15,
				fn(SimplePressurePlate $b) => $b->isPressed() ? 15 : 0,
				fn(SimplePressurePlate $b, int $v) => $b->setPressed($v !== 0)
			)
		];

		$this->stairProperties = [
			new BoolProperty(StateNames::UPSIDE_DOWN_BIT, fn(Stair $b) => $b->isUpsideDown(), fn(Stair $b, bool $v) => $b->setUpsideDown($v)),
			new ValueFromIntProperty(StateNames::WEIRDO_DIRECTION, $vm->horizontalFacing5Minus, $hfGet, $hfSet),
		];

		$this->stemProperties = [
			new ValueFromIntProperty(StateNames::FACING_DIRECTION, $vm->facingStem, fn(Stem $b) => $b->getFacing(), fn(Stem $b, int $v) => $b->setFacing($v)),
			$this->cropAgeMax7
		];

		$this->trapdoorProperties = [
			//this uses the same values as stairs, but the state is named differently
			new ValueFromIntProperty(StateNames::DIRECTION, $vm->horizontalFacing5Minus, $hfGet, $hfSet),

			new BoolProperty(StateNames::UPSIDE_DOWN_BIT, fn(Trapdoor $b) => $b->isTop(), fn(Trapdoor $b, bool $v) => $b->setTop($v)),
			new BoolProperty(StateNames::OPEN_BIT, fn(Trapdoor $b) => $b->isOpen(), fn(Trapdoor $b, bool $v) => $b->setOpen($v)),
		];

		$wallProperties = [
			new BoolProperty(StateNames::WALL_POST_BIT, fn(Wall $b) => $b->isPost(), fn(Wall $b, bool $v) => $b->setPost($v)),
		];
		foreach([
			Facing::NORTH => StateNames::WALL_CONNECTION_TYPE_NORTH,
			Facing::SOUTH => StateNames::WALL_CONNECTION_TYPE_SOUTH,
			Facing::WEST => StateNames::WALL_CONNECTION_TYPE_WEST,
			Facing::EAST => StateNames::WALL_CONNECTION_TYPE_EAST
		] as $facing => $stateName){
			$wallProperties[] = new ValueFromStringProperty(
				$stateName,
				EnumFromRawStateMap::string(WallConnectionTypeShim::class, fn(WallConnectionTypeShim $case) => $case->getValue()),
				fn(Wall $b) => WallConnectionTypeShim::serialize($b->getConnection($facing)),
				fn(Wall $b, WallConnectionTypeShim $v) => $b->setConnection($facing, $v->deserialize())
			);
		}
		$this->wallProperties = $wallProperties;
	}
}
