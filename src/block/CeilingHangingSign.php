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

use pocketmine\block\utils\SignLikeRotationTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function var_dump;

final class CeilingHangingSign extends BaseSign{
	use SignLikeRotationTrait {
		describeBlockOnlyState as describeSignRotation;
	}

	private bool $centerAttached = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		parent::describeBlockOnlyState($w);
		$this->describeSignRotation($w);
		$w->bool($this->centerAttached);
	}

	protected function getSupportingFace() : int{
		return Facing::UP;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face !== Facing::DOWN){
			return false;
		}

		$supportType = $blockReplace->getAdjacentSupportType(Facing::UP);
		if($supportType->equals(SupportType::CENTER())){
			$this->centerAttached = true;
		}
		if($player !== null){
			$this->rotation = self::getRotationFromYaw($player->getLocation()->getYaw());
			var_dump($this->rotation);
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	/**
	 * Returns whether the sign is attached to a single point on the block above.
	 * If false, the sign has two chains attached to different points on the block above.
	 */
	public function isCenterAttached() : bool{ return $this->centerAttached; }

	public function setCenterAttached(bool $centerAttached) : self{
		$this->centerAttached = $centerAttached;
		return $this;
	}

	//TODO: these may have a solid collision box
}
