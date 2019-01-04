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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function array_map;
use function array_pad;
use function array_slice;
use function assert;
use function count;
use function explode;
use function implode;
use function sprintf;

class Sign extends Spawnable{
	public const TAG_TEXT_BLOB = "Text";
	public const TAG_TEXT_LINE = "Text%d"; //sprintf()able

	/** @var string[] */
	protected $text = ["", "", "", ""];

	protected function readSaveData(CompoundTag $nbt) : void{
		if($nbt->hasTag(self::TAG_TEXT_BLOB, StringTag::class)){ //MCPE 1.2 save format
			$this->text = array_pad(explode("\n", $nbt->getString(self::TAG_TEXT_BLOB)), 4, "");
			assert(count($this->text) === 4, "Too many lines!");
		}else{
			for($i = 1; $i <= 4; ++$i){
				$textKey = sprintf(self::TAG_TEXT_LINE, $i);
				if($nbt->hasTag($textKey, StringTag::class)){
					$this->text[$i - 1] = $nbt->getString($textKey);
				}
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text));

		for($i = 1; $i <= 4; ++$i){ //Backwards-compatibility
			$textKey = sprintf(self::TAG_TEXT_LINE, $i);
			$nbt->setString($textKey, $this->getLine($i - 1));
		}
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
	public function setText(?string $line1 = "", ?string $line2 = "", ?string $line3 = "", ?string $line4 = "") : void{
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
	public function setLine(int $index, string $line, bool $update = true) : void{
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

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text));
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		if($nbt->getString("id") !== Tile::SIGN){
			return false;
		}

		if($nbt->hasTag(self::TAG_TEXT_BLOB, StringTag::class)){
			$lines = array_slice(array_pad(explode("\n", $nbt->getString(self::TAG_TEXT_BLOB)), 4, ""), 0, 4);
		}else{
			return false;
		}

		$removeFormat = $player->getRemoveFormat();

		$ev = new SignChangeEvent($this->getBlock(), $player, array_map(function(string $line) use ($removeFormat){ return TextFormat::clean($line, $removeFormat); }, $lines));
		$ev->call();

		if(!$ev->isCancelled()){
			$this->setText(...$ev->getLines());

			return true;
		}else{
			return false;
		}
	}
}
