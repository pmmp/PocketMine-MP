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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class NetherPortal extends Transparent{
	/** @var int */
	protected $axis = Facing::AXIS_X;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? BlockBreakInfo::indestructible(0.0));
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->axis = $stateMeta === BlockLegacyMetadata::NETHER_PORTAL_AXIS_Z ? Facing::AXIS_Z : Facing::AXIS_X; //mojang u dumb
	}

	protected function writeStateToMeta() : int{
		return $this->axis === Facing::AXIS_Z ? BlockLegacyMetadata::NETHER_PORTAL_AXIS_Z : BlockLegacyMetadata::NETHER_PORTAL_AXIS_X;
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	/**
	 * @return int
	 */
	public function getAxis() : int{
		return $this->axis;
	}

	/**
	 * @param int $axis
	 * @throws \InvalidArgumentException
	 */
	public function setAxis(int $axis) : void{
		if($axis !== Facing::AXIS_X and $axis !== Facing::AXIS_Z){
			throw new \InvalidArgumentException("Invalid axis");
		}
		$this->axis = $axis;
	}

	public function getLightLevel() : int{
		return 11;
	}

	public function isSolid() : bool{
		return false;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function getDrops(Item $item) : array{
		return [];
	}

	public function onEntityInside(Entity $entity) : void{
		//TODO
	}
}
