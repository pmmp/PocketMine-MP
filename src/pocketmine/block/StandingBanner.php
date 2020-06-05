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
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Banner as TileBanner;
use pocketmine\tile\Tile;
use function floor;

class StandingBanner extends Transparent{

	protected $id = self::STANDING_BANNER;

	protected $itemId = Item::BANNER;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 1;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getName() : string{
		return "Standing Banner";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return null;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($face !== Vector3::SIDE_DOWN){
			if($face === Vector3::SIDE_UP and $player !== null){
				$this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0f;
				$this->getLevelNonNull()->setBlock($blockReplace, $this, true);
			}else{
				$this->meta = $face;
				$this->getLevelNonNull()->setBlock($blockReplace, BlockFactory::get(Block::WALL_BANNER, $this->meta), true);
			}

			Tile::createTile(Tile::BANNER, $this->getLevelNonNull(), TileBanner::createNBT($this, $face, $item, $player));
			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
			$this->getLevelNonNull()->useBreakOn($this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$tile = $this->level->getTile($this);

		$drop = ItemFactory::get(Item::BANNER, ($tile instanceof TileBanner ? $tile->getBaseColor() : 0));
		if($tile instanceof TileBanner and !($patterns = $tile->getPatterns())->empty()){
			$drop->setNamedTagEntry(clone $patterns);
		}

		return [$drop];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
