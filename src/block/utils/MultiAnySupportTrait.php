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
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function array_key_first;
use function count;

/**
 * Used by blocks that have multiple support requirements in the area of one solid block, such as covering three sides of a corner.
 * Prevents placement if support isn't available and automatically destroys a block side if it's support is removed.
 */
trait MultiAnySupportTrait{
	use MultiAnyFacingTrait;

	/**
	 * Returns a list of faces that block should already have when placed.
	 *
	 * @return int[]
	 */
	abstract protected function getInitialPlaceFaces(Block $blockReplace) : array;

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->faces = $this->getInitialPlaceFaces($blockReplace);
		$availableFaces = $this->getAvailableFaces();

		if(count($availableFaces) === 0){
			return false;
		}

		$opposite = Facing::opposite($face);
		$placedFace = isset($availableFaces[$opposite]) ? $opposite : array_key_first($availableFaces);
		$this->faces[$placedFace] = $placedFace;

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$changed = false;

		foreach($this->faces as $face){
			if($this->getAdjacentSupportType($face) !== SupportType::FULL){
				unset($this->faces[$face]);
				$changed = true;
			}
		}

		if($changed){
			$world = $this->position->getWorld();
			if(count($this->faces) === 0){
				$world->useBreakOn($this->position);
			}else{
				$world->setBlock($this->position, $this);
			}
		}
	}

	/**
	 * @return array<int, int> $faces
	 */
	private function getAvailableFaces() : array{
		$faces = [];
		foreach(Facing::ALL as $face){
			if(!$this->hasFace($face) && $this->getAdjacentSupportType($face) === SupportType::FULL){
				$faces[$face] = $face;
			}
		}
		return $faces;
	}
}
