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

namespace pocketmine\data\bedrock\block\upgrade;

use pocketmine\nbt\tag\Tag;

final class BlockStateUpgradeSchemaBlockRemap{
	/**
	 * @param Tag[]    $oldState
	 * @param Tag[]    $newState
	 * @param string[] $copiedState
	 *
	 * @phpstan-param array<string, Tag> $oldState
	 * @phpstan-param array<string, Tag> $newState
	 * @phpstan-param list<string>       $copiedState
	 */
	public function __construct(
		public array $oldState,
		public string $newName,
		public array $newState,
		public array $copiedState
	){}
}
