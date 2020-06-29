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

use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class BrewingStand extends Transparent{

	/** @var bool */
	protected $eastSlot = false;
	/** @var bool */
	protected $northwestSlot = false;
	/** @var bool */
	protected $southwestSlot = false;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()));
	}

	protected function writeStateToMeta() : int{
		return ($this->eastSlot ? BlockLegacyMetadata::BREWING_STAND_FLAG_EAST : 0) |
			($this->southwestSlot ? BlockLegacyMetadata::BREWING_STAND_FLAG_SOUTHWEST : 0) |
			($this->northwestSlot ? BlockLegacyMetadata::BREWING_STAND_FLAG_NORTHWEST : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->eastSlot = ($stateMeta & BlockLegacyMetadata::BREWING_STAND_FLAG_EAST) !== 0;
		$this->southwestSlot = ($stateMeta & BlockLegacyMetadata::BREWING_STAND_FLAG_SOUTHWEST) !== 0;
		$this->northwestSlot = ($stateMeta & BlockLegacyMetadata::BREWING_STAND_FLAG_NORTHWEST) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$stand = $this->pos->getWorld()->getTile($this->pos);
			if($stand instanceof TileBrewingStand and $stand->canOpenWith($item->getCustomName())){
				$player->setCurrentWindow($stand->getInventory());
			}
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		//TODO
	}
}
