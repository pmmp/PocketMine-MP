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
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;

/**
 * @phpstan-template TBlock of Block
 */
final class BlockDataModel{
	/**
	 * @var Property[]
	 * @phpstan-var array<string, Property<TBlock>>
	 */
	private array $properties = [];

	/**
	 * @phpstan-param TBlock $blockTemplate
	 */
	private function __construct(
		private Block $blockTemplate,
		private string $id
	){
		$this->blockTemplate = clone $this->blockTemplate;
	}

	/**
	 * @phpstan-template TBlock_ of Block
	 * @phpstan-param TBlock_ $blockTemplate
	 * @phpstan-return self<TBlock_>
	 */
	public static function create(Block $blockTemplate, string $id) : self{
		return new self($blockTemplate, $id);
	}

	public function getId() : string{ return $this->id; }

	/** @phpstan-return TBlock */
	public function getBlockTemplate() : Block{ return $this->blockTemplate; }

	/**
	 * @phpstan-param \Closure(TBlock) : bool $getter
	 * @phpstan-param \Closure(TBlock, bool) : mixed $setter
	 * @return $this
	 */
	public function bool(string $name, \Closure $getter, \Closure $setter) : self{
		$this->properties[$name] = new BoolProperty($name, $getter, $setter);
		return $this;
	}

	/**
	 * @phpstan-param \Closure(TBlock) : int $getter
	 * @phpstan-param \Closure(TBlock, int) : mixed $setter
	 * @return $this
	 */
	public function int(string $name, int $min, int $max, \Closure $getter, \Closure $setter) : self{
		$this->properties[$name] = new IntProperty($name, $min, $max, $getter, $setter);
		return $this;
	}

	/**
	 * @phpstan-return TBlock
	 */
	public function deserialize(BlockStateReader $in) : Block{
		$block = clone $this->blockTemplate;
		foreach($this->properties as $property){
			$property->deserialize($block, $in);
		}
		return $block;
	}

	/**
	 * @phpstan-param TBlock $block
	 */
	public function serialize(Block $block) : BlockStateWriter{
		$writer = new BlockStateWriter($this->id);
		foreach($this->properties as $property){
			$property->serialize($block, $writer);
		}
		return $writer;
	}
}
