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

use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\block\utils\LeavesType;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\World;
use function mt_rand;

class Leaves extends Transparent{
	private const MAX_LOG_DISTANCE = 4;

	protected LeavesType $leavesType; //immutable for now
	protected bool $noDecay = false;
	protected bool $checkDecay = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, LeavesType $leavesType){
		parent::__construct($idInfo, $name, $typeInfo);
		$this->leavesType = $leavesType;
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->bool($this->noDecay);
		$w->bool($this->checkDecay);
	}

	public function getLeavesType() : LeavesType{ return $this->leavesType; }

	public function isNoDecay() : bool{ return $this->noDecay; }

	/** @return $this */
	public function setNoDecay(bool $noDecay) : self{
		$this->noDecay = $noDecay;
		return $this;
	}

	public function isCheckDecay() : bool{ return $this->checkDecay; }

	/** @return $this */
	public function setCheckDecay(bool $checkDecay) : self{
		$this->checkDecay = $checkDecay;
		return $this;
	}

	public function blocksDirectSkyLight() : bool{
		return true;
	}

	/**
	 * @param true[] $visited reference parameter
	 * @phpstan-param array<int, true> $visited
	 * @phpstan-param-out array<int, true> $visited
	 */
	protected function findLog(Vector3 $pos, array &$visited = [], int $distance = 0) : bool{
		$index = World::blockHash($pos->x, $pos->y, $pos->z);
		if(isset($visited[$index])){
			return false;
		}
		$visited[$index] = true;

		$block = $this->position->getWorld()->getBlock($pos);
		if($block instanceof Wood){ //type doesn't matter
			return true;
		}

		if($block instanceof Leaves && $distance <= self::MAX_LOG_DISTANCE){
			foreach(Facing::ALL as $side){
				if($this->findLog($pos->getSide($side), $visited, $distance + 1)){
					return true;
				}
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->noDecay && !$this->checkDecay){
			$this->checkDecay = true;
			$this->position->getWorld()->setBlock($this->position, $this, false);
		}
	}

	public function ticksRandomly() : bool{
		return !$this->noDecay && $this->checkDecay;
	}

	public function onRandomTick() : void{
		if(!$this->noDecay && $this->checkDecay){
			$cancelled = false;
			if(LeavesDecayEvent::hasHandlers()){
				$ev = new LeavesDecayEvent($this);
				$ev->call();
				$cancelled = $ev->isCancelled();
			}

			$world = $this->position->getWorld();
			if($cancelled || $this->findLog($this->position)){
				$this->checkDecay = false;
				$world->setBlock($this->position, $this, false);
			}else{
				$world->useBreakOn($this->position);
			}
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->noDecay = true; //artificial leaves don't decay
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if(($item->getBlockToolType() & BlockToolType::SHEARS) !== 0){
			return parent::getDropsForCompatibleTool($item);
		}

		$drops = [];
		if(FortuneDropHelper::bonusChanceDivisor($item, 20, 4)){ //Saplings
			// TODO: according to the wiki, the jungle saplings have a different drop rate
			$sapling = (match($this->leavesType){
				LeavesType::ACACIA => VanillaBlocks::ACACIA_SAPLING(),
				LeavesType::BIRCH => VanillaBlocks::BIRCH_SAPLING(),
				LeavesType::DARK_OAK => VanillaBlocks::DARK_OAK_SAPLING(),
				LeavesType::JUNGLE => VanillaBlocks::JUNGLE_SAPLING(),
				LeavesType::OAK => VanillaBlocks::OAK_SAPLING(),
				LeavesType::SPRUCE => VanillaBlocks::SPRUCE_SAPLING(),
				LeavesType::MANGROVE, //TODO: mangrove propagule
				LeavesType::AZALEA, LeavesType::FLOWERING_AZALEA => null, //TODO: azalea
				LeavesType::CHERRY => null, //TODO: cherry
			})?->asItem();
			if($sapling !== null){
				$drops[] = $sapling;
			}
		}
		if(
			($this->leavesType === LeavesType::OAK || $this->leavesType === LeavesType::DARK_OAK) &&
			FortuneDropHelper::bonusChanceDivisor($item, 200, 20)
		){ //Apples
			$drops[] = VanillaItems::APPLE();
		}
		if(FortuneDropHelper::bonusChanceDivisor($item, 50, 5)){
			$drops[] = VanillaItems::STICK()->setCount(mt_rand(1, 2));
		}

		return $drops;
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function getFlameEncouragement() : int{
		return 30;
	}

	public function getFlammability() : int{
		return 60;
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}
}
