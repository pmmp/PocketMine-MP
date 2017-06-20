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
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Sign extends Spawnable{

	public function __construct(Level $level, CompoundTag $nbt){
		for($i = 1; $i <= 4; ++$i){
			if(!$nbt->exists("Text$i")){
				$nbt->setTag(new StringTag("Text$i", ""));
			}
		}

		parent::__construct($level, $nbt);
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->remove("Creator");
	}

	public function setText($line1 = "", $line2 = "", $line3 = "", $line4 = ""){
		$this->namedtag->setTag(new StringTag("Text1", $line1));
		$this->namedtag->setTag(new StringTag("Text2", $line2));
		$this->namedtag->setTag(new StringTag("Text3", $line3));
		$this->namedtag->setTag(new StringTag("Text4", $line4));
		$this->onChanged();

		return true;
	}

	public function getText(){
		return [
			$this->namedtag->getTag("Text1")->getValue(),
			$this->namedtag->getTag("Text2")->getValue(),
			$this->namedtag->getTag("Text3")->getValue(),
			$this->namedtag->getTag("Text4")->getValue()
		];
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::SIGN),
			$this->namedtag->getTag("Text1"),
			$this->namedtag->getTag("Text2"),
			$this->namedtag->getTag("Text3"),
			$this->namedtag->getTag("Text4"),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z)
		]);
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		if(!$nbt->exists("id") or $nbt->getTag("id")->getValue() !== Tile::SIGN){
			return false;
		}

		$text = [];
		$removeFormat = $player->getRemoveFormat();
		for($i = 1; $i <= 4; ++$i){
			$text[] = $nbt->exists("Text$i") ? TextFormat::clean($nbt->getTag("Text$i")->getValue(), $removeFormat) : "";
		}
		$ev = new SignChangeEvent($this->getBlock(), $player, $text);

		if(!$this->namedtag->exists("Creator") or $this->namedtag->getTag("Creator")->getValue() !== $player->getRawUniqueId()){
			$this->server->getLogger()->debug("Sign changed but creator id not set or mismatch");
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
