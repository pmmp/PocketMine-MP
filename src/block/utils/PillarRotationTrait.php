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

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

trait PillarRotationTrait{

	/** @var int */
	protected $axis = Facing::AXIS_Y;

	/**
	 * @see Block::writeStateToMeta()
	 * @return int
	 */
	protected function writeStateToMeta() : int{
		return $this->writeAxisToMeta();
	}

	/**
	 * @see Block::readStateFromMeta()
	 * @param int $meta
	 */
	public function readStateFromMeta(int $meta) : void{
		$this->readAxisFromMeta($meta);
	}

	/**
	 * @see Block::getStateBitmask()
	 * @return int
	 */
	public function getStateBitmask() : int{
		return 0b1100;
	}

	protected function readAxisFromMeta(int $meta) : void{
		static $map = [
			0 => Facing::AXIS_Y,
			1 => Facing::AXIS_X,
			2 => Facing::AXIS_Z,
			3 => Facing::AXIS_Y //TODO: how to deal with all-bark logs?
		];
		$this->axis = $map[$meta >> 2];
	}

	protected function writeAxisToMeta() : int{
		static $bits = [
			Facing::AXIS_Y => 0,
			Facing::AXIS_Z => 2,
			Facing::AXIS_X => 1
		];
		return $bits[$this->axis] << 2;
	}

	/**
	 * @see Block::place()
	 *
	 * @param Item        $item
	 * @param Block       $blockReplace
	 * @param Block       $blockClicked
	 * @param int         $face
	 * @param Vector3     $clickVector
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$this->axis = Facing::axis($face);
		/** @see Block::place() */
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}
