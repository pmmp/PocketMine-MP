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

namespace pocketmine\resourcepacks;

class ResourcePackInfoEntry{
	protected $packId; //UUID
	protected $version;
	protected $packSize;

	public function __construct(string $packId, string $version, int $packSize = 0){
		$this->packId = $packId;
		$this->version = $version;
		$this->packSize = $packSize;
	}

	public function getPackId() : string{
		return $this->packId;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getPackSize() : int{
		return $this->packSize;
	}

}
