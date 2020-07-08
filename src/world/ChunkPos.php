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

namespace pocketmine\world;

use pocketmine\math\Vector3;

final class ChunkPos{
	/** @var int */
	private $x;
	/** @var int */
	private $z;

	/** @var int */
	public $hash;

	public function __construct(int $chunkX, int $chunkZ){
		$this->x = $chunkX;
		$this->z = $chunkZ;
		$this->hash = World::chunkHash($chunkX, $chunkZ);
	}

	public function getX() : int{
		return $this->x;
	}

	public function getZ() : int{
		return $this->z;
	}

	public function equals(ChunkPos $other) : bool{
		return $this->x === $other->x and $this->z === $other->z;
	}

	public function hash() : int{
		return $this->hash;
	}

	public function add(int $chunkX, int $chunkZ) : self{
		return new self($this->x + $chunkX, $this->z + $chunkZ);
	}

	public function __toString(){
		return "ChunkPos(x=$this->x, z=$this->z)";
	}

	public static function fromHash(int $hash) : self{
		World::getXZ($hash, $chunkX, $chunkZ);
		return new self($chunkX, $chunkZ);
	}

	public static function fromVec3(Vector3 $vector3) : self{
		return new self($vector3->getFloorX() >> 4, $vector3->getFloorZ() >> 4);
	}
}
