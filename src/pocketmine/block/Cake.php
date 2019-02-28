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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\item\FoodSource;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Cake extends Transparent implements FoodSource{

	/** @var int */
	protected $bites = 0;

	protected function writeStateToMeta() : int{
		return $this->bites;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->bites = BlockDataValidator::readBoundedInt("bites", $stateMeta, 0, 6);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getHardness() : float{
		return 0.5;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()
			->contract(1 / 16, 0, 1 / 16)
			->trim(Facing::UP, 0.5)
			->trim(Facing::WEST, $this->bites / 8);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() !== self::AIR){
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->getId() === self::AIR){ //Replace with common break method
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$player->consumeObject($this);
			return true;
		}

		return false;
	}

	public function getFoodRestore() : int{
		return 2;
	}

	public function getSaturationRestore() : float{
		return 0.4;
	}

	public function requiresHunger() : bool{
		return true;
	}

	/**
	 * @return Block
	 */
	public function getResidue(){
		$clone = clone $this;
		$clone->bites++;
		if($clone->bites > 6){
			$clone = BlockFactory::get(Block::AIR);
		}
		return $clone;
	}

	/**
	 * @return EffectInstance[]
	 */
	public function getAdditionalEffects() : array{
		return [];
	}

	public function onConsume(Living $consumer) : void{
		$this->level->setBlock($this, $this->getResidue());
	}
}
