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

namespace pocketmine\data\bedrock;

use const pocketmine\BEDROCK_DATA_PATH;

final class BedrockDataFiles{
	private function __construct(){
		//NOOP
	}

	public const BANNER_PATTERNS_JSON = BEDROCK_DATA_PATH . '/banner_patterns.json';
	public const BIOME_DEFINITIONS_JSON = BEDROCK_DATA_PATH . '/biome_definitions.json';
	public const BIOME_DEFINITIONS_NBT = BEDROCK_DATA_PATH . '/biome_definitions.nbt';
	public const BIOME_DEFINITIONS_FULL_NBT = BEDROCK_DATA_PATH . '/biome_definitions_full.nbt';
	public const BIOME_ID_MAP_JSON = BEDROCK_DATA_PATH . '/biome_id_map.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_20_0_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.20.0.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_20_10_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.20.10.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_20_40_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.20.40.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_20_50_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.20.50.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_20_60_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.20.60.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_20_70_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.20.70.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_20_80_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.20.80.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_21_2_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.21.2.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_21_20_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.21.20.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_21_30_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.21.30.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_21_40_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.21.40.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_21_60_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.21.60.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_1_21_70_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map-1.21.70.json';
	public const BLOCK_ID_TO_ITEM_ID_MAP_JSON = BEDROCK_DATA_PATH . '/block_id_to_item_id_map.json';
	public const BLOCK_PROPERTIES_TABLE_JSON = BEDROCK_DATA_PATH . '/block_properties_table.json';
	public const BLOCK_STATE_META_MAP_1_20_0_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.0.json';
	public const BLOCK_STATE_META_MAP_1_20_10_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.10.json';
	public const BLOCK_STATE_META_MAP_1_20_30_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.30.json';
	public const BLOCK_STATE_META_MAP_1_20_40_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.40.json';
	public const BLOCK_STATE_META_MAP_1_20_50_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.50.json';
	public const BLOCK_STATE_META_MAP_1_20_60_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.60.json';
	public const BLOCK_STATE_META_MAP_1_20_70_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.70.json';
	public const BLOCK_STATE_META_MAP_1_20_80_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.20.80.json';
	public const BLOCK_STATE_META_MAP_1_21_2_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.21.2.json';
	public const BLOCK_STATE_META_MAP_1_21_20_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.21.20.json';
	public const BLOCK_STATE_META_MAP_1_21_30_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.21.30.json';
	public const BLOCK_STATE_META_MAP_1_21_40_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.21.40.json';
	public const BLOCK_STATE_META_MAP_1_21_50_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.21.50.json';
	public const BLOCK_STATE_META_MAP_1_21_60_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.21.60.json';
	public const BLOCK_STATE_META_MAP_1_21_70_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map-1.21.70.json';
	public const BLOCK_STATE_META_MAP_JSON = BEDROCK_DATA_PATH . '/block_state_meta_map.json';
	public const CANONICAL_BLOCK_STATES_1_20_0_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.0.nbt';
	public const CANONICAL_BLOCK_STATES_1_20_10_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.10.nbt';
	public const CANONICAL_BLOCK_STATES_1_20_30_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.30.nbt';
	public const CANONICAL_BLOCK_STATES_1_20_40_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.40.nbt';
	public const CANONICAL_BLOCK_STATES_1_20_50_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.50.nbt';
	public const CANONICAL_BLOCK_STATES_1_20_60_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.60.nbt';
	public const CANONICAL_BLOCK_STATES_1_20_70_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.70.nbt';
	public const CANONICAL_BLOCK_STATES_1_20_80_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.20.80.nbt';
	public const CANONICAL_BLOCK_STATES_1_21_2_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.21.2.nbt';
	public const CANONICAL_BLOCK_STATES_1_21_20_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.21.20.nbt';
	public const CANONICAL_BLOCK_STATES_1_21_30_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.21.30.nbt';
	public const CANONICAL_BLOCK_STATES_1_21_40_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.21.40.nbt';
	public const CANONICAL_BLOCK_STATES_1_21_50_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.21.50.nbt';
	public const CANONICAL_BLOCK_STATES_1_21_60_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.21.60.nbt';
	public const CANONICAL_BLOCK_STATES_1_21_70_NBT = BEDROCK_DATA_PATH . '/canonical_block_states-1.21.70.nbt';
	public const CANONICAL_BLOCK_STATES_NBT = BEDROCK_DATA_PATH . '/canonical_block_states.nbt';
	public const COMMAND_ARG_TYPES_JSON = BEDROCK_DATA_PATH . '/command_arg_types.json';
	public const CREATIVE = BEDROCK_DATA_PATH . '/creative';
	public const ENTITY_ID_MAP_JSON = BEDROCK_DATA_PATH . '/entity_id_map.json';
	public const ENTITY_IDENTIFIERS_NBT = BEDROCK_DATA_PATH . '/entity_identifiers.nbt';
	public const ENUMS = BEDROCK_DATA_PATH . '/enums';
	public const ENUMS_PY = BEDROCK_DATA_PATH . '/enums.py';
	public const ITEM_TAGS_1_20_0_JSON = BEDROCK_DATA_PATH . '/item_tags-1.20.0.json';
	public const ITEM_TAGS_JSON = BEDROCK_DATA_PATH . '/item_tags.json';
	public const LEVEL_SOUND_ID_MAP_JSON = BEDROCK_DATA_PATH . '/level_sound_id_map.json';
	public const PROTOCOL_INFO_JSON = BEDROCK_DATA_PATH . '/protocol_info.json';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_0_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.0.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_10_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.10.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_30_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.30.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_40_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.40.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_50_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.50.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_60_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.60.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_70_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.70.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_20_80_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.20.80.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_21_2_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.21.2.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_21_20_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.21.20.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_21_30_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.21.30.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_1_21_50_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map-1.21.50.bin';
	public const R12_TO_CURRENT_BLOCK_MAP_BIN = BEDROCK_DATA_PATH . '/r12_to_current_block_map.bin';
	public const R16_TO_CURRENT_ITEM_MAP_JSON = BEDROCK_DATA_PATH . '/r16_to_current_item_map.json';
	public const RECIPES = BEDROCK_DATA_PATH . '/recipes';
	public const REQUIRED_ITEM_LIST_1_20_0_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.20.0.json';
	public const REQUIRED_ITEM_LIST_1_20_10_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.20.10.json';
	public const REQUIRED_ITEM_LIST_1_20_40_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.20.40.json';
	public const REQUIRED_ITEM_LIST_1_20_50_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.20.50.json';
	public const REQUIRED_ITEM_LIST_1_20_60_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.20.60.json';
	public const REQUIRED_ITEM_LIST_1_20_70_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.20.70.json';
	public const REQUIRED_ITEM_LIST_1_20_80_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.20.80.json';
	public const REQUIRED_ITEM_LIST_1_21_2_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.2.json';
	public const REQUIRED_ITEM_LIST_1_21_20_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.20.json';
	public const REQUIRED_ITEM_LIST_1_21_30_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.30.json';
	public const REQUIRED_ITEM_LIST_1_21_40_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.40.json';
	public const REQUIRED_ITEM_LIST_1_21_50_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.50.json';
	public const REQUIRED_ITEM_LIST_1_21_60_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.60.json';
	public const REQUIRED_ITEM_LIST_1_21_70_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.70.json';
	public const REQUIRED_ITEM_LIST_1_21_80_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.80.json';
	public const REQUIRED_ITEM_LIST_1_21_90_JSON = BEDROCK_DATA_PATH . '/required_item_list-1.21.90.json';
	public const REQUIRED_ITEM_LIST_JSON = BEDROCK_DATA_PATH . '/required_item_list.json';
}
