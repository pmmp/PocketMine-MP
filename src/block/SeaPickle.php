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

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class SeaPickle extends Transparent{
	/** @var int */
	protected $count = 1;
	/** @var bool */
	protected $underwater = false;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? BlockBreakInfo::instant());
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->count = ($stateMeta & 0x03) + 1;
		$this->underwater = ($stateMeta & BlockLegacyMetadata::SEA_PICKLE_FLAG_NOT_UNDERWATER) === 0;
	}

	protected function writeStateToMeta() : int{
		return ($this->count - 1) | ($this->underwater ? 0 : BlockLegacyMetadata::SEA_PICKLE_FLAG_NOT_UNDERWATER);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getCount() : int{ return $this->count; }

	/** @return $this */
	public function setCount(int $count) : self{
		if($count < 1 || $count > 4){
			throw new \InvalidArgumentException("Count must be in range 1-4");
		}
		$this->count = $count;
		return $this;
	}

	public function isUnderwater() : bool{ return $this->underwater; }

	/** @return $this */
	public function setUnderwater(bool $underwater) : self{
		$this->underwater = $underwater;
		return $this;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getLightLevel() : int{
		return $this->underwater ? ($this->count + 1) * 3 : 0;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		//TODO: proper placement logic (needs a supporting face below)
		return ($blockReplace instanceof SeaPickle and $blockReplace->count < 4) or parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->underwater = false; //TODO: implement this once we have new water logic in place
		if($blockReplace instanceof SeaPickle and $blockReplace->count < 4){
			$this->count = $blockReplace->count + 1;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		//TODO: bonemeal logic (requires coral)
		return parent::onInteract($item, $face, $clickVector, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [$this->asItem()->setCount($this->count)];
	}
}
