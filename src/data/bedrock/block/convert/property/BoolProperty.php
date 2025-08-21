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

/**
 * @phpstan-template TBlock of object
 * @phpstan-implements Property<TBlock>
 */
final class BoolProperty implements Property{
	/**
	 * @phpstan-param \Closure(TBlock) : bool $getter
	 * @phpstan-param \Closure(TBlock, bool) : mixed $setter
	 */
	public function __construct(
		private string $name,
		private \Closure $getter,
		private \Closure $setter,
		private bool $inverted = false //we don't *need* this, but it avoids accidentally forgetting a ! in the getter/setter closures (and makes it analysable)
	){}

	/**
	 * @phpstan-return self<object>
	 */
	public static function unused(string $name, bool $serializedValue) : self{
		return new self($name, fn() => $serializedValue, fn() => null);
	}

	public function getName() : string{ return $this->name; }

	/**
	 * @phpstan-param TBlock $block
	 */
	public function deserialize(object $block, BlockStateReader $in) : void{
		$raw = $in->readBool($this->name);
		$value = $raw !== $this->inverted;
		($this->setter)($block, $value);
	}

	/**
	 * @phpstan-param TBlock $block
	 */
	public function serialize(object $block, BlockStateWriter $out) : void{
		$value = ($this->getter)($block);
		$raw = $value !== $this->inverted;
		$out->writeBool($this->name, $raw);
	}
}
