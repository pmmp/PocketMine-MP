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

namespace pocketmine\data\bedrock\blockstate\upgrade\model;

final class BlockStateUpgradeSchemaModelBlockRemap{

	/**
	 * @var BlockStateUpgradeSchemaModelTag[]
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelTag>
	 * @required
	 */
	public array $oldState;

	/** @required */
	public string $newName;

	/**
	 * @var BlockStateUpgradeSchemaModelTag[]
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelTag>
	 * @required
	 */
	public array $newState;

	/**
	 * @param BlockStateUpgradeSchemaModelTag[] $oldState
	 * @param BlockStateUpgradeSchemaModelTag[] $newState
	 * @phpstan-param array<string, BlockStateUpgradeSchemaModelTag> $oldState
	 * @phpstan-param array<string, BlockStateUpgradeSchemaModelTag> $newState
	 */
	public function __construct(array $oldState, string $newName, array $newState){
		$this->oldState = $oldState;
		$this->newName = $newName;
		$this->newState = $newState;
	}
}
