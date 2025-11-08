<?php

declare(strict_types=1);

namespace pocketmine\block;



use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Axis;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\block\utils\SupportType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Block;
use pocketmine\block\Lava;
use pocketmine\block\BlockTypeIds;

class Scaffolding extends Transparent{
	protected int $stability = 7;
	protected bool $stabilityCheck = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(0, 7, $this->stability);
		$w->bool($this->stabilityCheck);
	}

	public function getStability() : int{ return $this->stability; }
	public function setStability(int $v) : self{ $this->stability = $v; return $this; }

	public function getStabilityCheck() : bool{ return $this->stabilityCheck; }
	public function setStabilityCheck(bool $v) : self{ $this->stabilityCheck = $v; return $this; }

	public function hasEntityCollision() : bool{
		return true;
	}

	public function isSolid() : bool{
		return false;
	}

	public function canClimb() : bool{
		return true;
	}

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if ($blockReplace instanceof Lava) {
			return false;
		}

		$down = $this->getSide(Facing::DOWN);

	if (!$blockClicked->hasSameTypeId($this) && !$down->hasSameTypeId($this) && $down->getTypeId() !== BlockTypeIds::AIR && !$down->isSolid()) {
			$scaffoldOnSide = false;
			foreach (Facing::HORIZONTAL as $sideFace) {
				if ($sideFace !== $face) {
					$side = $this->getSide($sideFace);
					if ($side instanceof Scaffolding && $side->hasSameTypeId($this)) {
						$scaffoldOnSide = true;
						break;
					}
				}
			}

			if (!$scaffoldOnSide) {
				return false;
			}
		}
		

		$this->setStabilityCheck(true);
		$world = $this->position->getWorld();
	$world->setBlock($this->position, $this);
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$this->recalculateStabilityAndUpdate();
	}

	private function recalculateStabilityAndUpdate() : void{
		$world = $this->position->getWorld();
		$new = $this->calculateStability();

		if($new !== $this->stability || $this->stabilityCheck){
			$this->stability = $new;
			$this->stabilityCheck = false;
			if($new >= 7){
				// break the block if unstable - spawn drops manually and replace with air
				$drops = $this->getDrops(VanillaItems::AIR());
				$dropPos = $this->position->asVector3()->add(0.5, 0.5, 0.5);
				foreach($drops as $drop){
					if(!$drop->isNull()){
						$world->dropItem($dropPos, $drop);
					}
				}
				$world->setBlock($this->position, VanillaBlocks::AIR());
				return;
			}
			$world->setBlock($this->position, $this);
		}
	}
 

	private function calculateStability() : int{
		$world = $this->position->getWorld();

		// If block below provides full support, stability = 0
		$below = $this->getSide(Facing::DOWN);
		if($below->getSupportType(Facing::UP) === SupportType::FULL){
			return 0;
		}

		// If block below is scaffolding, inherit its stability
		if($below instanceof Scaffolding && $below->hasSameTypeId($this)){
			return $below->getStability();
		}

		// Otherwise, look at horizontal neighbours and take min(neighbour.stability + 1)
		$min = 7;
		foreach(Facing::HORIZONTAL as $f){
			$side = $this->getSide($f);
			if($side instanceof Scaffolding && $side->hasSameTypeId($this)){
				$min = min($min, $side->getStability() + 1);
			}
		}

		return $min;
	}

	public function asItem() : Item{
		return VanillaItems::SCAFFOLDING();
	}
}

