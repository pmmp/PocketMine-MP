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

namespace pocketmine\block\utils;

use pocketmine\utils\EnumTrait;
use pocketmine\world\generator\object\TreeType;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static SaplingType ACACIA()
 * @method static SaplingType BIRCH()
 * @method static SaplingType DARK_OAK()
 * @method static SaplingType JUNGLE()
 * @method static SaplingType OAK()
 * @method static SaplingType SPRUCE()
 */
final class SaplingType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("oak", TreeType::OAK()),
			new self("spruce", TreeType::SPRUCE()),
			new self("birch", TreeType::BIRCH()),
			new self("jungle", TreeType::JUNGLE()),
			new self("acacia", TreeType::ACACIA()),
			new self("dark_oak", TreeType::DARK_OAK()),
			//TODO: cherry
		);
	}

	private function __construct(
		string $enumName,
		private TreeType $treeType,
	){
		$this->Enum___construct($enumName);
	}

	public function getTreeType() : TreeType{ return $this->treeType; }

	public function getDisplayName() : string{
		return $this->treeType->getDisplayName();
	}
}
