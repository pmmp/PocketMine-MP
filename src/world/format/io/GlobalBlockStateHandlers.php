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

namespace pocketmine\world\format\io;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializer;
use pocketmine\data\bedrock\block\BlockStateSerializer;
use pocketmine\data\bedrock\block\CachingBlockStateDeserializer;
use pocketmine\data\bedrock\block\CachingBlockStateSerializer;
use pocketmine\data\bedrock\block\convert\BlockObjectToBlockStateSerializer;
use pocketmine\data\bedrock\block\convert\BlockStateToBlockObjectDeserializer;
use pocketmine\data\bedrock\block\upgrade\BlockDataUpgrader;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgrader;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;
use pocketmine\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use pocketmine\data\bedrock\block\upgrade\LegacyBlockStateMapper;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use Webmozart\PathUtil\Path;
use function file_get_contents;
use const pocketmine\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH;

/**
 * Provides global access to blockstate serializers for all world providers.
 * TODO: Get rid of this. This is necessary to enable plugins to register custom serialize/deserialize handlers, and
 * also because we can't break BC of WorldProvider before PM5. While this is a sucky hack, it provides meaningful
 * benefits for now.
 */
final class GlobalBlockStateHandlers{

	private static ?BlockStateSerializer $blockStateSerializer = null;

	private static ?BlockStateDeserializer $blockStateDeserializer = null;

	private static ?BlockDataUpgrader $blockDataUpgrader = null;

	public static function getDeserializer() : BlockStateDeserializer{
		return self::$blockStateDeserializer ??= new CachingBlockStateDeserializer(new BlockStateToBlockObjectDeserializer());
	}

	public static function getSerializer() : BlockStateSerializer{
		return self::$blockStateSerializer ??= new CachingBlockStateSerializer(new BlockObjectToBlockStateSerializer());
	}

	public static function getUpgrader() : BlockDataUpgrader{
		if(self::$blockDataUpgrader === null){
			$blockStateUpgrader = new BlockStateUpgrader(BlockStateUpgradeSchemaUtils::loadSchemas(
				Path::join(BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH, 'nbt_upgrade_schema'),
				BlockStateData::CURRENT_VERSION
			));
			self::$blockDataUpgrader = new BlockDataUpgrader(
				LegacyBlockStateMapper::loadFromString(
					ErrorToExceptionHandler::trapAndRemoveFalse(fn() => file_get_contents(Path::join(
						BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH,
						'1.12.0_to_1.18.10_blockstate_map.bin'
					))),
					LegacyBlockIdToStringIdMap::getInstance(),
					$blockStateUpgrader
				),
				$blockStateUpgrader
			);
		}

		return self::$blockDataUpgrader;
	}
}
