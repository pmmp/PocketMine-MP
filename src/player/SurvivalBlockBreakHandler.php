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

namespace pocketmine\player;

use pocketmine\block\Block;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\world\particle\BlockPunchParticle;
use pocketmine\world\sound\BlockPunchSound;
use function abs;

final class SurvivalBlockBreakHandler{

	public const DEFAULT_FX_INTERVAL_TICKS = 5;

	/** @var Player */
	private $player;
	/** @var Vector3 */
	private $blockPos;
	/** @var Block */
	private $block;
	/** @var int */
	private $targetedFace;

	/** @var int */
	private $fxTicker = 0;
	/** @var int */
	private $fxTickInterval;
	/** @var int */
	private $maxPlayerDistance;

	/** @var float */
	private $breakSpeed;

	/** @var float */
	private $breakProgress = 0;

	private function __construct(Player $player, Vector3 $blockPos, Block $block, int $targetedFace, int $maxPlayerDistance, int $fxTickInterval = self::DEFAULT_FX_INTERVAL_TICKS){
		$this->player = $player;
		$this->blockPos = $blockPos;
		$this->block = $block;
		$this->targetedFace = $targetedFace;
		$this->fxTickInterval = $fxTickInterval;
		$this->maxPlayerDistance = $maxPlayerDistance;

		$this->breakSpeed = $this->calculateBreakProgressPerTick();
		if($this->breakSpeed > 0){
			$this->player->getWorld()->broadcastPacketToViewers(
				$this->blockPos,
				LevelEventPacket::create(LevelEventPacket::EVENT_BLOCK_START_BREAK, (int) (65535 * $this->breakSpeed), $this->blockPos)
			);
		}
	}

	public static function createIfNecessary(Player $player, Vector3 $blockPos, Block $block, int $targetedFace, int $maxPlayerDistance, int $fxTickInterval = self::DEFAULT_FX_INTERVAL_TICKS) : ?self{
		$breakInfo = $block->getBreakInfo();
		if(!$breakInfo->breaksInstantly()){
			return new self($player, $blockPos, $block, $targetedFace, $maxPlayerDistance, $fxTickInterval);
		}
		return null;
	}

	/**
	 * Returns the calculated break speed as percentage progress per game tick.
	 */
	private function calculateBreakProgressPerTick() : float{
		if(!$this->block->getBreakInfo()->isBreakable()){
			return 0.0;
		}
		//TODO: improve this to take stuff like swimming, ladders, enchanted tools into account, fix wrong tool break time calculations for bad tools (pmmp/PocketMine-MP#211)
		$breakTimePerTick = $this->block->getBreakInfo()->getBreakTime($this->player->getInventory()->getItemInHand()) * 20;

		if($breakTimePerTick > 0){
			return 1 / $breakTimePerTick;
		}
		return 1;
	}

	public function update() : bool{
		if(
			$this->player->getPosition()->distanceSquared($this->blockPos->add(0.5, 0.5, 0.5)) > $this->maxPlayerDistance ** 2){
			return false;
		}

		$newBreakSpeed = $this->calculateBreakProgressPerTick();
		if(abs($newBreakSpeed - $this->breakSpeed) > 0.0001){
			$this->breakSpeed = $newBreakSpeed;
			//TODO: sync with client
		}

		$this->breakProgress += $this->breakSpeed;

		if(($this->fxTicker++ % $this->fxTickInterval) === 0 and $this->breakProgress < 1){
			$this->player->getWorld()->addParticle($this->blockPos, new BlockPunchParticle($this->block, $this->targetedFace));
			$this->player->getWorld()->addSound($this->blockPos, new BlockPunchSound($this->block));
			$this->player->broadcastAnimation(new ArmSwingAnimation($this->player), $this->player->getViewers());
		}

		return $this->breakProgress < 1;
	}

	public function getBlockPos() : Vector3{
		return $this->blockPos;
	}

	public function getTargetedFace() : int{
		return $this->targetedFace;
	}

	public function setTargetedFace(int $face) : void{
		Facing::validate($face);
		$this->targetedFace = $face;
	}

	public function getBreakSpeed() : float{
		return $this->breakSpeed;
	}

	public function getBreakProgress() : float{
		return $this->breakProgress;
	}

	public function __destruct(){
		if($this->player->getWorld()->isInLoadedTerrain($this->blockPos)){
			$this->player->getWorld()->broadcastPacketToViewers(
				$this->blockPos,
				LevelEventPacket::create(LevelEventPacket::EVENT_BLOCK_STOP_BREAK, 0, $this->blockPos)
			);
		}
	}
}
