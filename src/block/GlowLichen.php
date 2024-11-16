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

use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\MultiAnySupportTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use function count;
use function shuffle;

class GlowLichen extends Transparent{
	use MultiAnySupportTrait;

	public function getLightLevel() : int{
		return 7;
	}

	public function isSolid() : bool{
		return false;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	/**
	 * @return int[]
	 */
	protected function getInitialPlaceFaces(Block $blockReplace) : array{
		return $blockReplace instanceof GlowLichen ? $blockReplace->faces : [];
	}

	private function getSpreadBlock(Block $replace, int $spreadFace) : ?Block{
		if($replace instanceof self && $replace->hasSameTypeId($this)){
			if($replace->hasFace($spreadFace)){
				return null;
			}
			$result = $replace;
		}elseif($replace->getTypeId() === BlockTypeIds::AIR){
			$result = VanillaBlocks::GLOW_LICHEN();
		}else{
			//TODO: if this is a water block, generate a waterlogged block
			return null;
		}
		return $result->setFace($spreadFace, true);
	}

	private function spread(World $world, Vector3 $replacePos, int $spreadFace) : bool{
		$supportBlock = $world->getBlock($replacePos->getSide($spreadFace));
		$supportFace = Facing::opposite($spreadFace);

		if($supportBlock->getSupportType($supportFace) !== SupportType::FULL){
			return false;
		}

		$replacedBlock = $supportBlock->getSide($supportFace);
		$replacementBlock = $this->getSpreadBlock($replacedBlock, Facing::opposite($supportFace));
		if($replacementBlock === null){
			return false;
		}

		return BlockEventHelper::spread($replacedBlock, $replacementBlock, $this);
	}

	/**
	 * @phpstan-return \Generator<int, int, void, void>
	 */
	private static function getShuffledSpreadFaces(int $sourceFace) : \Generator{
		$skipAxis = Facing::axis($sourceFace);

		$faces = Facing::ALL;
		shuffle($faces);
		foreach($faces as $spreadFace){
			if(Facing::axis($spreadFace) !== $skipAxis){
				yield $spreadFace;
			}
		}
	}

	private function spreadAroundSupport(int $sourceFace) : bool{
		$world = $this->position->getWorld();

		$supportPos = $this->position->getSide($sourceFace);
		foreach(self::getShuffledSpreadFaces($sourceFace) as $spreadFace){
			$replacePos = $supportPos->getSide($spreadFace);
			if($this->spread($world, $replacePos, Facing::opposite($spreadFace))){
				return true;
			}
		}

		return false;
	}

	private function spreadAdjacentToSupport(int $sourceFace) : bool{
		$world = $this->position->getWorld();

		foreach(self::getShuffledSpreadFaces($sourceFace) as $spreadFace){
			$replacePos = $this->position->getSide($spreadFace);
			if($this->spread($world, $replacePos, $sourceFace)){
				return true;
			}
		}
		return false;
	}

	private function spreadWithinSelf(int $sourceFace) : bool{
		foreach(self::getShuffledSpreadFaces($sourceFace) as $spreadFace){
			if(!$this->hasFace($spreadFace) && $this->spread($this->position->getWorld(), $this->position, $spreadFace)){
				return true;
			}
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && count($this->faces) > 0){
			$shuffledFaces = $this->faces;
			shuffle($shuffledFaces);

			$spreadMethods = [
				$this->spreadAroundSupport(...),
				$this->spreadAdjacentToSupport(...),
				$this->spreadWithinSelf(...),
			];
			shuffle($spreadMethods);

			foreach($shuffledFaces as $sourceFace){
				foreach($spreadMethods as $spreadMethod){
					if($spreadMethod($sourceFace)){
						$item->pop();
						break 2;
					}
				}
			}

			return true;
		}
		return false;
	}

	public function getDrops(Item $item) : array{
		if(($item->getBlockToolType() & BlockToolType::SHEARS) !== 0){
			return $this->getDropsForCompatibleTool($item);
		}

		return [];
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}
}
