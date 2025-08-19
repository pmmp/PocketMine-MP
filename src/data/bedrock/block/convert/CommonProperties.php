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
use pocketmine\block\Torch;
use pocketmine\block\utils\AnyFacing;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\PillarRotation;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues;
use pocketmine\data\bedrock\block\convert\property\DummyProperty;
use pocketmine\data\bedrock\block\convert\property\IntFromIntProperty;
use pocketmine\data\bedrock\block\convert\property\IntFromStringProperty;
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

	public readonly DummyProperty $dummyCardinalDirection;
	public readonly DummyProperty $dummyPillarAxis;

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

		$this->dummyCardinalDirection = new DummyProperty(StateNames::MC_CARDINAL_DIRECTION, BlockStateStringValues::MC_CARDINAL_DIRECTION_SOUTH);
		$this->dummyPillarAxis = new DummyProperty(StateNames::PILLAR_AXIS, BlockStateStringValues::PILLAR_AXIS_Y);
	}
}
