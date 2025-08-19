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
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Stair;
use pocketmine\block\Torch;
use pocketmine\block\Trapdoor;
use pocketmine\block\utils\AnyFacing;
use pocketmine\block\utils\Colored;
use pocketmine\block\utils\CopperMaterial;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\CoralMaterial;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\PillarRotation;
use pocketmine\block\utils\SignLikeRotation;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues;
use pocketmine\data\bedrock\block\convert\property\BoolFromStringProperty;
use pocketmine\data\bedrock\block\convert\property\BoolProperty;
use pocketmine\data\bedrock\block\convert\property\DummyProperty;
use pocketmine\data\bedrock\block\convert\property\EnumProperty;
use pocketmine\data\bedrock\block\convert\property\HorizontalFacingProperty;
use pocketmine\data\bedrock\block\convert\property\HorizontalFacingReadTransform;
use pocketmine\data\bedrock\block\convert\property\IntFromIntProperty;
use pocketmine\data\bedrock\block\convert\property\IntFromStringProperty;
use pocketmine\data\bedrock\block\convert\property\IntProperty;
use pocketmine\data\bedrock\block\convert\property\Property;
use pocketmine\data\bedrock\block\convert\property\StringProperty;
use pocketmine\math\Facing;
use pocketmine\utils\SingletonTrait;

final class CommonProperties{
	use SingletonTrait;

	/** @phpstan-var IntFromStringProperty<Block&HorizontalFacing> */
	public readonly IntFromStringProperty $cardinalDirection;
	/** @phpstan-var IntFromStringProperty<Block&AnyFacing> */
	public readonly IntFromStringProperty $blockFace;
	/** @phpstan-var IntFromStringProperty<Block&PillarRotation> */
	public readonly IntFromStringProperty $pillarAxis;
	/** @phpstan-var IntFromStringProperty<Torch> */
	public readonly IntFromStringProperty $torchFacing;

	/** @phpstan-var IntFromIntProperty<Block&HorizontalFacing> */
	public readonly IntFromIntProperty $horizontalFacingSWNE;
	/** @phpstan-var IntFromIntProperty<Block&HorizontalFacing> */
	public readonly IntFromIntProperty $horizontalFacingSWNEInverted;
	/** @phpstan-var IntFromIntProperty<Block&HorizontalFacing> */
	public readonly IntFromIntProperty $horizontalFacingClassic;

	/** @phpstan-var IntFromIntProperty<Block&AnyFacing> */
	public readonly IntFromIntProperty $anyFacingClassic;

	/** @phpstan-var IntProperty<Block&SignLikeRotation> */
	public readonly IntProperty $floorSignLikeRotation;

	public readonly DummyProperty $dummyCardinalDirection;
	public readonly DummyProperty $dummyPillarAxis;

	/** @phpstan-var EnumProperty<Block&Colored, DyeColor> */
	public readonly EnumProperty $dyeColorIdInfix;

	/**
	 * @var StringProperty[]
	 * @phpstan-var non-empty-list<string|StringProperty<Block&CoralMaterial>>
	 */
	public readonly array $coralIdPrefixes;
	/**
	 * @var StringProperty[]
	 * @phpstan-var non-empty-list<string|StringProperty<Block&CopperMaterial>>
	 */
	public readonly array $copperIdPrefixes;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Button>>
	 */
	public readonly array $buttonProperties;

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
	 * @phpstan-var non-empty-list<Property<contravariant Stair>>
	 */
	public readonly array $stairProperties;

	/**
	 * @var Property[]
	 * @phpstan-var non-empty-list<Property<contravariant Trapdoor>>
	 */
	public readonly array $trapdoorProperties;

