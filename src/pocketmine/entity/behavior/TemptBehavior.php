<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\behavior;

use pocketmine\entity\Mob;
use pocketmine\Player;
use function in_array;

class TemptBehavior extends Behavior{

	/** @var float */
	protected $speedMultiplier;
	/** @var int[] */
	protected $temptItems;
	/** @var int */
	protected $delayTemptCounter = 0;
	/** @var Player */
	protected $temptingPlayer;
	/** @var bool */
	protected $scaredByPlayerMovement = false;

	public function __construct(Mob $mob, array $temptItemIds, float $speedMultiplier, bool $scaredByPlayerMovement = false){
		parent::__construct($mob);

		$this->temptItems = $temptItemIds;
		$this->speedMultiplier = $speedMultiplier;
		$this->scaredByPlayerMovement = $scaredByPlayerMovement;

		$this->mutexBits = 3;
	}

	public function canStart() : bool{
		if($this->delayTemptCounter > 0){
			$this->delayTemptCounter--;
			return false;
		}

		$player = $this->mob->level->getNearestEntity($this->mob, sqrt(10), Player::class);

		if($player instanceof Player){
			if(in_array($player->getInventory()->getItemInHand()->getId(), $this->temptItems)){
				$this->temptingPlayer = $player;

				return true;
			}
		}

		return false;
	}

	public function canContinue() : bool{
		if($this->scaredByPlayerMovement){
			if($this->temptingPlayer->hasMovementUpdate()){
				return false;
			}
		}
		return $this->canStart();
	}

	public function onTick() : void{
		$this->mob->getLookHelper()->setLookPositionWithEntity($this->temptingPlayer, 30, $this->mob->getVerticalFaceSpeed());

		if($this->temptingPlayer->distanceSquared($this->mob) < 6.25){
			$this->mob->getNavigator()->clearPath();
		}else{
			$this->mob->getNavigator()->tryMoveTo($this->temptingPlayer, $this->speedMultiplier);
		}
	}

	public function onEnd() : void{
		$this->delayTemptCounter = 100;
		$this->temptingPlayer = null;
		$this->mob->pitch = 0;
		$this->mob->getNavigator()->clearPath();
	}
}