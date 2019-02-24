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

use pocketmine\item\Banner as ItemBanner;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Banner as TileBanner;
use function floor;

class StandingBanner extends Transparent{

	/** @var int */
	protected $rotation = 0;

	protected function writeStateToMeta() : int{
		return $this->rotation;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->rotation = $stateMeta;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getHardness() : float{
		return 1;
	}

	public function isSolid() : bool{
		return false;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return null;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face !== Facing::DOWN){
			if($face === Facing::UP and $player !== null){
				$this->rotation = ((int) floor((($player->yaw + 180) * 16 / 360) + 0.5)) & 0x0f;
				return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
			}

			return $this->getLevel()->setBlock($blockReplace, BlockFactory::get(Block::WALL_BANNER, $face));
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->getId() === self::AIR){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$tile = $this->level->getTile($this);

		$drop = ItemFactory::get(Item::BANNER, ($tile instanceof TileBanner ? $tile->getBaseColor()->getInvertedMagicNumber() : 0));
		if($tile instanceof TileBanner and $drop instanceof ItemBanner and !($patterns = $tile->getPatterns())->empty()){
			$drop->setPatterns($patterns);
		}

		return [$drop];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
