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

use pocketmine\block\tile\Lectern as TileLectern;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataReader;
use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\item\Item;
use pocketmine\item\WritableBookBase;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\LecternPlaceBookSound;
use function count;

class Lectern extends Transparent{
	use FacesOppositePlacingPlayerTrait;
	use HorizontalFacingTrait;

	protected int $viewedPage = 0;
	protected ?WritableBookBase $book = null;

	protected bool $producingSignal = false;

	public function getRequiredStateDataBits() : int{ return 3; }

	protected function decodeState(RuntimeDataReader $r) : void{
		$this->facing = $r->readHorizontalFacing();
		$this->producingSignal = $r->readBool();
	}

	protected function encodeState(RuntimeDataWriter $w) : void{
		$w->writeHorizontalFacing($this->facing);
		$w->writeBool($this->producingSignal);
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileLectern){
			$this->viewedPage = $tile->getViewedPage();
			$this->book = $tile->getBook();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileLectern){
			$tile->setViewedPage($this->viewedPage);
			$tile->setBook($this->book);
		}
	}

	public function getFlammability() : int{
		return 30;
	}

	public function getDrops(Item $item) : array{
		$drops = parent::getDrops($item);
		if($this->book !== null){
			$drops[] = clone $this->book;
		}

		return $drops;
	}

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 0.1)];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function isProducingSignal() : bool{ return $this->producingSignal; }

	/** @return $this */
	public function setProducingSignal(bool $producingSignal) : self{
		$this->producingSignal = $producingSignal;
		return $this;
	}

	public function getViewedPage() : int{
		return $this->viewedPage;
	}

	/** @return $this */
	public function setViewedPage(int $viewedPage) : self{
		$this->viewedPage = $viewedPage;
		return $this;
	}

	public function getBook() : ?WritableBookBase{
		return $this->book !== null ? clone $this->book : null;
	}

	/** @return $this */
	public function setBook(?WritableBookBase $book) : self{
		$this->book = $book !== null && !$book->isNull() ? (clone $book)->setCount(1) : null;
		$this->viewedPage = 0;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->book === null && $item instanceof WritableBookBase){
			$this->position->getWorld()->setBlock($this->position, $this->setBook($item));
			$this->position->getWorld()->addSound($this->position, new LecternPlaceBookSound());
			$item->pop();
		}
		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		if($this->book !== null){
			$this->position->getWorld()->dropItem($this->position->up(), $this->book);
			$this->position->getWorld()->setBlock($this->position, $this->setBook(null));
		}
		return false;
	}

	public function onPageTurn(int $newPage) : bool{
		if($newPage === $this->viewedPage){
			return true;
		}
		if($this->book === null || $newPage >= count($this->book->getPages()) || $newPage < 0){
			return false;
		}

		$this->viewedPage = $newPage;
		if(!$this->producingSignal){
			$this->producingSignal = true;
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
		}

		$this->position->getWorld()->setBlock($this->position, $this);

		return true;
	}

	public function onScheduledUpdate() : void{
		if($this->producingSignal){
			$this->producingSignal = false;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}
}
