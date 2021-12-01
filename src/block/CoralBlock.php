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

use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\data\bedrock\CoralTypeIdMap;
use pocketmine\item\Item;
use function mt_rand;

final class CoralBlock extends Opaque{

	private CoralType $coralType;
	private bool $dead = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->coralType = CoralType::TUBE();
		parent::__construct($idInfo, $name, $breakInfo);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$coralType = CoralTypeIdMap::getInstance()->fromId($stateMeta & 0x7);
		if($coralType === null){
			throw new InvalidBlockStateException("No such coral type");
		}
		$this->coralType = $coralType;
		$this->dead = ($stateMeta & BlockLegacyMetadata::CORAL_BLOCK_FLAG_DEAD) !== 0;
	}

	protected function writeStateToMeta() : int{
		return ($this->dead ? BlockLegacyMetadata::CORAL_BLOCK_FLAG_DEAD : 0) | CoralTypeIdMap::getInstance()->toId($this->coralType);
	}

	protected function writeStateToItemMeta() : int{
		return $this->writeStateToMeta();
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getCoralType() : CoralType{ return $this->coralType; }

	/** @return $this */
	public function setCoralType(CoralType $coralType) : self{
		$this->coralType = $coralType;
		return $this;
	}

	public function isDead() : bool{ return $this->dead; }

	/** @return $this */
	public function setDead(bool $dead) : self{
		$this->dead = $dead;
		return $this;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->dead){
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, mt_rand(40, 200));
		}
	}

	public function onScheduledUpdate() : void{
		if(!$this->dead){
			$world = $this->position->getWorld();

			$hasWater = false;
			foreach($this->position->sides() as $vector3){
				if($world->getBlock($vector3) instanceof Water){
					$hasWater = true;
					break;
				}
			}
			if(!$hasWater){
				$world->setBlock($this->position, $this->setDead(true));
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [$this->setDead(true)->asItem()];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}
}
