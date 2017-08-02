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

namespace pocketmine\tile;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Sign extends Spawnable{

	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->Text1)){
			$nbt->Text1 = new StringTag("Text1", "");
		}
		if(!isset($nbt->Text2)){
			$nbt->Text2 = new StringTag("Text2", "");
		}
		if(!isset($nbt->Text3)){
			$nbt->Text3 = new StringTag("Text3", "");
		}
		if(!isset($nbt->Text4)){
			$nbt->Text4 = new StringTag("Text4", "");
		}

		parent::__construct($level, $nbt);
	}

	public function saveNBT(){
		parent::saveNBT();
		unset($this->namedtag->Creator);
	}

	public function setText($line1 = "", $line2 = "", $line3 = "", $line4 = ""){
		$this->namedtag->Text1->setValue($line1);
		$this->namedtag->Text2->setValue($line2);
		$this->namedtag->Text3->setValue($line3);
		$this->namedtag->Text4->setValue($line4);
		$this->onChanged();
	}

	/**
	 * @param int    $index 0-3
	 * @param string $line
	 * @param bool   $update
	 */
	public function setLine(int $index, string $line, bool $update = true){
		if($index < 0 or $index > 3){
			throw new \InvalidArgumentException("Index must be in the range 0-3!");
		}
		$this->namedtag->{"Text" . ($index + 1)}->setValue($line);
		if($update){
			$this->onChanged();
		}
	}

	/**
	 * @param int $index 0-3
	 *
	 * @return string
	 */
	public function getLine(int $index) : string{
		if($index < 0 or $index > 3){
			throw new \InvalidArgumentException("Index must be in the range 0-3!");
		}
		return $this->namedtag->{"Text" . ($index + 1)}->getValue();
	}

	public function getText(){
		return [
			$this->namedtag->Text1->getValue(),
			$this->namedtag->Text2->getValue(),
			$this->namedtag->Text3->getValue(),
			$this->namedtag->Text4->getValue()
		];
	}

	public function addAdditionalSpawnData(CompoundTag $nbt){
		for($i = 1; $i <= 4; $i++){
			$textKey = "Text" . $i;
			$nbt->$textKey = $this->namedtag->$textKey;
		}
		return $nbt;
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		if($nbt["id"] !== Tile::SIGN){
			return false;
		}

		$ev = new SignChangeEvent($this->getBlock(), $player, [
			TextFormat::clean($nbt->Text1->getValue(), ($removeFormat = $player->getRemoveFormat())),
			TextFormat::clean($nbt->Text2->getValue(), $removeFormat),
			TextFormat::clean($nbt->Text3->getValue(), $removeFormat),
			TextFormat::clean($nbt->Text4->getValue(), $removeFormat)
		]);

		if(!isset($this->namedtag->Creator) or $this->namedtag->Creator->getValue() !== $player->getRawUniqueId()){
			$ev->setCancelled();
		}

		$this->level->getServer()->getPluginManager()->callEvent($ev);

		if(!$ev->isCancelled()){
			$this->setText(...$ev->getLines());
			return true;
		}else{
			return false;
		}
	}

}
