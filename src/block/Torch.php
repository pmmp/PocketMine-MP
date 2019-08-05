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

namespace pocketmine\block;

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Torch extends Flowable{

	/** @var int */
	protected $facing = Facing::UP;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? BlockBreakInfo::instant());
	}

	protected function writeStateToMeta() : int{
		return $this->facing === Facing::UP ? 5 : 6 - BlockDataSerializer::writeHorizontalFacing($this->facing);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = $stateMeta === 5 ? Facing::UP : BlockDataSerializer::readHorizontalFacing(6 - $stateMeta);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getLightLevel() : int{
		return 14;
	}

	public function onNearbyBlockChange() : void{
		$below = $this->getSide(Facing::DOWN);
		$face = Facing::opposite($this->facing);

		if($this->getSide($face)->isTransparent() and !($face === Facing::DOWN and ($below->getId() === BlockLegacyIds::FENCE or $below->getId() === BlockLegacyIds::COBBLESTONE_WALL))){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($blockClicked->canBeReplaced() and !$blockClicked->getSide(Facing::DOWN)->isTransparent()){
			$this->facing = Facing::UP;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}elseif($face !== Facing::DOWN and (!$blockClicked->isTransparent() or ($face === Facing::UP and ($blockClicked->getId() === BlockLegacyIds::FENCE or $blockClicked->getId() === BlockLegacyIds::COBBLESTONE_WALL)))){
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}else{
			static $faces = [
				Facing::SOUTH,
				Facing::WEST,
				Facing::NORTH,
				Facing::EAST,
				Facing::DOWN
			];
			foreach($faces as $side){
				$block = $this->getSide($side);
				if(!$block->isTransparent()){
					$this->facing = Facing::opposite($side);
					return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
				}
			}
		}
		return false;
	}
}
