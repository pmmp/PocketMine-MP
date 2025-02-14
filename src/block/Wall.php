<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\SupportType;
use pocketmine\block\utils\WallConnectionType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

/**
 * @phpstan-type WallConnectionSet array<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST, WallConnectionType>
 */
class Wall extends Transparent{

	/**
	 * @var WallConnectionType[]
	 * @phpstan-var WallConnectionSet
	 */
	protected array $connections = [];
	protected bool $post = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->wallConnections($this->connections);
		$w->bool($this->post);
	}

	/**
	 * @return WallConnectionType[]
	 * @phpstan-return WallConnectionSet
	 */
	public function getConnections() : array{ return $this->connections; }

	public function getConnection(int $face) : ?WallConnectionType{
		return $this->connections[$face] ?? null;
	}

	/**
	 * @param WallConnectionType[] $connections
	 * @phpstan-param WallConnectionSet $connections
	 * @return $this
	 */
	public function setConnections(array $connections) : self{
		$this->connections = $connections;
		return $this;
	}

	/** @return $this */
	public function setConnection(int $face, ?WallConnectionType $type) : self{
		if($face !== Facing::NORTH && $face !== Facing::SOUTH && $face !== Facing::WEST && $face !== Facing::EAST){
			throw new \InvalidArgumentException("Facing can only be north, east, south or west");
		}
		if($type !== null){
			$this->connections[$face] = $type;
		}else{
			unset($this->connections[$face]);
		}
		return $this;
	}

	public function isPost() : bool{ return $this->post; }

	/** @return $this */
	public function setPost(bool $post) : self{
		$this->post = $post;
		return $this;
	}

	public function onNearbyBlockChange() : void{
		$connectionsUpdated = $this->recalculateConnections();
		$postUpdated = $this->recalculatePost();
		if($connectionsUpdated || $postUpdated){
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}

	protected function recalculateConnections() : bool{
		$updated = false;
		$above = $this->getSide(Facing::UP);
		$abovePos = $above->getPosition();

		foreach(Facing::HORIZONTAL as $face) {
			$side = $this->getSide($face);
			$sidePos = $side->getPosition();

			// TODO: Improve stair check by checking corners

			$connected = match (get_class($side)) {
				Cake::class => false,
				Campfire::class => Facing::opposite($face) === Facing::DOWN,
				Fence::class => Facing::opposite($face) === Facing::DOWN || Facing::opposite($face) === Facing::UP,
				Hopper::class => Facing::opposite($face) === Facing::UP,
				Slab::class => $side->getSlabType() === SlabType::DOUBLE || ($side->getSlabType() === SlabType::TOP && Facing::opposite($face) === Facing::UP) || Facing::opposite($face) === Facing::DOWN,
				Stair::class => (!$side->isUpsideDown() && Facing::opposite($face) === Facing::DOWN) ||
					($side->isUpsideDown() && Facing::opposite($face) === Facing::UP) ||
					in_array($face, [Facing::rotate($face, Axis::Y, false), $side->getFacing()]),
				default => !$side->isTransparent()
			};

			if (!$connected){
				if($side instanceof Wall || $side instanceof Thin) $connected = true;
				if($side instanceof FenceGate && $side->getFacing() === $face) $connected = true;
			}

			$connectionType = null;
			if ($connected) {
				$connectionType = WallConnectionType::SHORT();
				$boxes = $above->recalculateCollisionBoxes();

				foreach($boxes as $bb) {
					if ($bb->minY == 0) {
						$xOverlap = $bb->minX < 0.75 && $bb->maxX > 0.25;
						$zOverlap = $bb->minX < 0.75 && $bb->maxX > 0.25;

						$tall = match($face) {
							Facing::NORTH => $xOverlap && $bb->maxZ > 0.75,
							Facing::EAST => $bb->minX < 0.25 && $zOverlap,
							Facing::SOUTH => $xOverlap && $bb->minZ < 0.25,
							Facing::WEST => $bb->maxX > 0.75 && $zOverlap,
							default => false
						};
						if ($tall) {
							$connectionType = WallConnectionType::TALL();
						}
					}
				}
			}

			if ($above instanceof Wall){
				if ($this->getSide($face) instanceof Wall) {
					if ($above->getSide($face) instanceof Wall) $connectionType = WallConnectionType::TALL();
					else if (!$this->post) {
						$updated = true;
						$connectionType = WallConnectionType::SHORT();
						$this->post = true;
					}
				}
			}

			if ($this->getConnection($face) !== $connectionType) {
				$updated = true;

				$this->connections[$face] = $connectionType;
			}
		}

		return $updated;
	}

	public function recalculatePost(): bool
	{
		$updated = false;
		$above = $this->getSide(Facing::UP);

		$connections = count(array_filter(Facing::HORIZONTAL, function ($face) {
			return $this->getConnection($face) !== WallConnectionType::NONE;
		}));

		// TODO: Lanterns and Hanging Signs
		$post = false;
		switch(get_class($above)) {
			case Torch::class:
				$post = $above->getFacing() === Facing::DOWN;
				break;
			case Trapdoor::class:
				if ($above->isOpen()) {
					$post = $this->getConnection($above->getFacing()) !== null;
				}
				break;
			case Wall::class:
				$post = $above->isPost();
				break;
		}

		if (!$post) {
			$post = $connections < 2;
			if ($connections > 2){
				if ($this->getConnection(Facing::NORTH) !== null && $this->getConnection(Facing::SOUTH) !== null) {
					$post = $this->getConnection(Facing::EAST) !== null || $this->getConnection(Facing::WEST) !== null;
				} else if ($this->getConnection(Facing::EAST) !== null && $this->getConnection(Facing::WEST) !== null) {
					$post = $this->getConnection(Facing::NORTH) !== null || $this->getConnection(Facing::SOUTH) !== null;
				} else $post = true;
			}
		}

		if ($this->post !== $post) {
			$updated = true;
			$this->post = $post;
		}

		return $updated;

	}

	protected function recalculateCollisionBoxes() : array{
		//walls don't have any special collision boxes like fences do

		$north = isset($this->connections[Facing::NORTH]);
		$south = isset($this->connections[Facing::SOUTH]);
		$west = isset($this->connections[Facing::WEST]);
		$east = isset($this->connections[Facing::EAST]);

		$inset = 0.25;
		if(
			!$this->post && //if there is a block on top, it stays as a post
			(
				($north && $south && !$west && !$east) ||
				(!$north && !$south && $west && $east)
			)
		){
			//If connected to two sides on the same axis but not any others, AND there is not a block on top, there is no post and the wall is thinner
			$inset = 0.3125;
		}

		return [
			AxisAlignedBB::one()
				->extend(Facing::UP, 0.5)
				->trim(Facing::NORTH, $north ? 0 : $inset)
				->trim(Facing::SOUTH, $south ? 0 : $inset)
				->trim(Facing::WEST, $west ? 0 : $inset)
				->trim(Facing::EAST, $east ? 0 : $inset)
		];
	}

	public function getSupportType(int $facing) : SupportType{
		return Facing::axis($facing) === Axis::Y ? SupportType::CENTER : SupportType::NONE;
	}
}