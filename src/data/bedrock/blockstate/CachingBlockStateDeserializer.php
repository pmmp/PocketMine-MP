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

namespace pocketmine\data\bedrock\blockstate;

final class CachingBlockStateDeserializer implements BlockStateDeserializer{

	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $simpleCache = [];

	public function __construct(
		private BlockStateDeserializer $realDeserializer
	){}

	public function deserialize(BlockStateData $stateData) : int{
		if($stateData->getStates()->count() === 0){
			//if a block has zero properties, we can keep a map of string ID -> internal blockstate ID
			return $this->simpleCache[$stateData->getName()] ??= $this->realDeserializer->deserialize($stateData);
		}

		//we can't cache blocks that have properties - go ahead and deserialize the slow way
		return $this->realDeserializer->deserialize($stateData);
	}

	public function getRealDeserializer() : BlockStateDeserializer{ return $this->realDeserializer; }
}
