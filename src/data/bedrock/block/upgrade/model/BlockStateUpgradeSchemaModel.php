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

namespace pocketmine\data\bedrock\block\upgrade\model;

use function count;
use function is_array;

/**
 * Model for loading upgrade schema data from JSON.
 */
final class BlockStateUpgradeSchemaModel implements \JsonSerializable{
	/** @required */
	public int $maxVersionMajor;
	/** @required */
	public int $maxVersionMinor;
	/** @required */
	public int $maxVersionPatch;
	/** @required */
	public int $maxVersionRevision;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $renamedIds;

	/**
	 * @var BlockStateUpgradeSchemaModelTag[][]
	 * @phpstan-var array<string, array<string, BlockStateUpgradeSchemaModelTag>>
	 */
	public array $addedProperties;

	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<string>>
	 */
	public array $removedProperties;

	/**
	 * @var string[][]
	 * @phpstan-var array<string, array<string, string>>
	 */
	public array $renamedProperties;

	/**
	 * @var string[][]
	 * @phpstan-var array<string, array<string, string>>
	 */
	public array $remappedPropertyValues;

	/**
	 * @var BlockStateUpgradeSchemaModelValueRemap[][]
	 * @phpstan-var array<string, list<BlockStateUpgradeSchemaModelValueRemap>>
	 */
	public array $remappedPropertyValuesIndex;

	/**
	 * @var BlockStateUpgradeSchemaModelFlattenInfo[]
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelFlattenInfo>
	 */
	public array $flattenedProperties;

	/**
	 * @var BlockStateUpgradeSchemaModelBlockRemap[][]
	 * @phpstan-var array<string, list<BlockStateUpgradeSchemaModelBlockRemap>>
	 */
	public array $remappedStates;

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize() : array{
		$result = (array) $this;

		foreach($result as $k => $v){
			if(is_array($v) && count($v) === 0){
				unset($result[$k]);
			}
		}

		return $result;
	}
}
