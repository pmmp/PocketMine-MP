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

use pocketmine\block\tile\Furnace as TileFurnace;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\NormalHorizontalFacingInMetadataTrait;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\player\Player;
use function array_map;

class Furnace extends Opaque{
	use FacesOppositePlacingPlayerTrait;
	use NormalHorizontalFacingInMetadataTrait {
		readStateFromData as readFacingStateFromData;
	}

	protected BlockIdentifierFlattened $idInfoFlattened;

	protected bool $lit = false; //this is set based on the blockID
	/** @var int */
	private $remainingFuelTime = 0;
	/** @var int */
	private $cookTime = 0;
	/** @var int */
	private $maxFuelTime = 0;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->idInfoFlattened = $idInfo;
		parent::__construct($idInfo, $name, $breakInfo);
	}

	public function getId() : int{
		return $this->lit ? $this->idInfoFlattened->getSecondId() : parent::getId();
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->readFacingStateFromData($id, $stateMeta);
		$this->lit = $id === $this->idInfoFlattened->getSecondId();
	}

	public function readStateFromWorld() : void{
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileFurnace){
			$this->remainingFuelTime = $tile->getRemainingFuelTime();
			$this->cookTime = $tile->getCookTime();
			$this->maxFuelTime = $tile->getMaxFuelTime();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileFurnace){
			$tile->setRemainingFuelTime($this->remainingFuelTime);
			$tile->setCookTime($this->cookTime);
			$tile->setMaxFuelTime($this->maxFuelTime);
		}
	}

	public function getLightLevel() : int{
		return $this->lit ? 13 : 0;
	}

	public function isLit() : bool{
		return $this->lit;
	}

	/** @return $this */
	public function setLit(bool $lit = true) : self{
		$this->lit = $lit;
		return $this;
	}

	/** @return $this */
	public function setRemainingFuelTime(int $time) : self {
		$this->remainingFuelTime = $time;
		return $this;
	}

	public function getRemainingFuelTime() : int{
		return $this->remainingFuelTime;
	}

	/** @return $this */
	public function setMaxFuelTime(int $time) : self {
		$this->maxFuelTime = $time;
		return $this;
	}

	public function getMaxFuelTime() : int{
		return $this->maxFuelTime;
	}

	/** @return $this */
	public function setCookTime(int $time) : self {
		$this->cookTime = $time;
		return $this;
	}

	public function getCookTime() : int{
		return $this->cookTime;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$furnace = $this->position->getWorld()->getTile($this->position);
			if($furnace instanceof TileFurnace and $furnace->canOpenWith($item->getCustomName())){
				$player->setCurrentWindow($furnace->getInventory());
			}
		}

		return true;
	}

	protected function onStartSmelting() : void{
		if(!$this->isLit()){
			$this->setLit(true);
			$this->position->getWorld()->setBlock($this->getPosition(), $this);
		}
	}

	protected function onStopSmelting() : void{
		if($this->isLit()){
			$this->setLit(false);
			$this->remainingFuelTime = $this->cookTime = $this->maxFuelTime = 0;
			$this->position->getWorld()->setBlock($this->getPosition(), $this);
		}
	}

	protected function checkFuel(Item $fuel) : void{
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileFurnace && !$tile->isClosed()){
			$ev = new FurnaceBurnEvent($tile, $fuel, $fuel->getFuelTime());
			$ev->call();
			if($ev->isCancelled()){
				return;
			}

			$this->maxFuelTime = $this->remainingFuelTime = $ev->getBurnTime();
			$this->onStartSmelting();

			if($this->remainingFuelTime > 0 && $ev->isBurning()){
				$tile->getInventory()->setFuel($fuel->getFuelResidue());
			}
		}
	}

	public function onScheduledUpdate() : void{
		$furnace = $this->position->getWorld()->getTile($this->position);
		if($furnace instanceof TileFurnace && !$furnace->isClosed()){
			$inventory = $furnace->getInventory();

			$prevCookTime = $this->cookTime;
			$prevRemainingFuelTime = $this->remainingFuelTime;
			$prevMaxFuelTime = $this->maxFuelTime;

			$fuel = $inventory->getFuel();
			$raw = $inventory->getSmelting();
			$product = $inventory->getResult();

			$furnaceType = $furnace->getFurnaceType();
			$smelt = $this->position->getWorld()->getServer()->getCraftingManager()->getFurnaceRecipeManager($furnaceType)->match($raw);
			$canSmelt = ($smelt instanceof FurnaceRecipe and $raw->getCount() > 0 and (($smelt->getResult()->equals($product) and $product->getCount() < $product->getMaxStackSize()) or $product->isNull()));

			if($this->remainingFuelTime <= 0 and $canSmelt and $fuel->getFuelTime() > 0 and $fuel->getCount() > 0){
				$this->checkFuel($fuel);
			}

			if($this->remainingFuelTime > 0){
				--$this->remainingFuelTime;

				if($smelt instanceof FurnaceRecipe and $canSmelt){
					++$this->cookTime;

					if($this->cookTime >= $furnaceType->getCookDurationTicks()){
						$product = $smelt->getResult()->setCount($product->getCount() + 1);

						$ev = new FurnaceSmeltEvent($furnace, $raw, $product);
						$ev->call();

						if(!$ev->isCancelled()){
							$inventory->setResult($ev->getResult());
							$raw->pop();
							$inventory->setSmelting($raw);
						}

						$this->cookTime -= $furnaceType->getCookDurationTicks();
					}
					$this->position->getWorld()->setBlock($this->position, $this);
				}elseif($this->remainingFuelTime <= 0){
					$this->onStopSmelting();
				}else{
					$this->cookTime = 0;
					$this->position->getWorld()->setBlock($this->position, $this);
				}
				$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
			}else{
				$this->onStopSmelting();
			}

			$viewers = array_map(fn(Player $p) => $p->getNetworkSession()->getInvManager(), $inventory->getViewers());
			foreach($viewers as $v){
				if($v === null){
					continue;
				}
				if($prevCookTime !== $this->cookTime){
					$v->syncData($inventory, ContainerSetDataPacket::PROPERTY_FURNACE_SMELT_PROGRESS, $this->cookTime);
				}
				if($prevRemainingFuelTime !== $this->remainingFuelTime){
					$v->syncData($inventory, ContainerSetDataPacket::PROPERTY_FURNACE_REMAINING_FUEL_TIME, $this->remainingFuelTime);
				}
				if($prevMaxFuelTime !== $this->maxFuelTime){
					$v->syncData($inventory, ContainerSetDataPacket::PROPERTY_FURNACE_MAX_FUEL_TIME, $this->maxFuelTime);
				}
			}
		}
	}
}
