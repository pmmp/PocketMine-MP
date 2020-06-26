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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;

final class Enchant{
	/** @var int */
	private $id;
	/** @var int */
	private $level;

	public function __construct(int $id, int $level){
		$this->id = $id;
		$this->level = $level;
	}

	public function getId() : int{ return $this->id; }

	public function getLevel() : int{ return $this->level; }

	public static function read(NetworkBinaryStream $in) : self{
		$id = $in->getByte();
		$level = $in->getByte();
		return new self($id, $level);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->id);
		$out->putByte($this->level);
	}
}
