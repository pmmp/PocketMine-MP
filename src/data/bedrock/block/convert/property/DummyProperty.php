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

namespace pocketmine\data\bedrock\block\convert\property;

use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\utils\AssumptionFailedError;
use function is_bool;
use function is_int;
use function is_string;

/**
 * @phpstan-implements Property<object>
 */
final class DummyProperty implements Property{
	public function __construct(
		private string $name,
		private bool|int|string $value
	){}

	public function getName() : string{
		return $this->name;
	}

	public function deserialize(object $block, BlockStateReader $in) : void{
		$in->ignored($this->name);
	}

	public function serialize(object $block, BlockStateWriter $out) : void{
		if(is_bool($this->value)){
			$out->writeBool($this->name, $this->value);
		}elseif(is_int($this->value)){
			$out->writeInt($this->name, $this->value);
		}elseif(is_string($this->value)){
			$out->writeString($this->name, $this->value);
		}else{
			throw new AssumptionFailedError();
		}
	}
}
