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

use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use function mt_rand;

final class ChorusPlant extends Flowable{
	use StaticSupportTrait;

	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	protected array $connections = [];

	protected function recalculateCollisionBoxes() : array{
		$bb = AxisAlignedBB::one();
		foreach(Facing::ALL as $facing){
			if(!isset($this->connections[$facing])){
				$bb->trim($facing, 2 / 16);
			}
		}

		return [$bb];
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();

		$this->collisionBoxes = null;

		foreach(Facing::ALL as $facing){
			$block = $this->getSide($facing);
			if(match($block->getTypeId()){
				BlockTypeIds::END_STONE, BlockTypeIds::CHORUS_FLOWER, $this->getTypeId() => true,
				default => false
			}){
				$this->connections[$facing] = true;
			}else{
				unset($this->connections[$facing]);
			}
		}

		return $this;
	}

	private function canBeSupportedBy(Block $block) : bool{
		return $block->hasSameTypeId($this) || $block->getTypeId() === BlockTypeIds::END_STONE;
	}

	private function canBeSupportedAt(Block $block) : bool{
		$position = $block->position;
		$world = $position->getWorld();

		$down = $world->getBlock($position->down());
		$verticalAir = $down->getTypeId() === BlockTypeIds::AIR || $world->getBlock($position->up())->getTypeId() === BlockTypeIds::AIR;

		foreach($position->sidesAroundAxis(Axis::Y) as $sidePosition){
			$block = $world->getBlock($sidePosition);

			if($block->getTypeId() === BlockTypeIds::CHORUS_PLANT){
				if(!$verticalAir){
					return false;
				}

				if($this->canBeSupportedBy($block->getSide(Facing::DOWN))){
					return true;
				}
			}
		}

		return $this->canBeSupportedBy($down);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if(mt_rand(0, 1) === 1){
			return [VanillaItems::CHORUS_FRUIT()];
		}

		return [];
	}
}
