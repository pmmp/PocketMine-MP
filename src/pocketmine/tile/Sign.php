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
	public const TAG_TEXT_BLOB = "Text";
	public const TAG_TEXT_LINE = "Text%d"; //sprintf()able
	public const TAG_CREATOR = "Creator";

	/** @var string[] */
	protected $text = ["", "", "", ""];

	public function __construct(Level $level, CompoundTag $nbt){
		if($nbt->hasTag(self::TAG_TEXT_BLOB, StringTag::class)){ //MCPE 1.2 save format
			$this->text = explode("\n", $nbt->getString(self::TAG_TEXT_BLOB));
			assert(count($this->text) === 4, "Too many lines!");
			$nbt->removeTag(self::TAG_TEXT_BLOB);
		}else{
			for($i = 1; $i <= 4; ++$i){
				$textKey = sprintf(self::TAG_TEXT_LINE, $i);
				if($nbt->hasTag($textKey, StringTag::class)){
					$this->text[$i - 1] = $nbt->getString($textKey);
					$nbt->removeTag($textKey);
				}
			}
		}

		parent::__construct($level, $nbt);
	}

	public function saveNBT() : void{
		parent::saveNBT();
		$this->namedtag->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text));

		for($i = 1; $i <= 4; ++$i){ //Backwards-compatibility
			$textKey = sprintf(self::TAG_TEXT_LINE, $i);
			$this->namedtag->setString($textKey, $this->getLine($i - 1));
		}

		$this->namedtag->removeTag(self::TAG_CREATOR);
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
		$nbt->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text));
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		if($nbt->getString("id") !== Tile::SIGN){
			return false;
		}

		if($nbt->hasTag(self::TAG_TEXT_BLOB, StringTag::class)){
			$lines = array_pad(explode("\n", $nbt->getString(self::TAG_TEXT_BLOB)), 4, "");
		}else{
			$lines = [
				$nbt->getString(sprintf(self::TAG_TEXT_LINE, 1)),
				$nbt->getString(sprintf(self::TAG_TEXT_LINE, 2)),
				$nbt->getString(sprintf(self::TAG_TEXT_LINE, 3)),
				$nbt->getString(sprintf(self::TAG_TEXT_LINE, 4))
			];
		}

		$removeFormat = $player->getRemoveFormat();

		$ev = new SignChangeEvent($this->getBlock(), $player, array_map(function(string $line) use ($removeFormat){ return TextFormat::clean($line, $removeFormat); }, $lines));

		if($this->namedtag->hasTag(self::TAG_CREATOR, StringTag::class) and $this->namedtag->getString(self::TAG_CREATOR) !== $player->getRawUniqueId()){
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
			$nbt->setString(sprintf(self::TAG_TEXT_LINE, $i), "");
		}

		if($player !== null){
			$nbt->setString(self::TAG_CREATOR, $player->getRawUniqueId());
		}
	}

}
