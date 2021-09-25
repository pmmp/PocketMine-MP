<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\tile\Lectern as TileLectern;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\WritableBookBase;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Lectern extends Transparent{
	use FacesOppositePlacingPlayerTrait;
	use HorizontalFacingTrait;

	protected int $viewedPage = 0;
	protected ?Item $book = null;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readLegacyHorizontalFacing($stateMeta & 0x03);
	}

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeLegacyHorizontalFacing($this->facing);
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);

		if($tile instanceof TileLectern){

			$this->book = $tile->getBook();
			$this->viewedPage = $tile->getViewedPage();

		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileLectern){
			$tile->setBook($this->book);
			$tile->setViewedPage($this->viewedPage);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{

		if($item instanceof WritableBookBase){
			$this->book = $item->pop();
			$this->position->getWorld()->setBlock($this->position, $this);
			return true;
		}
		return false;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		$tile = $this->position->getWorld()->getTile($this->position);
		if($this->book !== null && !$this->book->isNull() && $tile instanceof TileLectern){
			$droppedBook = new ItemEntity(Location::fromObject($this->position->up(), $this->position->getWorld(), 0, 0), $this->book);
			$droppedBook->spawnToAll();

			$this->book = ItemFactory::air();
			$this->viewedPage = 0;

			$this->position->getWorld()->setBlock($this->position, $this);
			return true;
		}
		return false;
	}

	public function setViewedPage(int $viewedPage) : void{
		$this->viewedPage = $viewedPage;
	}

	public function getViewedPage() : int{
		return $this->viewedPage;
	}

	public function setBook(Item $book) : void{
		$this->book = $book;
	}
}
