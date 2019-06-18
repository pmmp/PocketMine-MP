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
	/** @var BlockIdentifierFlattened */
	protected $idInfo;

	/** @var int */
	protected $power = 0;

	/** @var bool */
	protected $inverted = false;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.2, BlockToolType::TYPE_AXE));
	}

	public function getId() : int{
		return $this->inverted ? $this->idInfo->getSecondId() : parent::getId();
	}

	protected function writeStateToMeta() : int{
		return $this->power;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->power = BlockDataValidator::readBoundedInt("power", $stateMeta, 0, 15);
		$this->inverted = $id === $this->idInfo->getSecondId();
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function isInverted() : bool{
		return $this->inverted;
	}

	/**
	 * @param bool $inverted
	 *
	 * @return $this
	 */
	public function setInverted(bool $inverted = true) : self{
		$this->inverted = $inverted;
		return $this;
	}

	public function getFuelTime() : int{
		return 300;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->trim(Facing::UP, 0.5);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->inverted = !$this->inverted;
		$this->power = $this->recalculatePower();
		$this->world->setBlock($this, $this);
		return true;
	}

	public function onScheduledUpdate() : void{
		$this->power = $this->recalculatePower();
		$this->world->setBlock($this, $this);
		$this->world->scheduleDelayedBlockUpdate($this, 20);
	}

	private function recalculatePower() : int{
		$lightLevel = $this->world->getRealBlockSkyLightAt($this->x, $this->y, $this->z);
		if($this->inverted){
			return 15 - $lightLevel;
		}

		$sunAngle = $this->world->getSunAnglePercentage();
		return max(0, (int) round(15 * cos(($sunAngle + ((($sunAngle < 0.5 ? 0 : 1) - $sunAngle) / 5)) * 2 * M_PI)));
	}

	//TODO
}
