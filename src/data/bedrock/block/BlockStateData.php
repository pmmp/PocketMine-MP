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
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use function array_keys;
use function count;
use function implode;

/**
 * Contains the common information found in a serialized blockstate.
 */
final class BlockStateData{
	/**
	 * Bedrock version of the most recent backwards-incompatible change to blockstates.
	 *
	 * This is *not* the same as current game version. It should match the numbers in the
	 * newest blockstate upgrade schema used in BedrockBlockUpgradeSchema.
	 */
	public const CURRENT_VERSION =
		(1 << 24) | //major
		(21 << 16) | //minor
		(40 << 8) | //patch
		(1); //revision

	public const TAG_NAME = "name";
	public const TAG_STATES = "states";
	public const TAG_VERSION = "version";

	/**
	 * @param Tag[] $states
	 * @phpstan-param array<string, Tag> $states
	 */
	public function __construct(
		private string $name,
		private array $states,
		private int $version
	){}

	/**
	 * @param Tag[] $states
	 * @phpstan-param array<string, Tag> $states
	 */
	public static function current(string $name, array $states) : self{
		return new self($name, $states, self::CURRENT_VERSION);
	}

	public function getName() : string{ return $this->name; }

	/**
	 * @return Tag[]
	 * @phpstan-return array<string, Tag>
	 */
	public function getStates() : array{ return $this->states; }

	public function getState(string $name) : ?Tag{
		return $this->states[$name] ?? null;
	}

	public function getVersion() : int{ return $this->version; }

	public function getVersionAsString() : string{
		$major = ($this->version >> 24) & 0xff;
		$minor = ($this->version >> 16) & 0xff;
		$patch = ($this->version >> 8) & 0xff;
		$revision = $this->version & 0xff;
		return "$major.$minor.$patch.$revision";
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public static function fromNbt(CompoundTag $nbt) : self{
		try{
			$name = $nbt->getString(self::TAG_NAME);
			$states = $nbt->getCompoundTag(self::TAG_STATES) ?? throw new BlockStateDeserializeException("Missing tag \"" . self::TAG_STATES . "\"");
			$version = $nbt->getInt(self::TAG_VERSION, 0);
			//TODO: read version from VersionInfo::TAG_WORLD_DATA_VERSION - we may need it to fix up old blockstates
		}catch(NbtException $e){
			throw new BlockStateDeserializeException($e->getMessage(), 0, $e);
		}

		$allKeys = $nbt->getValue();
		unset($allKeys[self::TAG_NAME], $allKeys[self::TAG_STATES], $allKeys[self::TAG_VERSION], $allKeys[VersionInfo::TAG_WORLD_DATA_VERSION]);
		if(count($allKeys) !== 0){
			throw new BlockStateDeserializeException("Unexpected extra keys: " . implode(", ", array_keys($allKeys)));
		}

		return new self($name, $states->getValue(), $version);
	}

	/**
	 * Encodes the blockstate as a TAG_Compound, exactly as it would be in vanilla Bedrock.
	 */
	public function toVanillaNbt() : CompoundTag{
		$statesTag = CompoundTag::create();
		foreach(Utils::stringifyKeys($this->states) as $key => $value){
			$statesTag->setTag($key, $value);
		}
		return CompoundTag::create()
			->setString(self::TAG_NAME, $this->name)
			->setInt(self::TAG_VERSION, $this->version)
			->setTag(self::TAG_STATES, $statesTag);
	}

	/**
	 * Encodes the blockstate as a TAG_Compound, but with extra PM-specific metadata, used for fixing bugs in old saved
	 * data. This should be used for anything saved to disk.
	 */
	public function toNbt() : CompoundTag{
		return $this->toVanillaNbt()
			->setLong(VersionInfo::TAG_WORLD_DATA_VERSION, VersionInfo::WORLD_DATA_VERSION);
	}

	public function equals(self $that) : bool{
		if($this->name !== $that->name || count($this->states) !== count($that->states)){
			return false;
		}
		foreach(Utils::stringifyKeys($this->states) as $k => $v){
			if(!isset($that->states[$k]) || !$that->states[$k]->equals($v)){
				return false;
			}
		}

		return true;
	}
}
