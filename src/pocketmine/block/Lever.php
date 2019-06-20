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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;

class Lever extends Flowable{
	protected const BOTTOM = 0;
	protected const SIDE = 1;
	protected const TOP = 2;

	/** @var int */
	protected $position = self::BOTTOM;
	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $powered = false;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.5));
	}

	protected function writeStateToMeta() : int{
		if($this->position === self::BOTTOM){
			$rotationMeta = Facing::axis($this->facing) === Facing::AXIS_Z ? 7 : 0;
		}elseif($this->position === self::TOP){
			$rotationMeta = Facing::axis($this->facing) === Facing::AXIS_Z ? 5 : 6;
		}else{
			$rotationMeta = 6 - $this->facing;
		}
		return $rotationMeta | ($this->powered ? BlockLegacyMetadata::LEVER_FLAG_POWERED : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$rotationMeta = $stateMeta & 0x07;
		if($rotationMeta === 5 or $rotationMeta === 6){
			$this->position = self::TOP;
			$this->facing = $rotationMeta === 5 ? Facing::SOUTH : Facing::EAST;
		}elseif($rotationMeta === 7 or $rotationMeta === 0){
			$this->position = self::BOTTOM;
			$this->facing = $rotationMeta === 7 ? Facing::SOUTH : Facing::EAST;
		}else{
			$this->position = self::SIDE;
			$this->facing = BlockDataValidator::readHorizontalFacing(6 - $rotationMeta);
		}

		$this->powered = ($stateMeta & BlockLegacyMetadata::LEVER_FLAG_POWERED) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$blockClicked->isSolid()){
			return false;
		}

		if(Facing::axis($face) === Facing::AXIS_Y){
			if($player !== null){
				$this->facing = Facing::opposite($player->getHorizontalFacing());
			}
			$this->position = $face === Facing::DOWN ? self::BOTTOM : self::TOP;
		}else{
			$this->facing = $face;
			$this->position = self::SIDE;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		if($this->position === self::BOTTOM){
			$face = Facing::UP;
		}elseif($this->position === self::TOP){
			$face = Facing::DOWN;
		}else{
			$face = Facing::opposite($this->facing);
		}

		if(!$this->getSide($face)->isSolid()){
			$this->world->useBreakOn($this);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->powered = !$this->powered;
		$this->world->setBlock($this, $this);
		$this->world->addSound(
			$this->add(0.5, 0.5, 0.5),
			$this->powered ? new RedstonePowerOnSound() : new RedstonePowerOffSound()
		);
		return true;
	}

	//TODO
}
