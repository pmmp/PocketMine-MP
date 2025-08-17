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

use pocketmine\block\Block;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\math\Facing;

/**
 * @phpstan-implements Property<Block&HorizontalFacing>
 */
abstract class BaseHorizontalFacingProperty implements Property{

	public function __construct(
		private HorizontalFacingReadTransform $readTransform = HorizontalFacingReadTransform::NONE
	){}

	abstract protected function read(BlockStateReader $in) : int;

	abstract protected function write(BlockStateWriter $out, int $value) : void;

	public function deserialize(Block $block, BlockStateReader $in) : void{
		$value = $this->read($in);
		$transformed = match($this->readTransform){
			HorizontalFacingReadTransform::NONE => $value,
			HorizontalFacingReadTransform::OPPOSITE => Facing::opposite($value),
			default => Facing::rotateY($value, $this->readTransform === HorizontalFacingReadTransform::CLOCKWISE),
		};
		$block->setFacing($transformed);
	}

	public function serialize(Block $block, BlockStateWriter $out) : void{
		$value = $block->getFacing();
		$transformed = match($this->readTransform){
			HorizontalFacingReadTransform::NONE => $value,
			HorizontalFacingReadTransform::OPPOSITE => Facing::opposite($value),
			default => Facing::rotateY($value, $this->readTransform !== HorizontalFacingReadTransform::CLOCKWISE) // Reverse the rotation for serialization
		};
		$this->write($out, $transformed);
	}
}
