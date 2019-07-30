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

class BlockIdentifier{

	/** @var int */
	private $blockId;
	/** @var int */
	private $variant;
	/** @var int|null */
	private $itemId;
	/** @var string|null */
	private $tileClass;

	public function __construct(int $blockId, int $variant = 0, ?int $itemId = null, ?string $tileClass = null){
		$this->blockId = $blockId;
		$this->variant = $variant;
		$this->itemId = $itemId;
		$this->tileClass = $tileClass;
	}

	/**
	 * @return int
	 */
	public function getBlockId() : int{
		return $this->blockId;
	}

	/**
	 * @return int[]
	 */
	public function getAllBlockIds() : array{
		return [$this->blockId];
	}

	/**
	 * @return int
	 */
	public function getVariant() : int{
		return $this->variant;
	}

	/**
	 * @return int
	 */
	public function getItemId() : int{
		return $this->itemId ?? ($this->blockId > 255 ? 255 - $this->blockId : $this->blockId);
	}

	/**
	 * @return string|null
	 */
	public function getTileClass() : ?string{
		return $this->tileClass;
	}
}
