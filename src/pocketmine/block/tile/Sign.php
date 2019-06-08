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

namespace pocketmine\block\tile;

use pocketmine\block\utils\SignText;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\World;
use function array_pad;
use function array_slice;
use function explode;
use function implode;
use function mb_scrub;
use function sprintf;

/**
 * @deprecated
 * @see \pocketmine\block\Sign
 */
class Sign extends Spawnable{
	public const TAG_TEXT_BLOB = "Text";
	public const TAG_TEXT_LINE = "Text%d"; //sprintf()able

	public static function fixTextBlob(string $blob) : array{
		return array_slice(array_pad(explode("\n", $blob), 4, ""), 0, 4);
	}

	/** @var SignText */
	protected $text;

	public function __construct(World $world, Vector3 $pos){
		$this->text = new SignText();
		parent::__construct($world, $pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if($nbt->hasTag(self::TAG_TEXT_BLOB, StringTag::class)){ //MCPE 1.2 save format
			$this->text = SignText::fromBlob(mb_scrub($nbt->getString(self::TAG_TEXT_BLOB), 'UTF-8'));
		}else{
			$this->text = new SignText();
			for($i = 0; $i < SignText::LINE_COUNT; ++$i){
				$textKey = sprintf(self::TAG_TEXT_LINE, $i + 1);
				if($nbt->hasTag($textKey, StringTag::class)){
					$this->text->setLine($i, mb_scrub($nbt->getString($textKey), 'UTF-8'));
				}
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text->getLines()));

		for($i = 0; $i < SignText::LINE_COUNT; ++$i){ //Backwards-compatibility
			$textKey = sprintf(self::TAG_TEXT_LINE, $i + 1);
			$nbt->setString($textKey, $this->text->getLine($i));
		}
	}

	/**
	 * @return SignText
	 */
	public function getText() : SignText{
		return $this->text;
	}

	/**
	 * @param SignText $text
	 */
	public function setText(SignText $text) : void{
		$this->text = $text;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text->getLines()));
	}
}
