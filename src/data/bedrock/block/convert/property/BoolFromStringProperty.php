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

use pocketmine\block\Block;
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;

/**
 * @phpstan-template TBlock of Block
 * @phpstan-implements StringProperty<TBlock>
 */
final class BoolFromStringProperty implements StringProperty{

	/**
	 * @param \Closure(TBlock) : bool        $getter
	 * @param \Closure(TBlock, bool) : mixed $setter
	 */
	public function __construct(
		private string $name,
		private string $falseValue,
		private string $trueValue,
		private \Closure $getter,
		private \Closure $setter
	){}

	public function getName() : string{
		return $this->name;
	}

	public function getPossibleValues() : array{
		return [$this->falseValue, $this->trueValue];
	}

	public function deserialize(Block $block, BlockStateReader $in) : void{
		$raw = $in->readString($this->name);
		$value = match($raw){
			$this->falseValue => false,
			$this->trueValue => true,
			default => throw new BlockStateSerializeException("Invalid value for {$this->name}: $raw"),
		};

		($this->setter)($block, $value);
	}

	public function serialize(Block $block, BlockStateWriter $out) : void{
		$value = ($this->getter)($block);
		$out->writeString($this->name, $value ? $this->trueValue : $this->falseValue);
	}
}
