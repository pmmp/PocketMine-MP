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
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Sign extends Spawnable{

	/** @var string[] */
	protected $text = ["", "", "", ""];

	public function __construct(Level $level, CompoundTag $nbt){
		if(isset($nbt->Text)){ //MCPE 1.2 save format
			$this->text = explode("\n", $nbt->Text->getValue());
			unset($nbt->Text);
		}else{
			for($i = 1; $i <= 4; ++$i){
				$textKey = "Text$i";
				if(isset($nbt->$textKey)){
					$this->text[$i - 1] = $nbt->$textKey->getValue();
					unset($nbt->$textKey);
				}
			}
		}

		parent::__construct($level, $nbt);
	}

	public function saveNBT() : void{
		parent::saveNBT();
		$this->namedtag->Text = new StringTag("Text", implode("\n", $this->text));

		for($i = 1; $i <= 4; ++$i){ //Backwards-compatibility
			$textKey = "Text$i";
			$this->namedtag->$textKey = new StringTag($textKey, $this->getLine($i - 1));
		}

		unset($this->namedtag->Creator);
	}

	/**
	 * Changes contents of the specific lines to the string provided.
	 * Leaves contents of the specific lines as is if null is provided.
	 *
	 * @param null|string $line1
	 * @param null|string $line2
	 * @param null|string $line3
	 * @param null|string $line4
	 */
	public function setText($line1 = "", $line2 = "", $line3 = "", $line4 = ""){
		if($line1 !== null){
			$this->text[0] = $line1;
		}
		if($line2 !== null){
			$this->text[1] = $line2;
		}
		if($line3 !== null){
			$this->text[2] = $line3;
		}
		if($line4 !== null){
			$this->text[3] = $line4;
		}

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

		$this->text[$index] = $line;
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
		return $this->text[$index];
	}

	/**
	 * @return string[]
	 */
	public function getText() : array{
		return $this->text;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->Text = new StringTag("Text", implode("\n", $this->text));
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		if($nbt["id"] !== Tile::SIGN){
			return false;
		}

		if(isset($nbt->Text)){
			$lines = array_pad(explode("\n", $nbt->Text->getValue()), 4, "");
		}else{
			$lines = [
				$nbt->Text1->getValue(),
				$nbt->Text2->getValue(),
				$nbt->Text3->getValue(),
				$nbt->Text4->getValue()
			];
		}

		$removeFormat = $player->getRemoveFormat();

		$ev = new SignChangeEvent($this->getBlock(), $player, array_map(function(string $line) use ($removeFormat){ return TextFormat::clean($line, $removeFormat); }, $lines));

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

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		for($i = 1; $i <= 4; ++$i){
			$key = "Text$i";
			$nbt->$key = new StringTag($key, "");
		}

		if($player !== null){
			$nbt->Creator = new StringTag("Creator", $player->getRawUniqueId());
		}
	}

}
