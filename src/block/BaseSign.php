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

use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\utils\SignText;
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\BlockTransaction;
use function array_map;
use function assert;
use function strlen;

abstract class BaseSign extends Transparent{
	protected SignText $text;
	protected ?int $editorEntityRuntimeId = null;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		parent::__construct($idInfo, $name, $breakInfo);
		$this->text = new SignText();
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileSign){
			$this->text = $tile->getText();
			$this->editorEntityRuntimeId = $tile->getEditorEntityRuntimeId();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileSign);
		$tile->setText($this->text);
		$tile->setEditorEntityRuntimeId($this->editorEntityRuntimeId);
	}

	public function isSolid() : bool{
		return false;
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	abstract protected function getSupportingFace() : int;

	public function onNearbyBlockChange() : void{
		if($this->getSide($this->getSupportingFace())->getId() === BlockLegacyIds::AIR){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->editorEntityRuntimeId = $player->getId();
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	/**
	 * Returns an object containing information about the sign text.
	 */
	public function getText() : SignText{
		return $this->text;
	}

	/** @return $this */
	public function setText(SignText $text) : self{
		$this->text = $text;
		return $this;
	}

	/**
	 * Called by the player controller (network session) to update the sign text, firing events as appropriate.
	 *
	 * @return bool if the sign update was successful.
	 * @throws \UnexpectedValueException if the text payload is too large
	 */
	public function updateText(Player $author, SignText $text) : bool{
		$size = 0;
		foreach($text->getLines() as $line){
			$size += strlen($line);
		}
		if($size > 1000){
			throw new \UnexpectedValueException($author->getName() . " tried to write $size bytes of text onto a sign (bigger than max 1000)");
		}
		$ev = new SignChangeEvent($this, $author, new SignText(array_map(function(string $line) : string{
			return TextFormat::clean($line, false);
		}, $text->getLines())));
		if($this->editorEntityRuntimeId === null || $this->editorEntityRuntimeId !== $author->getId()){
			$ev->cancel();
		}
		$ev->call();
		if(!$ev->isCancelled()){
			$this->setText($ev->getNewText());
			$this->position->getWorld()->setBlock($this->position, $this);
			return true;
		}

		return false;
	}
}