	private function __construct(){
		$vm = ValueMappings::getInstance();

		$this->cardinalDirection = new IntFromStringProperty(
			StateNames::MC_CARDINAL_DIRECTION,
			$vm->cardinalDirection,
			fn(Block&HorizontalFacing $b) => $b->getFacing(),
			fn(Block&HorizontalFacing $b, int $v) => $b->setFacing($v)
		);

		$this->blockFace = new IntFromStringProperty(
			StateNames::MC_BLOCK_FACE,
			$vm->blockFace,
			fn(Block&AnyFacing $b) => $b->getFacing(),
			fn(Block&AnyFacing $b, int $v) => $b->setFacing($v)
		);

		$this->pillarAxis = new IntFromStringProperty(
			StateNames::PILLAR_AXIS,
			$vm->pillarAxis,
			fn(Block&PillarRotation $b) => $b->getAxis(),
			fn(Block&PillarRotation $b, int $v) => $b->setAxis($v)
		);

		$this->torchFacing = new IntFromStringProperty(
			StateNames::TORCH_FACING_DIRECTION,
			$vm->torchFacing,
			fn(Torch $b) => $b->getFacing(),
			fn(Torch $b, int $v) => $b->setFacing($v)
		);

		$this->horizontalFacingSWNE = new IntFromIntProperty(
			StateNames::DIRECTION,
			$vm->horizontalFacingSWNE,
			fn(Block&HorizontalFacing $b) => $b->getFacing(),
			fn(Block&HorizontalFacing $b, int $v) => $b->setFacing($v)
		);
		//TODO: baking the inversion logic into the getters and setters is opaque and not good for programmatic analysis
		//we might want to set up a value mapping instead
		$this->horizontalFacingSWNEInverted = new IntFromIntProperty(
			StateNames::DIRECTION,
			$vm->horizontalFacingSWNE,
			fn(Block&HorizontalFacing $b) => Facing::opposite($b->getFacing()),
			fn(Block&HorizontalFacing $b, int $v) => $b->setFacing(Facing::opposite($v))
		);
		$this->horizontalFacingClassic = new IntFromIntProperty(
			StateNames::FACING_DIRECTION,
			$vm->horizontalFacingClassic,
			fn(Block&HorizontalFacing $b) => $b->getFacing(),
			fn(Block&HorizontalFacing $b, int $v) => $b->setFacing($v)
		);

		$this->anyFacingClassic = new IntFromIntProperty(
			StateNames::FACING_DIRECTION,
			$vm->facing,
			fn(Block&AnyFacing $b) => $b->getFacing(),
			fn(Block&AnyFacing $b, int $v) => $b->setFacing($v)
		);

		$this->floorSignLikeRotation = new IntProperty(StateNames::GROUND_SIGN_DIRECTION, 0, 15, fn(Block&SignLikeRotation $b) => $b->getRotation(), fn(Block&SignLikeRotation $b, int $v) => $b->setRotation($v));

		$this->dummyCardinalDirection = new DummyProperty(StateNames::MC_CARDINAL_DIRECTION, BlockStateStringValues::MC_CARDINAL_DIRECTION_SOUTH);
		$this->dummyPillarAxis = new DummyProperty(StateNames::PILLAR_AXIS, BlockStateStringValues::PILLAR_AXIS_Y);

		$this->dyeColorIdInfix = new EnumProperty("color", ValueMappings::getInstance()->dyeColor, fn(Block&Colored $b) => $b->getColor(), fn(Block&Colored $b, DyeColor $v) => $b->setColor($v));

		$this->coralIdPrefixes = [
			"minecraft:",
			new BoolFromStringProperty("dead", "", "dead_", fn(Block&CoralMaterial $b) => $b->isDead(), fn(Block&CoralMaterial $b, bool $v) => $b->setDead($v)),
			new EnumProperty("type", new EnumFromStringStateMap(CoralType::class, fn(CoralType $case) => match ($case) {
				CoralType::BRAIN => "brain",
				CoralType::BUBBLE => "bubble",
				CoralType::FIRE => "fire",
				CoralType::HORN => "horn",
				CoralType::TUBE => "tube"
			}), fn(Block&CoralMaterial $b) => $b->getCoralType(), fn(Block&CoralMaterial $b, CoralType $v) => $b->setCoralType($v)),
		];
		$this->copperIdPrefixes = [
			"minecraft:",
			new BoolFromStringProperty("waxed", "", "waxed_", fn(Block&CopperMaterial $b) => $b->isWaxed(), fn(Block&CopperMaterial $b, bool $v) => $b->setWaxed($v)),
			new EnumProperty("oxidation", new EnumFromStringStateMap(CopperOxidation::class, fn(CopperOxidation $case) => match ($case) {
				CopperOxidation::NONE => "",
				CopperOxidation::EXPOSED => "exposed_",
				CopperOxidation::WEATHERED => "weathered_",
				CopperOxidation::OXIDIZED => "oxidized_",
			}), fn(Block&CopperMaterial $b) => $b->getOxidation(), fn(Block&CopperMaterial $b, CopperOxidation $v) => $b->setOxidation($v))
		];

		$this->buttonProperties = [
			$this->anyFacingClassic,
			new BoolProperty(StateNames::BUTTON_PRESSED_BIT, fn(Button $b) => $b->isPressed(), fn(Button $b, bool $v) => $b->setPressed($v)),
		];

		//TODO: check if these need any special treatment to get the appropriate data to both halves of the door
		$this->doorProperties = [
			new BoolProperty(StateNames::UPPER_BLOCK_BIT, fn(Door $b) => $b->isTop(), fn(Door $b, bool $v) => $b->setTop($v)),
			new BoolProperty(StateNames::DOOR_HINGE_BIT, fn(Door $b) => $b->isHingeRight(), fn(Door $b, bool $v) => $b->setHingeRight($v)),
			new BoolProperty(StateNames::OPEN_BIT, fn(Door $b) => $b->isOpen(), fn(Door $b, bool $v) => $b->setOpen($v)),
			new HorizontalFacingProperty(
				StateNames::MC_CARDINAL_DIRECTION,
				ValueMappings::getInstance()->cardinalDirection,
				HorizontalFacingReadTransform::COUNTER_CLOCKWISE
			)
		];

		$this->fenceGateProperties = [
			new BoolProperty(StateNames::IN_WALL_BIT, fn(FenceGate $b) => $b->isInWall(), fn(FenceGate $b, bool $v) => $b->setInWall($v)),
			new BoolProperty(StateNames::OPEN_BIT, fn(FenceGate $b) => $b->isOpen(), fn(FenceGate $b, bool $v) => $b->setOpen($v)),
			$this->cardinalDirection,
		];

		$this->stairProperties = [
			new BoolProperty(StateNames::UPSIDE_DOWN_BIT, fn(Stair $b) => $b->isUpsideDown(), fn(Stair $b, bool $v) => $b->setUpsideDown($v)),
			new HorizontalFacingProperty(StateNames::WEIRDO_DIRECTION, ValueMappings::getInstance()->horizontalFacing5Minus)
		];

		$this->trapdoorProperties = [
			//this uses the same values as stairs, but the state is named differently
			new HorizontalFacingProperty(StateNames::DIRECTION, ValueMappings::getInstance()->horizontalFacing5Minus),

			new BoolProperty(StateNames::UPSIDE_DOWN_BIT, fn(Trapdoor $b) => $b->isTop(), fn(Trapdoor $b, bool $v) => $b->setTop($v)),
			new BoolProperty(StateNames::OPEN_BIT, fn(Trapdoor $b) => $b->isOpen(), fn(Trapdoor $b, bool $v) => $b->setOpen($v)),
		];
	}
}
