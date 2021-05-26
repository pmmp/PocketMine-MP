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
use pocketmine\block\utils\BlockDataSerializer;
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

	protected BlockIdentifierFlattened $idInfoFlattened;

	protected bool $inverted = false;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->idInfoFlattened = $idInfo;
		parent::__construct($idInfo, $name, $breakInfo);
	}

	public function getId() : int{
		return $this->inverted ? $this->idInfoFlattened->getSecondId() : parent::getId();
	}

	protected function writeStateToMeta() : int{
		return $this->signalStrength;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->signalStrength = BlockDataSerializer::readBoundedInt("signalStrength", $stateMeta, 0, 15);
		$this->inverted = $id === $this->idInfoFlattened->getSecondId();
	}

	public function getStateBitmask() : int{
		return 0b1111;
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

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->inverted = !$this->inverted;
		$this->signalStrength = $this->recalculateSignalStrength();
		$this->pos->getWorld()->setBlock($this->pos, $this);
		return true;
	}

	public function onScheduledUpdate() : void{
		$signalStrength = $this->recalculateSignalStrength();
		if($this->signalStrength !== $signalStrength){
			$this->signalStrength = $signalStrength;
			$this->pos->getWorld()->setBlock($this->pos, $this);
		}
		$this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, 20);
	}

	private function recalculateSignalStrength() : int{
		$lightLevel = $this->pos->getWorld()->getRealBlockSkyLightAt($this->pos->x, $this->pos->y, $this->pos->z);
		if($this->inverted){
			return 15 - $lightLevel;
		}

		$sunAngle = $this->pos->getWorld()->getSunAnglePercentage();
		return max(0, (int) round($lightLevel * cos(($sunAngle + ((($sunAngle < 0.5 ? 0 : 1) - $sunAngle) / 5)) * 2 * M_PI)));
	}

	//TODO
}
