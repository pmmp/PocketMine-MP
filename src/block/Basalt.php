<?php
declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemFactory;
use function mt_rand;

class Basalt extends Opaque {

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::STONE()->getHarvestLevel()));
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::getInstance()->get(ItemIds::BASALT)
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
