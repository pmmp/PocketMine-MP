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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\block\Block;
use pocketmine\data\bedrock\block\convert\property\Property;
use pocketmine\data\bedrock\block\convert\property\StringProperty;

/**
 * This class works around a limitation in PHPStan.
 * Ideally, we'd just have a function that accepted ($block, $id, $properties) all together and just have the template
 * type inferred from $block alone.
 * However, there's no way to tell PHPStan to ignore $properties for inference, so we're stuck with this hack.
 *
 * @phpstan-template TBlock of Block
 * @phpstan-template THasIdComponents of bool
 */
final class FlattenedIdModel{

	/**
	 * @var string[]|StringProperty[]
	 * @phpstan-var list<string|StringProperty<contravariant TBlock>>
	 */
	private array $idComponents = [];

	/**
	 * @var Property[]
	 * @phpstan-var list<Property<contravariant TBlock>>
	 */
	private array $properties = [];

	/**
	 * @phpstan-param TBlock $block
	 */
	private function __construct(
		private Block $block
	){}

	/**
	 * @phpstan-template TBlock_ of Block
	 * @phpstan-param TBlock_ $block
	 * @return self<TBlock_, false>
	 */
	public static function create(Block $block) : self{
		/** @phpstan-var self<TBlock_, false> $result */
		$result = new self($block);
		return $result;
	}

	/** @phpstan-return TBlock */
	public function getBlock() : Block{ return $this->block; }

	/**
	 * @return string[]|StringProperty[]
	 * @phpstan-return list<string|StringProperty<contravariant TBlock>>
	 */
	public function getIdComponents() : array{ return $this->idComponents; }

	/**
	 * @return Property[]
	 * @phpstan-return list<Property<contravariant TBlock>>
	 */
	public function getProperties() : array{ return $this->properties; }

	/**
	 * @param string[]|StringProperty[] $components
	 * @phpstan-param non-empty-list<string|StringProperty<contravariant TBlock>> $components
	 * @return $this
	 * @phpstan-this-out self<TBlock, true>
	 */
	public function idComponents(array $components) : self{
		$this->idComponents = $components;
		return $this;
	}

	/**
	 * @param Property[] $properties
	 * @phpstan-param non-empty-list<Property<contravariant TBlock>> $properties
	 * @return $this
	 * @phpstan-this-out self<TBlock, THasIdComponents>
	 */
	public function properties(array $properties) : self{
		$this->properties = $properties;
		return $this;
	}
}
