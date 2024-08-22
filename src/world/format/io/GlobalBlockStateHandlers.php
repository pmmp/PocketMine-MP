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
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\data\bedrock\block\convert\BlockObjectToStateSerializer;
use pocketmine\data\bedrock\block\convert\BlockStateToObjectDeserializer;
use pocketmine\data\bedrock\block\upgrade\BlockDataUpgrader;
use pocketmine\data\bedrock\block\upgrade\BlockIdMetaUpgrader;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgrader;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;
use pocketmine\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use pocketmine\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use const PHP_INT_MAX;
use const pocketmine\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH;

/**
 * Provides global access to blockstate serializers for all world providers.
 * TODO: Get rid of this. This is necessary to enable plugins to register custom serialize/deserialize handlers, and
 * also because we can't break BC of WorldProvider before PM5. While this is a sucky hack, it provides meaningful
 * benefits for now.
 */
final class GlobalBlockStateHandlers{
	private static ?BlockObjectToStateSerializer $blockStateSerializer = null;

	private static ?BlockStateToObjectDeserializer $blockStateDeserializer = null;

	private static ?BlockDataUpgrader $blockDataUpgrader = null;

	private static ?BlockStateData $unknownBlockStateData = null;

	public static function getDeserializer() : BlockStateToObjectDeserializer{
		return self::$blockStateDeserializer ??= new BlockStateToObjectDeserializer();
	}

	public static function getSerializer() : BlockObjectToStateSerializer{
		return self::$blockStateSerializer ??= new BlockObjectToStateSerializer();
	}

	public static function getUpgrader() : BlockDataUpgrader{
		if(self::$blockDataUpgrader === null){
			$blockStateUpgrader = new BlockStateUpgrader(BlockStateUpgradeSchemaUtils::loadSchemas(
				Path::join(BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH, 'nbt_upgrade_schema'),
				PHP_INT_MAX
			));
			self::$blockDataUpgrader = new BlockDataUpgrader(
				BlockIdMetaUpgrader::loadFromString(
					Filesystem::fileGetContents(Path::join(
						BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH,
						'id_meta_to_nbt/1.12.0.bin'
					)),
					LegacyBlockIdToStringIdMap::getInstance(),
					$blockStateUpgrader
				),
				$blockStateUpgrader
			);
		}

		return self::$blockDataUpgrader;
	}

	public static function getUnknownBlockStateData() : BlockStateData{
		return self::$unknownBlockStateData ??= BlockStateData::current(BlockTypeNames::INFO_UPDATE, []);
	}
}
