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

namespace pocketmine\block\utils;

use pocketmine\math\Axis;

trait PillarRotationInMetadataTrait{
	use PillarRotationTrait;

	protected function getAxisMetaShift() : int{
		return 2; //default
	}

	/**
	 * @see Block::writeStateToMeta()
	 */
	protected function writeStateToMeta() : int{
		return $this->writeAxisToMeta();
	}

	/**
	 * @see Block::readStateFromData()
	 */
	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->readAxisFromMeta($stateMeta);
	}

	/**
	 * @see Block::getStateBitmask()
	 */
	public function getStateBitmask() : int{
		return 0b11 << $this->getAxisMetaShift();
	}

	protected function readAxisFromMeta(int $meta) : void{
		$axis = $meta >> $this->getAxisMetaShift();
		$mapped = [
			0 => Axis::Y,
			1 => Axis::X,
			2 => Axis::Z
		][$axis] ?? null;
		if($mapped === null){
			throw new InvalidBlockStateException("Invalid axis meta $axis");
		}
		$this->axis = $mapped;
	}

	protected function writeAxisToMeta() : int{
		return [
			Axis::Y => 0,
			Axis::Z => 2,
			Axis::X => 1
		][$this->axis] << $this->getAxisMetaShift();
	}
}
