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

namespace pocketmine\inventory;

use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\utils\SingletonTrait;

final class CreativeInventoryCache{
	use SingletonTrait;

	/** @var CreativeContentEntry[] $entries */
	private array $entries = [];
	private bool $isHit = false;

	public function isHit() : bool{
		return $this->isHit;
	}

	public function creativeInventoryChanged() : void{
		$this->isHit = false;
	}

	public function getEntries() : array{
		if(!$this->isHit){
			$this->regenerate();
		}
		return $this->entries;
	}

	private function regenerate() : void{
		$this->entries = [];

		$typeConverter = TypeConverter::getInstance();
		//creative inventory may have holes if items were unregistered - ensure network IDs used are always consistent
		foreach(CreativeInventory::getInstance()->getAll() as $k => $item){
			$this->entries[] = new CreativeContentEntry($k, $typeConverter->coreItemStackToNet($item));
		}

		$this->isHit = true;
	}
}
