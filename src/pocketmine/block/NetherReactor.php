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
use pocketmine\item\ItemFactory;
use pocketmine\item\TieredTool;

class NetherReactor extends Solid{

	/** @var int */
	protected $state = BlockLegacyMetadata::NETHER_REACTOR_INACTIVE;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(3.0, BlockToolType::TYPE_PICKAXE, TieredTool::TIER_WOODEN));
	}

	protected function writeStateToMeta() : int{
		return $this->state;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->state = BlockDataValidator::readBoundedInt("state", $stateMeta, 0, 2);
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::IRON_INGOT, 0, 6),
			ItemFactory::get(Item::DIAMOND, 0, 3)
		];
	}
}
