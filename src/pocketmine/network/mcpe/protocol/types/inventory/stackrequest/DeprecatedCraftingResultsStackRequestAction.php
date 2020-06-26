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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\item\Item;
use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;

/**
 * Not clear what this is needed for, but it is very clearly marked as deprecated, so hopefully it'll go away before I
 * have to write a proper description for it.
 */
final class DeprecatedCraftingResultsStackRequestAction extends ItemStackRequestAction{

	/** @var Item[] */
	private $results;
	/** @var int */
	private $iterations;

	/**
	 * @param Item[] $results
	 */
	public function __construct(array $results, int $iterations){
		$this->results = $results;
		$this->iterations = $iterations;
	}

	/** @return Item[] */
	public function getResults() : array{ return $this->results; }

	public function getIterations() : int{ return $this->iterations; }

	public static function getTypeId() : int{
		return ItemStackRequestActionType::CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING;
	}

	public static function read(NetworkBinaryStream $in) : self{
		$results = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$results[] = $in->getSlot();
		}
		$iterations = $in->getByte();
		return new self($results, $iterations);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt(count($this->results));
		foreach($this->results as $result){
			$out->putSlot($result);
		}
		$out->putByte($this->iterations);
	}
}
