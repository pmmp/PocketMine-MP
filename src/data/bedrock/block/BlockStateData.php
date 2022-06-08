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

namespace pocketmine\data\bedrock\block;

use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use function array_keys;
use function count;
use function implode;

/**
 * Contains the common information found in a serialized blockstate.
 */
final class BlockStateData{
	/**
	 * Bedrock version of the most recent backwards-incompatible change to blockstates.
	 */
	public const CURRENT_VERSION =
		(1 << 24) | //major
		(18 << 16) | //minor
		(10 << 8) | //patch
		(1); //revision

	public const TAG_NAME = "name";
	public const TAG_STATES = "states";
	public const TAG_VERSION = "version";

	public function __construct(
		private string $name,
		private CompoundTag $states,
		private int $version
	){}

	public function getName() : string{ return $this->name; }

	public function getStates() : CompoundTag{ return $this->states; }

	public function getVersion() : int{ return $this->version; }

	/**
	 * @throws BlockStateDeserializeException
	 */
	public static function fromNbt(CompoundTag $nbt) : self{
		try{
			$name = $nbt->getString(self::TAG_NAME);
			$states = $nbt->getCompoundTag(self::TAG_STATES) ?? throw new BlockStateDeserializeException("Missing tag \"" . self::TAG_STATES . "\"");
			$version = $nbt->getInt(self::TAG_VERSION, 0);
		}catch(NbtException $e){
			throw new BlockStateDeserializeException($e->getMessage(), 0, $e);
		}

		$allKeys = $nbt->getValue();
		unset($allKeys[self::TAG_NAME], $allKeys[self::TAG_STATES], $allKeys[self::TAG_VERSION]);
		if(count($allKeys) !== 0){
			throw new BlockStateDeserializeException("Unexpected extra keys: " . implode(", ", array_keys($allKeys)));
		}

		return new self($name, $states, $version);
	}

	public function toNbt() : CompoundTag{
		return CompoundTag::create()
			->setString(self::TAG_NAME, $this->name)
			->setInt(self::TAG_VERSION, $this->version)
			->setTag(self::TAG_STATES, $this->states);
	}

	public function equals(self $that) : bool{
		return
			$this->name === $that->name &&
			$this->states->equals($that->states) &&
			$this->version === $that->version;
	}
}
