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

final class BlockStateUpgradeSchemaModelBlockRemap{

	/**
	 * @var BlockStateUpgradeSchemaModelTag[]|null
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelTag>|null
	 * @required
	 */
	public ?array $oldState;

	/**
	 * Either this or newFlattenedName must be present
	 * Due to technical limitations of jsonmapper, we can't use a union type here
	 */
	public string $newName;
	/**
	 * Either this or newName must be present
	 * Due to technical limitations of jsonmapper, we can't use a union type here
	 */
	public BlockStateUpgradeSchemaModelFlattenInfo $newFlattenedName;

	/**
	 * @var BlockStateUpgradeSchemaModelTag[]|null
	 * @phpstan-var array<string, BlockStateUpgradeSchemaModelTag>|null
	 * @required
	 */
	public ?array $newState;

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 * May not be present in older schemas
	 */
	public array $copiedState;

	/**
	 * @param BlockStateUpgradeSchemaModelTag[] $oldState
	 * @param BlockStateUpgradeSchemaModelTag[] $newState
	 * @param string[]                          $copiedState
	 * @phpstan-param array<string, BlockStateUpgradeSchemaModelTag> $oldState
	 * @phpstan-param array<string, BlockStateUpgradeSchemaModelTag> $newState
	 * @phpstan-param list<string> $copiedState
	 */
	public function __construct(array $oldState, string|BlockStateUpgradeSchemaModelFlattenInfo $newNameRule, array $newState, array $copiedState){
		$this->oldState = count($oldState) === 0 ? null : $oldState;
		if($newNameRule instanceof BlockStateUpgradeSchemaModelFlattenInfo){
			$this->newFlattenedName = $newNameRule;
		}else{
			$this->newName = $newNameRule;
		}
		$this->newState = count($newState) === 0 ? null : $newState;
		$this->copiedState = $copiedState;
	}
}
