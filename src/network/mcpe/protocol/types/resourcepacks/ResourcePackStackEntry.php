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

namespace pocketmine\network\mcpe\protocol\types\resourcepacks;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class ResourcePackStackEntry{

	/** @var string */
	private $packId;
	/** @var string */
	private $version;
	/** @var string */
	private $subPackName;

	public function __construct(string $packId, string $version, string $subPackName){
		$this->packId = $packId;
		$this->version = $version;
		$this->subPackName = $subPackName;
	}

	public function getPackId() : string{
		return $this->packId;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getSubPackName() : string{
		return $this->subPackName;
	}

	public function write(PacketSerializer $out) : void{
		$out->putString($this->packId);
		$out->putString($this->version);
		$out->putString($this->subPackName);
	}

	public static function read(PacketSerializer $in) : self{
		$packId = $in->getString();
		$version = $in->getString();
		$subPackName = $in->getString();
		return new self($packId, $version, $subPackName);
	}
}
