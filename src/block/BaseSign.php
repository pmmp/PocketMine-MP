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
use pocketmine\event\block\SignChangeEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_map;
use function assert;
use function strlen;

abstract class BaseSign extends Transparent{
	//TODO: conditionally useless properties, find a way to fix

	/** @var SignText */
	protected $text;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(1.0, BlockToolType::AXE));
		$this->text = new SignText();
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileSign){
			$this->text = $tile->getText();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->pos->getWorld()->getTile($this->pos);
		assert($tile instanceof TileSign);
		$tile->setText($this->text);
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

	abstract protected function getSupportingFace() : int;

	public function onNearbyBlockChange() : void{
		if($this->getSide($this->getSupportingFace())->getId() === BlockLegacyIds::AIR){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}
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
		$removeFormat = $author->getRemoveFormat();
		$ev = new SignChangeEvent($this, $author, new SignText(array_map(function(string $line) use ($removeFormat) : string{
			return TextFormat::clean($line, $removeFormat);
		}, $text->getLines())));
		$ev->call();
		if(!$ev->isCancelled()){
			$this->setText($ev->getNewText());
			$this->pos->getWorld()->setBlock($this->pos, $this);
			return true;
		}

		return false;
	}
}
