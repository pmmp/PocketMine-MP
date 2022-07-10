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

use pocketmine\block\utils\AnalogRedstoneSignalEmitterTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataReader;
use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function cos;
use function max;
use function round;
use const M_PI;

class DaylightSensor extends Transparent{
	use AnalogRedstoneSignalEmitterTrait;

	protected bool $inverted = false;

	public function getRequiredStateDataBits() : int{ return 5; }

	protected function decodeState(RuntimeDataReader $r) : void{
		$this->signalStrength = $r->readBoundedInt(4, 0, 15);
		$this->inverted = $r->readBool();
	}

	protected function encodeState(RuntimeDataWriter $w) : void{
		$w->writeInt(4, $this->signalStrength);
		$w->writeBool($this->inverted);
	}

	public function isInverted() : bool{
		return $this->inverted;
	}

	/**
	 * @return $this
	 */
	public function setInverted(bool $inverted = true) : self{
		$this->inverted = $inverted;
		return $this;
	}

	public function getFuelTime() : int{
		return 300;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 10 / 16)];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->inverted = !$this->inverted;
		$this->signalStrength = $this->recalculateSignalStrength();
		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function onScheduledUpdate() : void{
		$signalStrength = $this->recalculateSignalStrength();
		if($this->signalStrength !== $signalStrength){
			$this->signalStrength = $signalStrength;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
	}

	private function recalculateSignalStrength() : int{
		$lightLevel = $this->position->getWorld()->getRealBlockSkyLightAt($this->position->x, $this->position->y, $this->position->z);
		if($this->inverted){
			return 15 - $lightLevel;
		}

		$sunAngle = $this->position->getWorld()->getSunAnglePercentage();
		return max(0, (int) round($lightLevel * cos(($sunAngle + ((($sunAngle < 0.5 ? 0 : 1) - $sunAngle) / 5)) * 2 * M_PI)));
	}

	//TODO
}
