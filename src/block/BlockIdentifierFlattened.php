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

namespace pocketmine\block;

use function count;

class BlockIdentifierFlattened extends BlockIdentifier{

	/** @var int[] */
	private array $additionalIds;

	/**
	 * @param int[] $additionalIds
	 */
	public function __construct(int $blockId, array $additionalIds, int $variant, ?int $itemId = null, ?string $tileClass = null){
		if(count($additionalIds) === 0){
			throw new \InvalidArgumentException("Expected at least 1 additional ID");
		}
		parent::__construct($blockId, $variant, $itemId, $tileClass);

		$this->additionalIds = $additionalIds;
	}

	public function getAdditionalId(int $index) : int{
		if(!isset($this->additionalIds[$index])){
			throw new \InvalidArgumentException("No such ID at index $index");
		}
		return $this->additionalIds[$index];
	}

	public function getSecondId() : int{
		return $this->getAdditionalId(0);
	}

	public function getAllBlockIds() : array{
		return [$this->getBlockId(), ...$this->additionalIds];
	}
}
