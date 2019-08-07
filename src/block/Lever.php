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

use pocketmine\block\utils\BlockDataSerializer;
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
	protected $leverPos = self::BOTTOM;
	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $powered = false;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.5));
	}

	protected function writeStateToMeta() : int{
		if($this->leverPos === self::BOTTOM){
			$rotationMeta = Facing::axis($this->facing) === Facing::AXIS_Z ? 7 : 0;
		}elseif($this->leverPos === self::TOP){
			$rotationMeta = Facing::axis($this->facing) === Facing::AXIS_Z ? 5 : 6;
		}else{
			$rotationMeta = 6 - BlockDataSerializer::writeHorizontalFacing($this->facing);
		}
		return $rotationMeta | ($this->powered ? BlockLegacyMetadata::LEVER_FLAG_POWERED : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$rotationMeta = $stateMeta & 0x07;
		if($rotationMeta === 5 or $rotationMeta === 6){
			$this->leverPos = self::TOP;
			$this->facing = $rotationMeta === 5 ? Facing::SOUTH : Facing::EAST;
		}elseif($rotationMeta === 7 or $rotationMeta === 0){
			$this->leverPos = self::BOTTOM;
			$this->facing = $rotationMeta === 7 ? Facing::SOUTH : Facing::EAST;
		}else{
			$this->leverPos = self::SIDE;
			$this->facing = BlockDataSerializer::readHorizontalFacing(6 - $rotationMeta);
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
			$this->leverPos = $face === Facing::DOWN ? self::BOTTOM : self::TOP;
		}else{
			$this->facing = $face;
			$this->leverPos = self::SIDE;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		if($this->leverPos === self::BOTTOM){
			$face = Facing::UP;
		}elseif($this->leverPos === self::TOP){
			$face = Facing::DOWN;
		}else{
			$face = Facing::opposite($this->facing);
		}

		if(!$this->getSide($face)->isSolid()){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->powered = !$this->powered;
		$this->pos->getWorld()->setBlock($this->pos, $this);
		$this->pos->getWorld()->addSound(
			$this->pos->add(0.5, 0.5, 0.5),
			$this->powered ? new RedstonePowerOnSound() : new RedstonePowerOffSound()
		);
		return true;
	}

	//TODO
}
