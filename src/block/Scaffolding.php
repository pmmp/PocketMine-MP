<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\math\Vector3;

/**
 * Minimal Scaffolding block implementation.
 * Provides basic climbable behavior so the block can be placed and climbed.
 * Full scaffolding mechanics (support distance, collapsing, fall-through, etc.)
 * are out of scope for this small addition and can be implemented later.
 */
class Scaffolding extends Transparent{
	public function hasEntityCollision() : bool{
		return true;
	}

	public function isSolid() : bool{
		return false;
	}

	public function canClimb() : bool{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool{
		if($entity instanceof Living && $entity->getPosition()->floor()->distanceSquared($this->position) < 1){
			$entity->resetFallDistance();
			$entity->onGround = true;
		}
		return true;
	}

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}
