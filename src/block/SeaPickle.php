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

use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataReader;
use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class SeaPickle extends Transparent{
	public const MIN_COUNT = 1;
	public const MAX_COUNT = 4;

	protected int $count = self::MIN_COUNT;
	protected bool $underwater = false;

	public function getRequiredStateDataBits() : int{ return 3; }

	protected function decodeState(RuntimeDataReader $r) : void{
		$this->count = $r->readBoundedInt(2, self::MIN_COUNT - 1, self::MAX_COUNT - 1) + 1;
		$this->underwater = $r->readBool();
	}

	protected function encodeState(RuntimeDataWriter $w) : void{
		$w->writeInt(2, $this->count - 1);
		$w->writeBool($this->underwater);
	}

	public function getCount() : int{ return $this->count; }

	/** @return $this */
	public function setCount(int $count) : self{
		if($count < self::MIN_COUNT || $count > self::MAX_COUNT){
			throw new \InvalidArgumentException("Count must be in range " . self::MIN_COUNT . " ... " . self::MAX_COUNT);
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

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		//TODO: proper placement logic (needs a supporting face below)
		return ($blockReplace instanceof SeaPickle && $blockReplace->count < self::MAX_COUNT) || parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->underwater = false; //TODO: implement this once we have new water logic in place
		if($blockReplace instanceof SeaPickle && $blockReplace->count < self::MAX_COUNT){
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
