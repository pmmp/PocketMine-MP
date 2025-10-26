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

use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\World;
use function var_dump;

class MossBlock extends Opaque{
	/**
	 * @param array<float, \Closure(\pocketmine\utils\Random): \pocketmine\block\Block|null> $vegetationChances Array mapping chance (float) to closures that take a Random and return a Block or null
	 * @param float $vegetationPlaceChance Chance to place vegetation on moss blocks (default 60%)
	 */
	public function __construct(
		BlockIdentifier $id,
		string $name,
		BlockTypeInfo $info,
		private readonly array $vegetationChances,
		private readonly float $vegetationPlaceChance = 0.6
	){
		parent::__construct($id, $name, $info);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && $this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR){
			$item->pop();
			$this->generateMossPatch(new Random());
			return true;
		}
		return false;
	}

	private function generateMossPatch(Random $random) : void{
		$origin = $this->position;
		$world = $origin->getWorld();

		$xzRadiusX = $random->nextBoolean() ? 2 : 3;
		$xzRadiusZ = $random->nextBoolean() ? 2 : 3;

		$positions = $this->placeGroundPatch($world, $random, $origin, $xzRadiusX, $xzRadiusZ);
		$this->distributeVegetation($world, $random, $positions);
	}

	/**
	 * @return Vector3[]
	 */
	private function placeGroundPatch(World $world, Random $random, Vector3 $origin, int $xzRadiusX, int $xzRadiusZ) : array{
		$positions = [];

		for($dx = -$xzRadiusX; $dx <= $xzRadiusX; ++$dx){
			for($dz = -$xzRadiusZ; $dz <= $xzRadiusZ; ++$dz){
				$borderX = ($dx === -$xzRadiusX) || ($dx === $xzRadiusX);
				$borderZ = ($dz === -$xzRadiusZ) || ($dz === $xzRadiusZ);
				$isCorner = $borderX && $borderZ;
				$isEdge = ($borderX || $borderZ) && !$isCorner;

				if($isCorner){
					var_dump("placeGroundPatch skipping corner at dx=$dx, dz=$dz");
					continue;
				}
				if($isEdge && $random->nextFloat() > 0.75){
					var_dump("placeGroundPatch skipping edge at dx=$dx, dz=$dz");
					continue;
				}

				$searchPos = $origin->add($dx, 1, $dz);

				$mossPos = $this->findMossPlacementPosition($world, $searchPos);
				// $mossPos = $this->findMossPlacementPosition($world, $origin);
				if($mossPos !== null){
					$world->setBlock($mossPos, $this);
					var_dump("placeGroundPatch placed moss at " . $mossPos->x . ", " . $mossPos->y . ", " . $mossPos->z);
					$positions[] = $mossPos;
				}
			}
		}

		return $positions;
	}

	private function findMossPlacementPosition(World $world, Vector3 $startPos) : ?Vector3{
		if(!$world->isInWorld($startPos->x, $startPos->y, $startPos->z)){
			var_dump("findMossPlacementPosition: startPos out of world");
			return null;
		}

		var_dump("findMossPlacementPosition: starting search downward from air block at y=" . $startPos->y);
		for($i = -6; $i < 6; $i++){
			$checkPos = $startPos->down($i);
			if(!$world->isInWorld($checkPos->x, $checkPos->y, $checkPos->z)){
				var_dump("findMossPlacementPosition: checkPos out of world at y=" . $checkPos->y);
				break;
			}

			$checkBlock = $world->getBlock($checkPos);
			$blockAbove = $world->getBlock($checkPos->up());

			if($checkBlock->hasTypeTag(BlockTypeTags::MOSS_REPLACEABLE) && $blockAbove->getTypeId() === BlockTypeIds::AIR){
				var_dump("findMossPlacementPosition: found moss position at " . $checkPos->x . ", " . $checkPos->y . ", " . $checkPos->z);
				return $checkPos;
			}
		}
		return null;
	}

	private function canReplaceBlock(Block $block) : bool{
		return $block->hasTypeTag(BlockTypeTags::MOSS_REPLACEABLE);
	}

	/**
	 * @param Vector3[] $positions
	 */
	private function distributeVegetation(World $world, Random $random, array $positions) : void{
		if(empty($positions) || empty($this->vegetationChances)){
			var_dump("distributeVegetation: no positions or vegetation chances");
			return;
		}

		foreach($positions as $mossPos){
			if($this->vegetationPlaceChance > 0.0 && $random->nextFloat() < $this->vegetationPlaceChance){
				$vegPos = $mossPos->up();
				if(!$world->isInWorld($vegPos->x, $vegPos->y, $vegPos->z)){
					var_dump("distributeVegetation: vegPos out of world");
					continue;
				}

				$targetBlock = $world->getBlock($vegPos);
				if($targetBlock->getTypeId() !== BlockTypeIds::AIR){
					var_dump("distributeVegetation: targetBlock not air at " . $vegPos->x . ", " . $vegPos->y . ", " . $vegPos->z);
					continue;
				}

				$vegetationBlock = $this->getRandomVegetation($random);
				if($vegetationBlock !== null && $vegetationBlock->canBePlacedAt($targetBlock, Vector3::zero(), Facing::DOWN, false)){
					var_dump("distributeVegetation: placing vegetation at " . $vegPos->x . ", " . $vegPos->y . ", " . $vegPos->z);
					$world->setBlock($vegPos, $vegetationBlock);
				}
			}
		}
	}

	private function getRandomVegetation(Random $random) : ?Block{
		if(empty($this->vegetationChances)){
			return null;
		}

		$roll = $random->nextFloat() * 100;
		$cumulative = 0.0;

		foreach($this->vegetationChances as $chance => $vegetationBlock){
			$cumulative += $chance;
			if($roll < $cumulative){
				return $vegetationBlock($random);
			}
		}

		return null;
	}
}