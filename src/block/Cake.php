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
use pocketmine\block\utils\SupportType;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\FoodSource;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Cake extends Transparent implements FoodSource{
	public const MAX_BITES = 6;

	protected int $bites = 0;

	protected function writeStateToMeta() : int{
		return $this->bites;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->bites = BlockDataSerializer::readBoundedInt("bites", $stateMeta, 0, self::MAX_BITES);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [
			AxisAlignedBB::one()
				->contract(1 / 16, 0, 1 / 16)
				->trim(Facing::UP, 0.5)
				->trim(Facing::WEST, $this->bites / 8)
		];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function getBites() : int{ return $this->bites; }

	/** @return $this */
	public function setBites(int $bites) : self{
		if($bites < 0 || $bites > self::MAX_BITES){
			throw new \InvalidArgumentException("Bites must be in range 0 ... " . self::MAX_BITES);
		}
		$this->bites = $bites;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() !== BlockLegacyIds::AIR){
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->getId() === BlockLegacyIds::AIR){ //Replace with common break method
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			return $player->consumeObject($this);
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
		if($clone->bites > self::MAX_BITES){
			$clone = VanillaBlocks::AIR();
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
		$this->position->getWorld()->setBlock($this->position, $this->getResidue());
	}
}
