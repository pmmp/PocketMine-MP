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
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\generator\object\TreeFactory;
use pocketmine\world\generator\object\TreeType;
use function mt_rand;

class NetherFungus extends Flowable{
	use StaticSupportTrait;

	/**
	 * @param int      $nyliumTypeId The type id of the nylium block this fungus can grow on.
	 * @param TreeType $treeType     The TreeType that this fungus will grow into when bone-mealed.
	 */
	public function __construct(
		BlockIdentifier $idInfo,
		string $name,
		BlockTypeInfo $typeInfo,
		private readonly int $nyliumTypeId,
		private readonly TreeType $treeType
	){
		parent::__construct($idInfo, $name, $typeInfo);
	}

	private function canBeSupportedAt(Block $block) : bool{
		//TODO: moss
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock->hasTypeTag(BlockTypeTags::DIRT) ||
			$supportBlock->hasTypeTag(BlockTypeTags::MUD) ||
			$supportBlock->hasTypeTag(BlockTypeTags::NYLIUM) ||
			$supportBlock->getTypeId() === BlockTypeIds::SOUL_SOIL;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer){
			$item->pop();
			if($this->getSide(Facing::DOWN)->getTypeId() === $this->nyliumTypeId && ($player === null || !$player->hasFiniteResources() || mt_rand(1, 100) <= 40)){
				$this->grow($player);
			}
			return true;
		}
		return false;
	}

	private function grow(?Player $player) : void{
		$random = new Random(mt_rand());
		$tree = TreeFactory::get($random, $this->treeType);
		$transaction = $tree?->getBlockTransaction($this->position->getWorld(), $this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ(), $random);
		if($transaction === null){
			return;
		}

		$ev = new StructureGrowEvent($this, $transaction, $player);
		$ev->call();
		if(!$ev->isCancelled()){
			$transaction->apply();
		}
	}
}
