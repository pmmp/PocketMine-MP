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
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\Binary;
use pocketmine\world\World;
use function array_pad;
use function array_slice;
use function explode;
use function implode;
use function mb_scrub;
use function sprintf;

/**
 * @deprecated
 * @see \pocketmine\block\BaseSign
 */
class Sign extends Spawnable{
	public const TAG_TEXT_BLOB = "Text";
	public const TAG_TEXT_LINE = "Text%d"; //sprintf()able
	public const TAG_TEXT_COLOR = "SignTextColor";
	public const TAG_GLOWING_TEXT = "IgnoreLighting";
	public const TAG_PERSIST_FORMATTING = "PersistFormatting"; //TAG_Byte
	/**
	 * This tag is set to indicate that MCPE-117835 has been addressed in whatever version this sign was created.
	 * @see https://bugs.mojang.com/browse/MCPE-117835
	 */
	public const TAG_LEGACY_BUG_RESOLVE = "TextIgnoreLegacyBugResolved";

	public const TAG_FRONT_TEXT = "FrontText"; //TAG_Compound
	public const TAG_BACK_TEXT = "BackText"; //TAG_Compound
	public const TAG_WAXED = "IsWaxed"; //TAG_Byte
	public const TAG_LOCKED_FOR_EDITING_BY = "LockedForEditingBy"; //TAG_Long

	/**
	 * @return string[]
	 */
	public static function fixTextBlob(string $blob) : array{
		return array_slice(array_pad(explode("\n", $blob), 4, ""), 0, 4);
	}

	protected SignText $text;
	private bool $waxed = false;

	protected ?int $editorEntityRuntimeId = null;

	public function __construct(World $world, Vector3 $pos){
		$this->text = new SignText();
		parent::__construct($world, $pos);
	}

	private function readTextTag(CompoundTag $nbt, bool $lightingBugResolved) : void{
		$baseColor = new Color(0, 0, 0);
		$glowingText = false;
		if(($baseColorTag = $nbt->getTag(self::TAG_TEXT_COLOR)) instanceof IntTag){
			$baseColor = Color::fromARGB(Binary::unsignInt($baseColorTag->getValue()));
		}
		if($lightingBugResolved && ($glowingTextTag = $nbt->getTag(self::TAG_GLOWING_TEXT)) instanceof ByteTag){
			//both of these must be 1 - if only one is set, it's a leftover from 1.16.210 experimental features
			//see https://bugs.mojang.com/browse/MCPE-117835
			$glowingText = $glowingTextTag->getValue() !== 0;
		}
		$this->text = SignText::fromBlob(mb_scrub($nbt->getString(self::TAG_TEXT_BLOB), 'UTF-8'), $baseColor, $glowingText);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$frontTextTag = $nbt->getTag(self::TAG_FRONT_TEXT);
		if($frontTextTag instanceof CompoundTag){
			$this->readTextTag($frontTextTag, true);
		}elseif($nbt->getTag(self::TAG_TEXT_BLOB) instanceof StringTag){ //MCPE 1.2 save format
			$lightingBugResolved = false;
			if(($lightingBugResolvedTag = $nbt->getTag(self::TAG_LEGACY_BUG_RESOLVE)) instanceof ByteTag){
				$lightingBugResolved = $lightingBugResolvedTag->getValue() !== 0;
			}
			$this->readTextTag($nbt, $lightingBugResolved);
		}else{
			$text = [];
			for($i = 0; $i < SignText::LINE_COUNT; ++$i){
				$textKey = sprintf(self::TAG_TEXT_LINE, $i + 1);
				if(($lineTag = $nbt->getTag($textKey)) instanceof StringTag){
					$text[$i] = mb_scrub($lineTag->getValue(), 'UTF-8');
				}
			}
			$this->text = new SignText($text);
		}
		$this->waxed = $nbt->getByte(self::TAG_WAXED, 0) !== 0;
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setTag(self::TAG_FRONT_TEXT, CompoundTag::create()
			->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text->getLines()))
			->setInt(self::TAG_TEXT_COLOR, Binary::signInt($this->text->getBaseColor()->toARGB()))
			->setByte(self::TAG_GLOWING_TEXT, $this->text->isGlowing() ? 1 : 0)
			->setByte(self::TAG_PERSIST_FORMATTING, 1)
		);
		$nbt->setTag(self::TAG_BACK_TEXT, CompoundTag::create()
			->setString(self::TAG_TEXT_BLOB, "")
			->setInt(self::TAG_TEXT_COLOR, Binary::signInt(0xff_00_00_00))
			->setByte(self::TAG_GLOWING_TEXT, 0)
			->setByte(self::TAG_PERSIST_FORMATTING, 1)
		);

		$nbt->setByte(self::TAG_WAXED, $this->waxed ? 1 : 0);
	}

	public function getText() : SignText{
		return $this->text;
	}

	public function setText(SignText $text) : void{
		$this->text = $text;
	}

	public function isWaxed() : bool{ return $this->waxed; }

	public function setWaxed(bool $waxed) : void{ $this->waxed = $waxed; }

	/**
	 * Returns the entity runtime ID of the player who placed this sign. Only the player whose entity ID matches this
	 * one may edit the sign text.
	 * This is needed because as of 1.16.220, there is still no reliable way to detect when the MCPE client closed the
	 * sign edit GUI, so we have no way to know when the text is finalized. This limits editing of the text to only the
	 * player who placed it, and only while that player is online.
	 * We can say for sure that the sign is finalized if either of the following occurs:
	 * - The player quits (after rejoin, the player's entity runtimeID will be different).
	 * - The chunk is unloaded (on next load, the entity runtimeID will be null, because it's not saved).
	 */
	public function getEditorEntityRuntimeId() : ?int{ return $this->editorEntityRuntimeId; }

	public function setEditorEntityRuntimeId(?int $editorEntityRuntimeId) : void{
		$this->editorEntityRuntimeId = $editorEntityRuntimeId;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setTag(self::TAG_FRONT_TEXT, CompoundTag::create()
			->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text->getLines()))
			->setInt(self::TAG_TEXT_COLOR, Binary::signInt($this->text->getBaseColor()->toARGB()))
			->setByte(self::TAG_GLOWING_TEXT, $this->text->isGlowing() ? 1 : 0)
			->setByte(self::TAG_PERSIST_FORMATTING, 1) //TODO: not sure what this is used for
		);
		//TODO: this is not yet used by the server, but needed to rollback any client-side changes to the back text
		$nbt->setTag(self::TAG_BACK_TEXT, CompoundTag::create()
			->setString(self::TAG_TEXT_BLOB, "")
			->setInt(self::TAG_TEXT_COLOR, Binary::signInt(0xff_00_00_00))
			->setByte(self::TAG_GLOWING_TEXT, 0)
			->setByte(self::TAG_PERSIST_FORMATTING, 1)
		);
		$nbt->setByte(self::TAG_WAXED, $this->waxed ? 1 : 0);
		$nbt->setLong(self::TAG_LOCKED_FOR_EDITING_BY, $this->editorEntityRuntimeId ?? -1);
	}
}
