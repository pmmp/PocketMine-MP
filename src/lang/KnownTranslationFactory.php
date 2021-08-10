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

namespace pocketmine\lang;

/**
 * This class contains factory methods for all the translations known to PocketMine-MP as per the used version of
 * pmmp/Language.
 * This class is generated automatically, do NOT modify it by hand.
 */
final class KnownTranslationFactory{
	public static function ability_flight() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::ABILITY_FLIGHT, []);
	}

	public static function ability_noclip() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::ABILITY_NOCLIP, []);
	}

	public static function accept_license() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::ACCEPT_LICENSE, []);
	}

	public static function chat_type_achievement(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::CHAT_TYPE_ACHIEVEMENT, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_admin(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::CHAT_TYPE_ADMIN, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_announcement(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::CHAT_TYPE_ANNOUNCEMENT, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_emote(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::CHAT_TYPE_EMOTE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_text(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::CHAT_TYPE_TEXT, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_ban_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BAN_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_ban_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BAN_USAGE, []);
	}

	public static function commands_banip_invalid() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BANIP_INVALID, []);
	}

	public static function commands_banip_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BANIP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_banip_success_players(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BANIP_SUCCESS_PLAYERS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_banip_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BANIP_USAGE, []);
	}

	public static function commands_banlist_ips(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BANLIST_IPS, [
			0 => $param0,
		]);
	}

	public static function commands_banlist_players(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BANLIST_PLAYERS, [
			0 => $param0,
		]);
	}

	public static function commands_banlist_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_BANLIST_USAGE, []);
	}

	public static function commands_clear_failure_no_items(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_CLEAR_FAILURE_NO_ITEMS, [
			0 => $param0,
		]);
	}

	public static function commands_clear_success(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_CLEAR_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_clear_testing(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_CLEAR_TESTING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_defaultgamemode_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_DEFAULTGAMEMODE_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_defaultgamemode_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_DEFAULTGAMEMODE_USAGE, []);
	}

	public static function commands_deop_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_DEOP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_deop_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_DEOP_USAGE, []);
	}

	public static function commands_difficulty_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_DIFFICULTY_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_difficulty_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_DIFFICULTY_USAGE, []);
	}

	public static function commands_effect_failure_notActive(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_FAILURE_NOTACTIVE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_effect_failure_notActive_all(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_FAILURE_NOTACTIVE_ALL, [
			0 => $param0,
		]);
	}

	public static function commands_effect_notFound(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_effect_success(string $param0, string $param1, string $param2, string $param3) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function commands_effect_success_removed(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS_REMOVED, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_effect_success_removed_all(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS_REMOVED_ALL, [
			0 => $param0,
		]);
	}

	public static function commands_effect_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_USAGE, []);
	}

	public static function commands_enchant_noItem() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_ENCHANT_NOITEM, []);
	}

	public static function commands_enchant_notFound(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_ENCHANT_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_enchant_success() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_ENCHANT_SUCCESS, []);
	}

	public static function commands_enchant_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_ENCHANT_USAGE, []);
	}

	public static function commands_gamemode_success_other(string $param1, string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GAMEMODE_SUCCESS_OTHER, [
			1 => $param1,
			0 => $param0,
		]);
	}

	public static function commands_gamemode_success_self(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GAMEMODE_SUCCESS_SELF, [
			0 => $param0,
		]);
	}

	public static function commands_gamemode_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GAMEMODE_USAGE, []);
	}

	public static function commands_generic_notFound() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_NOTFOUND, []);
	}

	public static function commands_generic_num_tooBig(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_NUM_TOOBIG, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_generic_num_tooSmall(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_NUM_TOOSMALL, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_generic_permission() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION, []);
	}

	public static function commands_generic_player_notFound() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_PLAYER_NOTFOUND, []);
	}

	public static function commands_generic_usage(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GENERIC_USAGE, [
			0 => $param0,
		]);
	}

	public static function commands_give_item_notFound(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GIVE_ITEM_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_give_success(string $param0, string $param1, string $param2) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GIVE_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function commands_give_tagError(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_GIVE_TAGERROR, [
			0 => $param0,
		]);
	}

	public static function commands_help_header(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_HELP_HEADER, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_help_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_HELP_USAGE, []);
	}

	public static function commands_kick_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_KICK_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_kick_success_reason(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_KICK_SUCCESS_REASON, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_kick_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_KICK_USAGE, []);
	}

	public static function commands_kill_successful(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_KILL_SUCCESSFUL, [
			0 => $param0,
		]);
	}

	public static function commands_me_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_ME_USAGE, []);
	}

	public static function commands_message_display_incoming(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_MESSAGE_DISPLAY_INCOMING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_message_display_outgoing(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_MESSAGE_DISPLAY_OUTGOING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_message_sameTarget() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_MESSAGE_SAMETARGET, []);
	}

	public static function commands_message_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_MESSAGE_USAGE, []);
	}

	public static function commands_op_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_OP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_op_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_OP_USAGE, []);
	}

	public static function commands_particle_notFound(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_PARTICLE_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_particle_success(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_PARTICLE_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_players_list(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_PLAYERS_LIST, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_players_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_PLAYERS_USAGE, []);
	}

	public static function commands_save_off_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAVE_OFF_USAGE, []);
	}

	public static function commands_save_on_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAVE_ON_USAGE, []);
	}

	public static function commands_save_disabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAVE_DISABLED, []);
	}

	public static function commands_save_enabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAVE_ENABLED, []);
	}

	public static function commands_save_start() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAVE_START, []);
	}

	public static function commands_save_success() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAVE_SUCCESS, []);
	}

	public static function commands_save_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAVE_USAGE, []);
	}

	public static function commands_say_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SAY_USAGE, []);
	}

	public static function commands_seed_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SEED_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_seed_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SEED_USAGE, []);
	}

	public static function commands_setworldspawn_success(string $param0, string $param1, string $param2) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SETWORLDSPAWN_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function commands_setworldspawn_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SETWORLDSPAWN_USAGE, []);
	}

	public static function commands_spawnpoint_success(string $param0, string $param1, string $param2, string $param3) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SPAWNPOINT_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function commands_spawnpoint_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_SPAWNPOINT_USAGE, []);
	}

	public static function commands_stop_start() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_STOP_START, []);
	}

	public static function commands_stop_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_STOP_USAGE, []);
	}

	public static function commands_time_added(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TIME_ADDED, [
			0 => $param0,
		]);
	}

	public static function commands_time_query(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TIME_QUERY, [
			0 => $param0,
		]);
	}

	public static function commands_time_set(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TIME_SET, [
			0 => $param0,
		]);
	}

	public static function commands_title_success() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TITLE_SUCCESS, []);
	}

	public static function commands_title_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TITLE_USAGE, []);
	}

	public static function commands_tp_success(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TP_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_tp_success_coordinates(string $param0, string $param1, string $param2, string $param3) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TP_SUCCESS_COORDINATES, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function commands_tp_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_TP_USAGE, []);
	}

	public static function commands_unban_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_UNBAN_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_unban_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_UNBAN_USAGE, []);
	}

	public static function commands_unbanip_invalid() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_UNBANIP_INVALID, []);
	}

	public static function commands_unbanip_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_UNBANIP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_unbanip_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_UNBANIP_USAGE, []);
	}

	public static function commands_whitelist_add_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_ADD_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_whitelist_add_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_ADD_USAGE, []);
	}

	public static function commands_whitelist_disabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_DISABLED, []);
	}

	public static function commands_whitelist_enabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_ENABLED, []);
	}

	public static function commands_whitelist_list(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_LIST, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_whitelist_reloaded() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_RELOADED, []);
	}

	public static function commands_whitelist_remove_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_whitelist_remove_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_USAGE, []);
	}

	public static function commands_whitelist_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::COMMANDS_WHITELIST_USAGE, []);
	}

	public static function death_attack_arrow(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_ARROW, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_arrow_item(string $param0, string $param1, string $param2) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_ARROW_ITEM, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function death_attack_cactus(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_CACTUS, [
			0 => $param0,
		]);
	}

	public static function death_attack_drown(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_DROWN, [
			0 => $param0,
		]);
	}

	public static function death_attack_explosion(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_EXPLOSION, [
			0 => $param0,
		]);
	}

	public static function death_attack_explosion_player(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_EXPLOSION_PLAYER, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_fall(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_FALL, [
			0 => $param0,
		]);
	}

	public static function death_attack_generic(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_GENERIC, [
			0 => $param0,
		]);
	}

	public static function death_attack_inFire(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_INFIRE, [
			0 => $param0,
		]);
	}

	public static function death_attack_inWall(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_INWALL, [
			0 => $param0,
		]);
	}

	public static function death_attack_lava(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_LAVA, [
			0 => $param0,
		]);
	}

	public static function death_attack_magic(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_MAGIC, [
			0 => $param0,
		]);
	}

	public static function death_attack_mob(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_MOB, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_onFire(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_ONFIRE, [
			0 => $param0,
		]);
	}

	public static function death_attack_outOfWorld(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_OUTOFWORLD, [
			0 => $param0,
		]);
	}

	public static function death_attack_player(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_PLAYER, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_player_item(string $param0, string $param1, string $param2) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_PLAYER_ITEM, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function death_attack_wither(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_ATTACK_WITHER, [
			0 => $param0,
		]);
	}

	public static function death_fell_accident_generic(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEATH_FELL_ACCIDENT_GENERIC, [
			0 => $param0,
		]);
	}

	public static function default_gamemode() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEFAULT_GAMEMODE, []);
	}

	public static function default_values_info() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DEFAULT_VALUES_INFO, []);
	}

	public static function disconnectionScreen_invalidName() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_INVALIDNAME, []);
	}

	public static function disconnectionScreen_invalidSkin() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_INVALIDSKIN, []);
	}

	public static function disconnectionScreen_noReason() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_NOREASON, []);
	}

	public static function disconnectionScreen_notAuthenticated() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_NOTAUTHENTICATED, []);
	}

	public static function disconnectionScreen_outdatedClient() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_OUTDATEDCLIENT, []);
	}

	public static function disconnectionScreen_outdatedServer() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_OUTDATEDSERVER, []);
	}

	public static function disconnectionScreen_resourcePack() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_RESOURCEPACK, []);
	}

	public static function disconnectionScreen_serverFull() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::DISCONNECTIONSCREEN_SERVERFULL, []);
	}

	public static function gameMode_adventure() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::GAMEMODE_ADVENTURE, []);
	}

	public static function gameMode_changed() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::GAMEMODE_CHANGED, []);
	}

	public static function gameMode_creative() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::GAMEMODE_CREATIVE, []);
	}

	public static function gameMode_spectator() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::GAMEMODE_SPECTATOR, []);
	}

	public static function gameMode_survival() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::GAMEMODE_SURVIVAL, []);
	}

	public static function gamemode_info() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::GAMEMODE_INFO, []);
	}

	public static function invalid_port() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::INVALID_PORT, []);
	}

	public static function ip_confirm() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::IP_CONFIRM, []);
	}

	public static function ip_get() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::IP_GET, []);
	}

	public static function ip_warning(string $EXTERNAL_IP, string $INTERNAL_IP) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::IP_WARNING, [
			"EXTERNAL_IP" => $EXTERNAL_IP,
			"INTERNAL_IP" => $INTERNAL_IP,
		]);
	}

	public static function kick_admin() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::KICK_ADMIN, []);
	}

	public static function kick_admin_reason(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::KICK_ADMIN_REASON, [
			0 => $param0,
		]);
	}

	public static function kick_reason_cheat(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::KICK_REASON_CHEAT, [
			0 => $param0,
		]);
	}

	public static function language_name() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::LANGUAGE_NAME, []);
	}

	public static function language_selected(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::LANGUAGE_SELECTED, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function language_has_been_selected() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::LANGUAGE_HAS_BEEN_SELECTED, []);
	}

	public static function max_players() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::MAX_PLAYERS, []);
	}

	public static function multiplayer_player_joined(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::MULTIPLAYER_PLAYER_JOINED, [
			0 => $param0,
		]);
	}

	public static function multiplayer_player_left(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::MULTIPLAYER_PLAYER_LEFT, [
			0 => $param0,
		]);
	}

	public static function name_your_server() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::NAME_YOUR_SERVER, []);
	}

	public static function op_info() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::OP_INFO, []);
	}

	public static function op_warning() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::OP_WARNING, []);
	}

	public static function op_who() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::OP_WHO, []);
	}

	public static function pocketmine_command_alias_illegal(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_ALIAS_ILLEGAL, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_alias_notFound(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_ALIAS_NOTFOUND, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_command_alias_recursive(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_ALIAS_RECURSIVE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_command_ban_ip_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_BAN_IP_DESCRIPTION, []);
	}

	public static function pocketmine_command_ban_player_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_BAN_PLAYER_DESCRIPTION, []);
	}

	public static function pocketmine_command_banlist_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_BANLIST_DESCRIPTION, []);
	}

	public static function pocketmine_command_clear_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_CLEAR_DESCRIPTION, []);
	}

	public static function pocketmine_command_clear_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_CLEAR_USAGE, []);
	}

	public static function pocketmine_command_defaultgamemode_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_DEFAULTGAMEMODE_DESCRIPTION, []);
	}

	public static function pocketmine_command_deop_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_DEOP_DESCRIPTION, []);
	}

	public static function pocketmine_command_difficulty_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_DIFFICULTY_DESCRIPTION, []);
	}

	public static function pocketmine_command_effect_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_EFFECT_DESCRIPTION, []);
	}

	public static function pocketmine_command_enchant_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_ENCHANT_DESCRIPTION, []);
	}

	public static function pocketmine_command_exception(string $param0, string $param1, string $param2) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_EXCEPTION, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function pocketmine_command_gamemode_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_GAMEMODE_DESCRIPTION, []);
	}

	public static function pocketmine_command_gc_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_GC_DESCRIPTION, []);
	}

	public static function pocketmine_command_gc_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_GC_USAGE, []);
	}

	public static function pocketmine_command_give_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_GIVE_DESCRIPTION, []);
	}

	public static function pocketmine_command_give_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_GIVE_USAGE, []);
	}

	public static function pocketmine_command_help_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_HELP_DESCRIPTION, []);
	}

	public static function pocketmine_command_kick_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_KICK_DESCRIPTION, []);
	}

	public static function pocketmine_command_kill_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_KILL_DESCRIPTION, []);
	}

	public static function pocketmine_command_kill_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_KILL_USAGE, []);
	}

	public static function pocketmine_command_list_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_LIST_DESCRIPTION, []);
	}

	public static function pocketmine_command_me_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_ME_DESCRIPTION, []);
	}

	public static function pocketmine_command_op_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_OP_DESCRIPTION, []);
	}

	public static function pocketmine_command_particle_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_PARTICLE_DESCRIPTION, []);
	}

	public static function pocketmine_command_particle_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_PARTICLE_USAGE, []);
	}

	public static function pocketmine_command_plugins_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_PLUGINS_DESCRIPTION, []);
	}

	public static function pocketmine_command_plugins_success(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_PLUGINS_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_command_plugins_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_PLUGINS_USAGE, []);
	}

	public static function pocketmine_command_save_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_SAVE_DESCRIPTION, []);
	}

	public static function pocketmine_command_saveoff_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_SAVEOFF_DESCRIPTION, []);
	}

	public static function pocketmine_command_saveon_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_SAVEON_DESCRIPTION, []);
	}

	public static function pocketmine_command_say_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_SAY_DESCRIPTION, []);
	}

	public static function pocketmine_command_seed_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_SEED_DESCRIPTION, []);
	}

	public static function pocketmine_command_setworldspawn_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_SETWORLDSPAWN_DESCRIPTION, []);
	}

	public static function pocketmine_command_spawnpoint_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_SPAWNPOINT_DESCRIPTION, []);
	}

	public static function pocketmine_command_status_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_STATUS_DESCRIPTION, []);
	}

	public static function pocketmine_command_status_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_STATUS_USAGE, []);
	}

	public static function pocketmine_command_stop_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_STOP_DESCRIPTION, []);
	}

	public static function pocketmine_command_tell_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TELL_DESCRIPTION, []);
	}

	public static function pocketmine_command_time_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIME_DESCRIPTION, []);
	}

	public static function pocketmine_command_time_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIME_USAGE, []);
	}

	public static function pocketmine_command_timings_alreadyEnabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_ALREADYENABLED, []);
	}

	public static function pocketmine_command_timings_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_DESCRIPTION, []);
	}

	public static function pocketmine_command_timings_disable() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_DISABLE, []);
	}

	public static function pocketmine_command_timings_enable() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_ENABLE, []);
	}

	public static function pocketmine_command_timings_pasteError() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_PASTEERROR, []);
	}

	public static function pocketmine_command_timings_reset() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_RESET, []);
	}

	public static function pocketmine_command_timings_timingsDisabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSDISABLED, []);
	}

	public static function pocketmine_command_timings_timingsRead(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSREAD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_timings_timingsUpload(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSUPLOAD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_timings_timingsWrite(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSWRITE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_timings_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_USAGE, []);
	}

	public static function pocketmine_command_title_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TITLE_DESCRIPTION, []);
	}

	public static function pocketmine_command_tp_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TP_DESCRIPTION, []);
	}

	public static function pocketmine_command_transferserver_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TRANSFERSERVER_DESCRIPTION, []);
	}

	public static function pocketmine_command_transferserver_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_TRANSFERSERVER_USAGE, []);
	}

	public static function pocketmine_command_unban_ip_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_UNBAN_IP_DESCRIPTION, []);
	}

	public static function pocketmine_command_unban_player_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_UNBAN_PLAYER_DESCRIPTION, []);
	}

	public static function pocketmine_command_version_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_DESCRIPTION, []);
	}

	public static function pocketmine_command_version_minecraftVersion(string $color, string $minecraftVersion, string $minecraftProtocolVersion) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_MINECRAFTVERSION, [
			"color" => $color,
			"minecraftVersion" => $minecraftVersion,
			"minecraftProtocolVersion" => $minecraftProtocolVersion,
		]);
	}

	public static function pocketmine_command_version_noSuchPlugin() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_NOSUCHPLUGIN, []);
	}

	public static function pocketmine_command_version_operatingSystem(string $color, string $operatingSystemName) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_OPERATINGSYSTEM, [
			"color" => $color,
			"operatingSystemName" => $operatingSystemName,
		]);
	}

	public static function pocketmine_command_version_phpJitDisabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITDISABLED, []);
	}

	public static function pocketmine_command_version_phpJitEnabled(string $extraJitInfo) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITENABLED, [
			"extraJitInfo" => $extraJitInfo,
		]);
	}

	public static function pocketmine_command_version_phpJitNotSupported() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITNOTSUPPORTED, []);
	}

	public static function pocketmine_command_version_phpJitStatus(string $color, string $jitStatus) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITSTATUS, [
			"color" => $color,
			"jitStatus" => $jitStatus,
		]);
	}

	public static function pocketmine_command_version_phpVersion(string $color, string $phpVersion) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPVERSION, [
			"color" => $color,
			"phpVersion" => $phpVersion,
		]);
	}

	public static function pocketmine_command_version_serverSoftwareName(string $color, string $serverSoftwareName) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_SERVERSOFTWARENAME, [
			"color" => $color,
			"serverSoftwareName" => $serverSoftwareName,
		]);
	}

	public static function pocketmine_command_version_serverSoftwareVersion(string $color, string $serverSoftwareVersion, string $serverGitHash) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_SERVERSOFTWAREVERSION, [
			"color" => $color,
			"serverSoftwareVersion" => $serverSoftwareVersion,
			"serverGitHash" => $serverGitHash,
		]);
	}

	public static function pocketmine_command_version_usage() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_USAGE, []);
	}

	public static function pocketmine_command_whitelist_description() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_COMMAND_WHITELIST_DESCRIPTION, []);
	}

	public static function pocketmine_crash_archive(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_CRASH_ARCHIVE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_crash_create() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_CRASH_CREATE, []);
	}

	public static function pocketmine_crash_error(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_CRASH_ERROR, [
			0 => $param0,
		]);
	}

	public static function pocketmine_crash_submit(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_CRASH_SUBMIT, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_playerCorrupted(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DATA_PLAYERCORRUPTED, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_playerNotFound(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DATA_PLAYERNOTFOUND, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_playerOld(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DATA_PLAYEROLD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_saveError(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DATA_SAVEERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_debug_enable() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DEBUG_ENABLE, []);
	}

	public static function pocketmine_disconnect_incompatibleProtocol(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DISCONNECT_INCOMPATIBLEPROTOCOL, [
			0 => $param0,
		]);
	}

	public static function pocketmine_disconnect_invalidSession(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION, [
			0 => $param0,
		]);
	}

	public static function pocketmine_disconnect_invalidSession_badSignature() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_BADSIGNATURE, []);
	}

	public static function pocketmine_disconnect_invalidSession_missingKey() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_MISSINGKEY, []);
	}

	public static function pocketmine_disconnect_invalidSession_tooEarly() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_TOOEARLY, []);
	}

	public static function pocketmine_disconnect_invalidSession_tooLate() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_TOOLATE, []);
	}

	public static function pocketmine_level_ambiguousFormat(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_AMBIGUOUSFORMAT, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_backgroundGeneration(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_BACKGROUNDGENERATION, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_badDefaultFormat(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_BADDEFAULTFORMAT, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_defaultError() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_DEFAULTERROR, []);
	}

	public static function pocketmine_level_generationError(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_GENERATIONERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_level_loadError(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_LOADERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_level_notFound(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_preparing(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_PREPARING, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_unknownFormat() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_UNKNOWNFORMAT, []);
	}

	public static function pocketmine_level_unloading(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_LEVEL_UNLOADING, [
			0 => $param0,
		]);
	}

	public static function pocketmine_player_invalidEntity(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLAYER_INVALIDENTITY, [
			0 => $param0,
		]);
	}

	public static function pocketmine_player_invalidMove(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLAYER_INVALIDMOVE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_player_logIn(string $param0, string $param1, string $param2, string $param3, string $param4, string $param5, string $param6, string $param7) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLAYER_LOGIN, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
			4 => $param4,
			5 => $param5,
			6 => $param6,
			7 => $param7,
		]);
	}

	public static function pocketmine_player_logOut(string $param0, string $param1, string $param2, string $param3) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLAYER_LOGOUT, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function pocketmine_plugin_aliasError(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_ALIASERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_plugin_ambiguousMinAPI(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_AMBIGUOUSMINAPI, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_circularDependency() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_CIRCULARDEPENDENCY, []);
	}

	public static function pocketmine_plugin_commandError(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_COMMANDERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_plugin_deprecatedEvent(string $param0, string $param1, string $param2) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_DEPRECATEDEVENT, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function pocketmine_plugin_disable(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_DISABLE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_duplicateError(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_DUPLICATEERROR, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_enable(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_ENABLE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_fileError(string $param0, string $param1, string $param2) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_FILEERROR, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function pocketmine_plugin_genericLoadError(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_GENERICLOADERROR, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatibleAPI(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEAPI, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatibleOS(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEOS, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatiblePhpVersion(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEPHPVERSION, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatibleProtocol(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEPROTOCOL, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_load(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_LOAD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_loadError(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_LOADERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_plugin_restrictedName() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_RESTRICTEDNAME, []);
	}

	public static function pocketmine_plugin_spacesDiscouraged(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_SPACESDISCOURAGED, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_unknownDependency(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGIN_UNKNOWNDEPENDENCY, [
			0 => $param0,
		]);
	}

	public static function pocketmine_save_start() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SAVE_START, []);
	}

	public static function pocketmine_save_success(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SAVE_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_auth_disabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_AUTH_DISABLED, []);
	}

	public static function pocketmine_server_auth_enabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_AUTH_ENABLED, []);
	}

	public static function pocketmine_server_authProperty_disabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_AUTHPROPERTY_DISABLED, []);
	}

	public static function pocketmine_server_authProperty_enabled() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_AUTHPROPERTY_ENABLED, []);
	}

	public static function pocketmine_server_authWarning() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_AUTHWARNING, []);
	}

	public static function pocketmine_server_defaultGameMode(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEFAULTGAMEMODE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_error1(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR1, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_error2() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR2, []);
	}

	public static function pocketmine_server_devBuild_error3() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR3, []);
	}

	public static function pocketmine_server_devBuild_error4(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR4, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_error5(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR5, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_warning1(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING1, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_warning2() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING2, []);
	}

	public static function pocketmine_server_devBuild_warning3() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING3, []);
	}

	public static function pocketmine_server_donate(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_DONATE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_info(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_INFO, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_info_extended(string $param0, string $param1, string $param2, string $param3) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_INFO_EXTENDED, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function pocketmine_server_license(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_LICENSE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_networkStart(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_NETWORKSTART, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_query_running(string $param0, string $param1) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_QUERY_RUNNING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_start(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_START, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_startFinished(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_STARTFINISHED, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_tickOverload() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_SERVER_TICKOVERLOAD, []);
	}

	public static function pocketmine_plugins() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_PLUGINS, []);
	}

	public static function pocketmine_will_start(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POCKETMINE_WILL_START, [
			0 => $param0,
		]);
	}

	public static function port_warning() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::PORT_WARNING, []);
	}

	public static function potion_absorption() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_ABSORPTION, []);
	}

	public static function potion_blindness() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_BLINDNESS, []);
	}

	public static function potion_conduitPower() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_CONDUITPOWER, []);
	}

	public static function potion_confusion() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_CONFUSION, []);
	}

	public static function potion_damageBoost() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_DAMAGEBOOST, []);
	}

	public static function potion_digSlowDown() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_DIGSLOWDOWN, []);
	}

	public static function potion_digSpeed() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_DIGSPEED, []);
	}

	public static function potion_fireResistance() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_FIRERESISTANCE, []);
	}

	public static function potion_harm() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_HARM, []);
	}

	public static function potion_heal() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_HEAL, []);
	}

	public static function potion_healthBoost() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_HEALTHBOOST, []);
	}

	public static function potion_hunger() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_HUNGER, []);
	}

	public static function potion_invisibility() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_INVISIBILITY, []);
	}

	public static function potion_jump() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_JUMP, []);
	}

	public static function potion_levitation() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_LEVITATION, []);
	}

	public static function potion_moveSlowdown() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_MOVESLOWDOWN, []);
	}

	public static function potion_moveSpeed() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_MOVESPEED, []);
	}

	public static function potion_nightVision() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_NIGHTVISION, []);
	}

	public static function potion_poison() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_POISON, []);
	}

	public static function potion_regeneration() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_REGENERATION, []);
	}

	public static function potion_resistance() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_RESISTANCE, []);
	}

	public static function potion_saturation() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_SATURATION, []);
	}

	public static function potion_waterBreathing() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_WATERBREATHING, []);
	}

	public static function potion_weakness() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_WEAKNESS, []);
	}

	public static function potion_wither() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::POTION_WITHER, []);
	}

	public static function query_disable() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::QUERY_DISABLE, []);
	}

	public static function query_warning1() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::QUERY_WARNING1, []);
	}

	public static function query_warning2() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::QUERY_WARNING2, []);
	}

	public static function server_port() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::SERVER_PORT, []);
	}

	public static function server_properties() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::SERVER_PROPERTIES, []);
	}

	public static function setting_up_server_now() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::SETTING_UP_SERVER_NOW, []);
	}

	public static function skip_installer() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::SKIP_INSTALLER, []);
	}

	public static function tile_bed_noSleep() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::TILE_BED_NOSLEEP, []);
	}

	public static function tile_bed_occupied() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::TILE_BED_OCCUPIED, []);
	}

	public static function tile_bed_tooFar() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::TILE_BED_TOOFAR, []);
	}

	public static function welcome_to_pocketmine(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::WELCOME_TO_POCKETMINE, [
			0 => $param0,
		]);
	}

	public static function whitelist_enable() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::WHITELIST_ENABLE, []);
	}

	public static function whitelist_info() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::WHITELIST_INFO, []);
	}

	public static function whitelist_warning() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::WHITELIST_WARNING, []);
	}

	public static function you_have_finished() : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::YOU_HAVE_FINISHED, []);
	}

	public static function you_have_to_accept_the_license(string $param0) : TranslationContainer{
		return new TranslationContainer(KnownTranslationKeys::YOU_HAVE_TO_ACCEPT_THE_LICENSE, [
			0 => $param0,
		]);
	}

}
