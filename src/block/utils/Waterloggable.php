<?php

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\Water;

interface Waterloggable{

	public function getContainedWater() : ?Water;

	/**
	 * @return $this
	 */
	public function setContainedWater(?Water $waterCover) : self;

	public function liquidCollide(Block $cause, Block $result) : bool;

	/**
	 * Returns whether block can be waterlogged at the current moment.
	 * For example, double slabs can't be waterlogged, while single can.
	 */
	public function canBeWaterlogged() : bool;

	/** Returns whether water can flow through the given side. */
	public function isSideOpenToFlow(int $face) : bool;
}