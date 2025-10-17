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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

final class BlockStateWriter{

	/**
	 * @var Tag[]
	 * @phpstan-var array<string, Tag>
	 */
	private array $states = [];

	public function __construct(
		private string $id
	){}

	public static function create(string $id) : self{
		return new self($id);
	}

	/** @return $this */
	public function writeBool(string $name, bool $value) : self{
		$this->states[$name] = new ByteTag($value ? 1 : 0);
		return $this;
	}

	/** @return $this */
	public function writeInt(string $name, int $value) : self{
		$this->states[$name] = new IntTag($value);
		return $this;
	}

	/** @return $this */
	public function writeString(string $name, string $value) : self{
		$this->states[$name] = new StringTag($value);
		return $this;
	}

	public function getBlockStateData() : BlockStateData{
		return BlockStateData::current($this->id, $this->states);
	}
}
