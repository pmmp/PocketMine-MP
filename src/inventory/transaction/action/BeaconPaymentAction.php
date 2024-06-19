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

namespace pocketmine\inventory\transaction\action;

use pocketmine\block\Beacon;
use pocketmine\block\inventory\BeaconInventory;
use pocketmine\entity\effect\Effect;
use pocketmine\event\block\BeaconActivateEvent;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use function assert;
use function in_array;

/**
 * Represents an action involving a beacon consuming input to produce effects.
 */
class BeaconPaymentAction extends InventoryAction{

	public function __construct(
		protected BeaconInventory $inventory,
		protected Effect $primaryEffect,
		protected ?Effect $secondaryEffect
	){
		parent::__construct(VanillaItems::AIR(), VanillaItems::AIR());
	}

	/**
	 * Returns the beacon inventory involved in this action.
	 */
	public function getInventory() : BeaconInventory{
		return $this->inventory;
	}

	public function validate(Player $source) : void{
		$input = $this->inventory->getInput();
		if(!isset(Beacon::ALLOWED_ITEM_IDS[$input->getTypeId()]) || $input->getCount() < 1){
			throw new TransactionValidationException("Invalid input item");
		}

		$position = $this->inventory->getHolder();
		$block = $position->getWorld()->getBlock($position);
		if (!$block instanceof Beacon) {
			throw new TransactionValidationException("Target block is not a beacon");
		}

		$allowedEffects = $block->getAllowedEffect($block->getBeaconLevel());
		if(!in_array($this->primaryEffect, $allowedEffects, true)){
			throw new TransactionValidationException("Primary effect provided is not allowed");
		}
		if($this->secondaryEffect !== null && !in_array($this->secondaryEffect, $allowedEffects, true)){
			throw new TransactionValidationException("Secondary effect provided is not allowed");
		}
	}

	public function onPreExecute(Player $source) : bool{
		$position = $this->inventory->getHolder();
		$block = $position->getWorld()->getBlock($position);

		assert($block instanceof Beacon);

		$beaconLevel = $block->getBeaconLevel();

		$ev = new BeaconActivateEvent($block, $this->primaryEffect, $beaconLevel >= Beacon::MAX_LEVEL_BEACON ? $this->secondaryEffect : null);

		if($beaconLevel < 1 || !$block->viewSky()){
			$ev->cancel();
		}

		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		//Plugins might change this
		$this->primaryEffect = $ev->getPrimaryEffect();
		$this->secondaryEffect = $ev->getSecondaryEffect();

		return true;
	}

	public function execute(Player $source) : void{
		$position = $this->inventory->getHolder();
		$world = $position->getWorld();
		$block = $world->getBlock($position);

		assert($block instanceof Beacon);

		$block->setPrimaryEffect($this->primaryEffect);
		$block->setSecondaryEffect($this->secondaryEffect);

		$input = $this->inventory->getInput();
		if ($input->getCount() > 0) {
			$input->pop();
			$this->inventory->setInput($input);
		}

		$world->setBlock($position, $block);
		$world->scheduleDelayedBlockUpdate($position, 20);
	}
}
