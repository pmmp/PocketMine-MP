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
 *
 * @internal
 */
final class KnownTranslationFactory{
	public static function ability_flight() : Translatable{
		return new Translatable(KnownTranslationKeys::ABILITY_FLIGHT, []);
	}

	public static function ability_noclip() : Translatable{
		return new Translatable(KnownTranslationKeys::ABILITY_NOCLIP, []);
	}

	public static function accept_license() : Translatable{
		return new Translatable(KnownTranslationKeys::ACCEPT_LICENSE, []);
	}

	public static function action_interact_armorstand_equip() : Translatable{
		return new Translatable(KnownTranslationKeys::ACTION_INTERACT_ARMORSTAND_EQUIP, []);
	}

	public static function action_interact_armorstand_pose() : Translatable{
		return new Translatable(KnownTranslationKeys::ACTION_INTERACT_ARMORSTAND_POSE, []);
	}

	public static function action_interact_exit_boat() : Translatable{
		return new Translatable(KnownTranslationKeys::ACTION_INTERACT_EXIT_BOAT, []);
	}

	public static function action_interact_fishing() : Translatable{
		return new Translatable(KnownTranslationKeys::ACTION_INTERACT_FISHING, []);
	}

	public static function action_interact_name() : Translatable{
		return new Translatable(KnownTranslationKeys::ACTION_INTERACT_NAME, []);
	}

	public static function action_interact_ride_boat() : Translatable{
		return new Translatable(KnownTranslationKeys::ACTION_INTERACT_RIDE_BOAT, []);
	}

	public static function action_interact_ride_minecart() : Translatable{
		return new Translatable(KnownTranslationKeys::ACTION_INTERACT_RIDE_MINECART, []);
	}

	public static function chat_type_achievement(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::CHAT_TYPE_ACHIEVEMENT, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_admin(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::CHAT_TYPE_ADMIN, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_announcement(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::CHAT_TYPE_ANNOUNCEMENT, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_emote(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::CHAT_TYPE_EMOTE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function chat_type_text(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::CHAT_TYPE_TEXT, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_ban_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BAN_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_ban_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BAN_USAGE, []);
	}

	public static function commands_banip_invalid() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BANIP_INVALID, []);
	}

	public static function commands_banip_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BANIP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_banip_success_players(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BANIP_SUCCESS_PLAYERS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_banip_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BANIP_USAGE, []);
	}

	public static function commands_banlist_ips(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BANLIST_IPS, [
			0 => $param0,
		]);
	}

	public static function commands_banlist_players(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BANLIST_PLAYERS, [
			0 => $param0,
		]);
	}

	public static function commands_banlist_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_BANLIST_USAGE, []);
	}

	public static function commands_clear_failure_no_items(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_CLEAR_FAILURE_NO_ITEMS, [
			0 => $param0,
		]);
	}

	public static function commands_clear_success(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_CLEAR_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_clear_testing(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_CLEAR_TESTING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_defaultgamemode_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_DEFAULTGAMEMODE_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_defaultgamemode_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_DEFAULTGAMEMODE_USAGE, []);
	}

	public static function commands_deop_message() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_DEOP_MESSAGE, []);
	}

	public static function commands_deop_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_DEOP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_deop_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_DEOP_USAGE, []);
	}

	public static function commands_difficulty_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_DIFFICULTY_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_difficulty_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_DIFFICULTY_USAGE, []);
	}

	public static function commands_effect_failure_notActive(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_EFFECT_FAILURE_NOTACTIVE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_effect_failure_notActive_all(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_EFFECT_FAILURE_NOTACTIVE_ALL, [
			0 => $param0,
		]);
	}

	public static function commands_effect_notFound(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_EFFECT_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_effect_success(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2, Translatable|string $param3) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function commands_effect_success_removed(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS_REMOVED, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_effect_success_removed_all(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS_REMOVED_ALL, [
			0 => $param0,
		]);
	}

	public static function commands_effect_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_EFFECT_USAGE, []);
	}

	public static function commands_enchant_noItem() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_ENCHANT_NOITEM, []);
	}

	public static function commands_enchant_notFound(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_ENCHANT_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_enchant_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_ENCHANT_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_enchant_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_ENCHANT_USAGE, []);
	}

	public static function commands_gamemode_success_other(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GAMEMODE_SUCCESS_OTHER, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_gamemode_success_self(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GAMEMODE_SUCCESS_SELF, [
			0 => $param0,
		]);
	}

	public static function commands_gamemode_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GAMEMODE_USAGE, []);
	}

	public static function commands_generic_notFound() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GENERIC_NOTFOUND, []);
	}

	public static function commands_generic_num_tooBig(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GENERIC_NUM_TOOBIG, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_generic_num_tooSmall(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GENERIC_NUM_TOOSMALL, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_generic_permission() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GENERIC_PERMISSION, []);
	}

	public static function commands_generic_player_notFound() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GENERIC_PLAYER_NOTFOUND, []);
	}

	public static function commands_generic_usage(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GENERIC_USAGE, [
			0 => $param0,
		]);
	}

	public static function commands_give_item_notFound(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GIVE_ITEM_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_give_success(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GIVE_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function commands_give_tagError(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_GIVE_TAGERROR, [
			0 => $param0,
		]);
	}

	public static function commands_help_header(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_HELP_HEADER, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_help_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_HELP_USAGE, []);
	}

	public static function commands_kick_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_KICK_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_kick_success_reason(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_KICK_SUCCESS_REASON, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_kick_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_KICK_USAGE, []);
	}

	public static function commands_kill_successful(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_KILL_SUCCESSFUL, [
			0 => $param0,
		]);
	}

	public static function commands_me_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_ME_USAGE, []);
	}

	public static function commands_message_display_incoming(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_MESSAGE_DISPLAY_INCOMING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_message_display_outgoing(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_MESSAGE_DISPLAY_OUTGOING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_message_sameTarget() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_MESSAGE_SAMETARGET, []);
	}

	public static function commands_message_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_MESSAGE_USAGE, []);
	}

	public static function commands_op_message() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_OP_MESSAGE, []);
	}

	public static function commands_op_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_OP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_op_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_OP_USAGE, []);
	}

	public static function commands_particle_notFound(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_PARTICLE_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function commands_particle_success(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_PARTICLE_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_players_list(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_PLAYERS_LIST, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_save_disabled() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SAVE_DISABLED, []);
	}

	public static function commands_save_enabled() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SAVE_ENABLED, []);
	}

	public static function commands_save_start() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SAVE_START, []);
	}

	public static function commands_save_success() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SAVE_SUCCESS, []);
	}

	public static function commands_say_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SAY_USAGE, []);
	}

	public static function commands_seed_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SEED_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_setworldspawn_success(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SETWORLDSPAWN_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function commands_setworldspawn_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SETWORLDSPAWN_USAGE, []);
	}

	public static function commands_spawnpoint_success(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2, Translatable|string $param3) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SPAWNPOINT_SUCCESS, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function commands_spawnpoint_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_SPAWNPOINT_USAGE, []);
	}

	public static function commands_stop_start() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_STOP_START, []);
	}

	public static function commands_time_added(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TIME_ADDED, [
			0 => $param0,
		]);
	}

	public static function commands_time_query(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TIME_QUERY, [
			0 => $param0,
		]);
	}

	public static function commands_time_set(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TIME_SET, [
			0 => $param0,
		]);
	}

	public static function commands_title_success() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TITLE_SUCCESS, []);
	}

	public static function commands_title_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TITLE_USAGE, []);
	}

	public static function commands_tp_success(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TP_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_tp_success_coordinates(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2, Translatable|string $param3) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TP_SUCCESS_COORDINATES, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function commands_tp_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_TP_USAGE, []);
	}

	public static function commands_unban_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_UNBAN_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_unban_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_UNBAN_USAGE, []);
	}

	public static function commands_unbanip_invalid() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_UNBANIP_INVALID, []);
	}

	public static function commands_unbanip_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_UNBANIP_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_unbanip_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_UNBANIP_USAGE, []);
	}

	public static function commands_whitelist_add_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_ADD_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_whitelist_add_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_ADD_USAGE, []);
	}

	public static function commands_whitelist_disabled() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_DISABLED, []);
	}

	public static function commands_whitelist_enabled() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_ENABLED, []);
	}

	public static function commands_whitelist_list(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_LIST, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function commands_whitelist_reloaded() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_RELOADED, []);
	}

	public static function commands_whitelist_remove_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function commands_whitelist_remove_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_REMOVE_USAGE, []);
	}

	public static function commands_whitelist_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::COMMANDS_WHITELIST_USAGE, []);
	}

	public static function death_attack_anvil(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_ANVIL, [
			0 => $param0,
		]);
	}

	public static function death_attack_arrow(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_ARROW, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_arrow_item(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_ARROW_ITEM, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function death_attack_cactus(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_CACTUS, [
			0 => $param0,
		]);
	}

	public static function death_attack_drown(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_DROWN, [
			0 => $param0,
		]);
	}

	public static function death_attack_explosion(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_EXPLOSION, [
			0 => $param0,
		]);
	}

	public static function death_attack_explosion_player(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_EXPLOSION_PLAYER, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_fall(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_FALL, [
			0 => $param0,
		]);
	}

	public static function death_attack_fallingBlock(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_FALLINGBLOCK, [
			0 => $param0,
		]);
	}

	public static function death_attack_generic(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_GENERIC, [
			0 => $param0,
		]);
	}

	public static function death_attack_inFire(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_INFIRE, [
			0 => $param0,
		]);
	}

	public static function death_attack_inWall(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_INWALL, [
			0 => $param0,
		]);
	}

	public static function death_attack_lava(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_LAVA, [
			0 => $param0,
		]);
	}

	public static function death_attack_magic(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_MAGIC, [
			0 => $param0,
		]);
	}

	public static function death_attack_mob(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_MOB, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_onFire(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_ONFIRE, [
			0 => $param0,
		]);
	}

	public static function death_attack_outOfWorld(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_OUTOFWORLD, [
			0 => $param0,
		]);
	}

	public static function death_attack_player(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_PLAYER, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_player_item(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_PLAYER_ITEM, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function death_attack_trident(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_TRIDENT, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function death_attack_wither(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_ATTACK_WITHER, [
			0 => $param0,
		]);
	}

	public static function death_fell_accident_generic(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::DEATH_FELL_ACCIDENT_GENERIC, [
			0 => $param0,
		]);
	}

	public static function default_gamemode() : Translatable{
		return new Translatable(KnownTranslationKeys::DEFAULT_GAMEMODE, []);
	}

	public static function default_values_info() : Translatable{
		return new Translatable(KnownTranslationKeys::DEFAULT_VALUES_INFO, []);
	}

	public static function disconnectionScreen_invalidName() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_INVALIDNAME, []);
	}

	public static function disconnectionScreen_invalidSkin() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_INVALIDSKIN, []);
	}

	public static function disconnectionScreen_noReason() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_NOREASON, []);
	}

	public static function disconnectionScreen_notAuthenticated() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_NOTAUTHENTICATED, []);
	}

	public static function disconnectionScreen_outdatedClient() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_OUTDATEDCLIENT, []);
	}

	public static function disconnectionScreen_outdatedServer() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_OUTDATEDSERVER, []);
	}

	public static function disconnectionScreen_resourcePack() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_RESOURCEPACK, []);
	}

	public static function disconnectionScreen_serverFull() : Translatable{
		return new Translatable(KnownTranslationKeys::DISCONNECTIONSCREEN_SERVERFULL, []);
	}

	public static function effect_darkness() : Translatable{
		return new Translatable(KnownTranslationKeys::EFFECT_DARKNESS, []);
	}

	public static function enchantment_arrowDamage() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_ARROWDAMAGE, []);
	}

	public static function enchantment_arrowFire() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_ARROWFIRE, []);
	}

	public static function enchantment_arrowInfinite() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_ARROWINFINITE, []);
	}

	public static function enchantment_arrowKnockback() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_ARROWKNOCKBACK, []);
	}

	public static function enchantment_crossbowMultishot() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_CROSSBOWMULTISHOT, []);
	}

	public static function enchantment_crossbowPiercing() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_CROSSBOWPIERCING, []);
	}

	public static function enchantment_crossbowQuickCharge() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_CROSSBOWQUICKCHARGE, []);
	}

	public static function enchantment_curse_binding() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_CURSE_BINDING, []);
	}

	public static function enchantment_curse_vanishing() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_CURSE_VANISHING, []);
	}

	public static function enchantment_damage_all() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_DAMAGE_ALL, []);
	}

	public static function enchantment_damage_arthropods() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_DAMAGE_ARTHROPODS, []);
	}

	public static function enchantment_damage_undead() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_DAMAGE_UNDEAD, []);
	}

	public static function enchantment_digging() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_DIGGING, []);
	}

	public static function enchantment_durability() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_DURABILITY, []);
	}

	public static function enchantment_fire() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_FIRE, []);
	}

	public static function enchantment_fishingSpeed() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_FISHINGSPEED, []);
	}

	public static function enchantment_frostwalker() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_FROSTWALKER, []);
	}

	public static function enchantment_knockback() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_KNOCKBACK, []);
	}

	public static function enchantment_lootBonus() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_LOOTBONUS, []);
	}

	public static function enchantment_lootBonusDigger() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_LOOTBONUSDIGGER, []);
	}

	public static function enchantment_lootBonusFishing() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_LOOTBONUSFISHING, []);
	}

	public static function enchantment_mending() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_MENDING, []);
	}

	public static function enchantment_oxygen() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_OXYGEN, []);
	}

	public static function enchantment_protect_all() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_PROTECT_ALL, []);
	}

	public static function enchantment_protect_explosion() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_PROTECT_EXPLOSION, []);
	}

	public static function enchantment_protect_fall() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_PROTECT_FALL, []);
	}

	public static function enchantment_protect_fire() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_PROTECT_FIRE, []);
	}

	public static function enchantment_protect_projectile() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_PROTECT_PROJECTILE, []);
	}

	public static function enchantment_soul_speed() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_SOUL_SPEED, []);
	}

	public static function enchantment_swift_sneak() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_SWIFT_SNEAK, []);
	}

	public static function enchantment_thorns() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_THORNS, []);
	}

	public static function enchantment_tridentChanneling() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_TRIDENTCHANNELING, []);
	}

	public static function enchantment_tridentImpaling() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_TRIDENTIMPALING, []);
	}

	public static function enchantment_tridentLoyalty() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_TRIDENTLOYALTY, []);
	}

	public static function enchantment_tridentRiptide() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_TRIDENTRIPTIDE, []);
	}

	public static function enchantment_untouching() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_UNTOUCHING, []);
	}

	public static function enchantment_waterWalker() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_WATERWALKER, []);
	}

	public static function enchantment_waterWorker() : Translatable{
		return new Translatable(KnownTranslationKeys::ENCHANTMENT_WATERWORKER, []);
	}

	public static function gameMode_adventure() : Translatable{
		return new Translatable(KnownTranslationKeys::GAMEMODE_ADVENTURE, []);
	}

	public static function gameMode_changed(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::GAMEMODE_CHANGED, [
			0 => $param0,
		]);
	}

	public static function gameMode_creative() : Translatable{
		return new Translatable(KnownTranslationKeys::GAMEMODE_CREATIVE, []);
	}

	public static function gameMode_spectator() : Translatable{
		return new Translatable(KnownTranslationKeys::GAMEMODE_SPECTATOR, []);
	}

	public static function gameMode_survival() : Translatable{
		return new Translatable(KnownTranslationKeys::GAMEMODE_SURVIVAL, []);
	}

	public static function gamemode_info() : Translatable{
		return new Translatable(KnownTranslationKeys::GAMEMODE_INFO, []);
	}

	public static function gamemode_options(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::GAMEMODE_OPTIONS, [
			0 => $param0,
		]);
	}

	public static function invalid_port() : Translatable{
		return new Translatable(KnownTranslationKeys::INVALID_PORT, []);
	}

	public static function ip_confirm() : Translatable{
		return new Translatable(KnownTranslationKeys::IP_CONFIRM, []);
	}

	public static function ip_get() : Translatable{
		return new Translatable(KnownTranslationKeys::IP_GET, []);
	}

	public static function ip_warning(Translatable|string $EXTERNAL_IP, Translatable|string $INTERNAL_IP) : Translatable{
		return new Translatable(KnownTranslationKeys::IP_WARNING, [
			"EXTERNAL_IP" => $EXTERNAL_IP,
			"INTERNAL_IP" => $INTERNAL_IP,
		]);
	}

	public static function item_record_11_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_11_DESC, []);
	}

	public static function item_record_13_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_13_DESC, []);
	}

	public static function item_record_5_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_5_DESC, []);
	}

	public static function item_record_blocks_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_BLOCKS_DESC, []);
	}

	public static function item_record_cat_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_CAT_DESC, []);
	}

	public static function item_record_chirp_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_CHIRP_DESC, []);
	}

	public static function item_record_far_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_FAR_DESC, []);
	}

	public static function item_record_mall_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_MALL_DESC, []);
	}

	public static function item_record_mellohi_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_MELLOHI_DESC, []);
	}

	public static function item_record_otherside_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_OTHERSIDE_DESC, []);
	}

	public static function item_record_pigstep_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_PIGSTEP_DESC, []);
	}

	public static function item_record_stal_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_STAL_DESC, []);
	}

	public static function item_record_strad_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_STRAD_DESC, []);
	}

	public static function item_record_wait_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_WAIT_DESC, []);
	}

	public static function item_record_ward_desc() : Translatable{
		return new Translatable(KnownTranslationKeys::ITEM_RECORD_WARD_DESC, []);
	}

	public static function kick_admin() : Translatable{
		return new Translatable(KnownTranslationKeys::KICK_ADMIN, []);
	}

	public static function kick_admin_reason(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::KICK_ADMIN_REASON, [
			0 => $param0,
		]);
	}

	public static function kick_reason_cheat(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::KICK_REASON_CHEAT, [
			0 => $param0,
		]);
	}

	public static function language_name() : Translatable{
		return new Translatable(KnownTranslationKeys::LANGUAGE_NAME, []);
	}

	public static function language_selected(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::LANGUAGE_SELECTED, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function language_has_been_selected() : Translatable{
		return new Translatable(KnownTranslationKeys::LANGUAGE_HAS_BEEN_SELECTED, []);
	}

	public static function max_players() : Translatable{
		return new Translatable(KnownTranslationKeys::MAX_PLAYERS, []);
	}

	public static function multiplayer_player_joined(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::MULTIPLAYER_PLAYER_JOINED, [
			0 => $param0,
		]);
	}

	public static function multiplayer_player_left(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::MULTIPLAYER_PLAYER_LEFT, [
			0 => $param0,
		]);
	}

	public static function name_your_server() : Translatable{
		return new Translatable(KnownTranslationKeys::NAME_YOUR_SERVER, []);
	}

	public static function op_info() : Translatable{
		return new Translatable(KnownTranslationKeys::OP_INFO, []);
	}

	public static function op_warning() : Translatable{
		return new Translatable(KnownTranslationKeys::OP_WARNING, []);
	}

	public static function op_who() : Translatable{
		return new Translatable(KnownTranslationKeys::OP_WHO, []);
	}

	public static function pocketmine_command_alias_illegal(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_ALIAS_ILLEGAL, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_alias_notFound(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_ALIAS_NOTFOUND, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_command_alias_recursive(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_ALIAS_RECURSIVE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_command_ban_ip_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_BAN_IP_DESCRIPTION, []);
	}

	public static function pocketmine_command_ban_player_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_BAN_PLAYER_DESCRIPTION, []);
	}

	public static function pocketmine_command_banlist_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_BANLIST_DESCRIPTION, []);
	}

	public static function pocketmine_command_clear_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_CLEAR_DESCRIPTION, []);
	}

	public static function pocketmine_command_clear_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_CLEAR_USAGE, []);
	}

	public static function pocketmine_command_defaultgamemode_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_DEFAULTGAMEMODE_DESCRIPTION, []);
	}

	public static function pocketmine_command_deop_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_DEOP_DESCRIPTION, []);
	}

	public static function pocketmine_command_difficulty_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_DIFFICULTY_DESCRIPTION, []);
	}

	public static function pocketmine_command_effect_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_EFFECT_DESCRIPTION, []);
	}

	public static function pocketmine_command_enchant_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_ENCHANT_DESCRIPTION, []);
	}

	public static function pocketmine_command_error_permission(Translatable|string $commandName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_ERROR_PERMISSION, [
			"commandName" => $commandName,
		]);
	}

	public static function pocketmine_command_error_playerNotFound(Translatable|string $playerName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_ERROR_PLAYERNOTFOUND, [
			"playerName" => $playerName,
		]);
	}

	public static function pocketmine_command_exception(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_EXCEPTION, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function pocketmine_command_gamemode_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GAMEMODE_DESCRIPTION, []);
	}

	public static function pocketmine_command_gamemode_failure(Translatable|string $playerName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GAMEMODE_FAILURE, [
			"playerName" => $playerName,
		]);
	}

	public static function pocketmine_command_gamemode_unknown(Translatable|string $gameModeName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GAMEMODE_UNKNOWN, [
			"gameModeName" => $gameModeName,
		]);
	}

	public static function pocketmine_command_gc_chunks(Translatable|string $chunksCollected) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GC_CHUNKS, [
			"chunksCollected" => $chunksCollected,
		]);
	}

	public static function pocketmine_command_gc_cycles(Translatable|string $cyclesCollected) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GC_CYCLES, [
			"cyclesCollected" => $cyclesCollected,
		]);
	}

	public static function pocketmine_command_gc_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GC_DESCRIPTION, []);
	}

	public static function pocketmine_command_gc_entities(Translatable|string $entitiesCollected) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GC_ENTITIES, [
			"entitiesCollected" => $entitiesCollected,
		]);
	}

	public static function pocketmine_command_gc_header() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GC_HEADER, []);
	}

	public static function pocketmine_command_gc_memoryFreed(Translatable|string $memoryFreed) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GC_MEMORYFREED, [
			"memoryFreed" => $memoryFreed,
		]);
	}

	public static function pocketmine_command_give_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GIVE_DESCRIPTION, []);
	}

	public static function pocketmine_command_give_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_GIVE_USAGE, []);
	}

	public static function pocketmine_command_help_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_HELP_DESCRIPTION, []);
	}

	public static function pocketmine_command_help_specificCommand_aliases(Translatable|string $aliasList) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_HELP_SPECIFICCOMMAND_ALIASES, [
			"aliasList" => $aliasList,
		]);
	}

	public static function pocketmine_command_help_specificCommand_description(Translatable|string $description) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_HELP_SPECIFICCOMMAND_DESCRIPTION, [
			"description" => $description,
		]);
	}

	public static function pocketmine_command_help_specificCommand_header(Translatable|string $commandName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_HELP_SPECIFICCOMMAND_HEADER, [
			"commandName" => $commandName,
		]);
	}

	public static function pocketmine_command_help_specificCommand_usage(Translatable|string $usage) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_HELP_SPECIFICCOMMAND_USAGE, [
			"usage" => $usage,
		]);
	}

	public static function pocketmine_command_kick_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_KICK_DESCRIPTION, []);
	}

	public static function pocketmine_command_kill_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_KILL_DESCRIPTION, []);
	}

	public static function pocketmine_command_kill_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_KILL_USAGE, []);
	}

	public static function pocketmine_command_list_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_LIST_DESCRIPTION, []);
	}

	public static function pocketmine_command_me_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_ME_DESCRIPTION, []);
	}

	public static function pocketmine_command_notFound(Translatable|string $commandName, Translatable|string $helpCommand) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_NOTFOUND, [
			"commandName" => $commandName,
			"helpCommand" => $helpCommand,
		]);
	}

	public static function pocketmine_command_op_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_OP_DESCRIPTION, []);
	}

	public static function pocketmine_command_particle_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_PARTICLE_DESCRIPTION, []);
	}

	public static function pocketmine_command_particle_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_PARTICLE_USAGE, []);
	}

	public static function pocketmine_command_plugins_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_PLUGINS_DESCRIPTION, []);
	}

	public static function pocketmine_command_plugins_success(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_PLUGINS_SUCCESS, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_command_save_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_SAVE_DESCRIPTION, []);
	}

	public static function pocketmine_command_saveoff_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_SAVEOFF_DESCRIPTION, []);
	}

	public static function pocketmine_command_saveon_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_SAVEON_DESCRIPTION, []);
	}

	public static function pocketmine_command_say_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_SAY_DESCRIPTION, []);
	}

	public static function pocketmine_command_seed_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_SEED_DESCRIPTION, []);
	}

	public static function pocketmine_command_setworldspawn_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_SETWORLDSPAWN_DESCRIPTION, []);
	}

	public static function pocketmine_command_spawnpoint_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_SPAWNPOINT_DESCRIPTION, []);
	}

	public static function pocketmine_command_status_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_STATUS_DESCRIPTION, []);
	}

	public static function pocketmine_command_stop_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_STOP_DESCRIPTION, []);
	}

	public static function pocketmine_command_tell_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TELL_DESCRIPTION, []);
	}

	public static function pocketmine_command_time_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIME_DESCRIPTION, []);
	}

	public static function pocketmine_command_time_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIME_USAGE, []);
	}

	public static function pocketmine_command_timings_alreadyEnabled() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_ALREADYENABLED, []);
	}

	public static function pocketmine_command_timings_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_DESCRIPTION, []);
	}

	public static function pocketmine_command_timings_disable() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_DISABLE, []);
	}

	public static function pocketmine_command_timings_enable() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_ENABLE, []);
	}

	public static function pocketmine_command_timings_pasteError() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_PASTEERROR, []);
	}

	public static function pocketmine_command_timings_reset() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_RESET, []);
	}

	public static function pocketmine_command_timings_timingsDisabled() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSDISABLED, []);
	}

	public static function pocketmine_command_timings_timingsRead(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSREAD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_timings_timingsUpload(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSUPLOAD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_timings_timingsWrite(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_TIMINGSWRITE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_command_timings_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TIMINGS_USAGE, []);
	}

	public static function pocketmine_command_title_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TITLE_DESCRIPTION, []);
	}

	public static function pocketmine_command_tp_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TP_DESCRIPTION, []);
	}

	public static function pocketmine_command_transferserver_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TRANSFERSERVER_DESCRIPTION, []);
	}

	public static function pocketmine_command_transferserver_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_TRANSFERSERVER_USAGE, []);
	}

	public static function pocketmine_command_unban_ip_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_UNBAN_IP_DESCRIPTION, []);
	}

	public static function pocketmine_command_unban_player_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_UNBAN_PLAYER_DESCRIPTION, []);
	}

	public static function pocketmine_command_userDefined_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_USERDEFINED_DESCRIPTION, []);
	}

	public static function pocketmine_command_version_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_DESCRIPTION, []);
	}

	public static function pocketmine_command_version_minecraftVersion(Translatable|string $minecraftVersion, Translatable|string $minecraftProtocolVersion) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_MINECRAFTVERSION, [
			"minecraftVersion" => $minecraftVersion,
			"minecraftProtocolVersion" => $minecraftProtocolVersion,
		]);
	}

	public static function pocketmine_command_version_noSuchPlugin() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_NOSUCHPLUGIN, []);
	}

	public static function pocketmine_command_version_operatingSystem(Translatable|string $operatingSystemName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_OPERATINGSYSTEM, [
			"operatingSystemName" => $operatingSystemName,
		]);
	}

	public static function pocketmine_command_version_phpJitDisabled() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITDISABLED, []);
	}

	public static function pocketmine_command_version_phpJitEnabled(Translatable|string $extraJitInfo) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITENABLED, [
			"extraJitInfo" => $extraJitInfo,
		]);
	}

	public static function pocketmine_command_version_phpJitNotSupported() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITNOTSUPPORTED, []);
	}

	public static function pocketmine_command_version_phpJitStatus(Translatable|string $jitStatus) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPJITSTATUS, [
			"jitStatus" => $jitStatus,
		]);
	}

	public static function pocketmine_command_version_phpVersion(Translatable|string $phpVersion) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_PHPVERSION, [
			"phpVersion" => $phpVersion,
		]);
	}

	public static function pocketmine_command_version_serverSoftwareName(Translatable|string $serverSoftwareName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_SERVERSOFTWARENAME, [
			"serverSoftwareName" => $serverSoftwareName,
		]);
	}

	public static function pocketmine_command_version_serverSoftwareVersion(Translatable|string $serverSoftwareVersion, Translatable|string $serverGitHash) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_SERVERSOFTWAREVERSION, [
			"serverSoftwareVersion" => $serverSoftwareVersion,
			"serverGitHash" => $serverGitHash,
		]);
	}

	public static function pocketmine_command_version_usage() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_USAGE, []);
	}

	public static function pocketmine_command_whitelist_description() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_COMMAND_WHITELIST_DESCRIPTION, []);
	}

	public static function pocketmine_crash_archive(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_CRASH_ARCHIVE, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_crash_create() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_CRASH_CREATE, []);
	}

	public static function pocketmine_crash_error(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_CRASH_ERROR, [
			0 => $param0,
		]);
	}

	public static function pocketmine_crash_submit(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_CRASH_SUBMIT, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_playerCorrupted(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DATA_PLAYERCORRUPTED, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_playerNotFound(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DATA_PLAYERNOTFOUND, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_playerOld(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DATA_PLAYEROLD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_data_saveError(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DATA_SAVEERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_debug_enable() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DEBUG_ENABLE, []);
	}

	public static function pocketmine_disconnect_incompatibleProtocol(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DISCONNECT_INCOMPATIBLEPROTOCOL, [
			0 => $param0,
		]);
	}

	public static function pocketmine_disconnect_invalidSession(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION, [
			0 => $param0,
		]);
	}

	public static function pocketmine_disconnect_invalidSession_badSignature() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_BADSIGNATURE, []);
	}

	public static function pocketmine_disconnect_invalidSession_missingKey() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_MISSINGKEY, []);
	}

	public static function pocketmine_disconnect_invalidSession_tooEarly() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_TOOEARLY, []);
	}

	public static function pocketmine_disconnect_invalidSession_tooLate() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_TOOLATE, []);
	}

	public static function pocketmine_level_ambiguousFormat(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_AMBIGUOUSFORMAT, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_backgroundGeneration(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_BACKGROUNDGENERATION, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_badDefaultFormat(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_BADDEFAULTFORMAT, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_conversion_finish(Translatable|string $worldName, Translatable|string $backupPath) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_CONVERSION_FINISH, [
			"worldName" => $worldName,
			"backupPath" => $backupPath,
		]);
	}

	public static function pocketmine_level_conversion_start(Translatable|string $worldName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_CONVERSION_START, [
			"worldName" => $worldName,
		]);
	}

	public static function pocketmine_level_corrupted(Translatable|string $details) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_CORRUPTED, [
			"details" => $details,
		]);
	}

	public static function pocketmine_level_defaultError() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_DEFAULTERROR, []);
	}

	public static function pocketmine_level_generationError(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_GENERATIONERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_level_invalidGeneratorOptions(Translatable|string $preset, Translatable|string $generatorName, Translatable|string $details) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_INVALIDGENERATOROPTIONS, [
			"preset" => $preset,
			"generatorName" => $generatorName,
			"details" => $details,
		]);
	}

	public static function pocketmine_level_loadError(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_LOADERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_level_notFound(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_NOTFOUND, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_preparing(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_PREPARING, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_spawnTerrainGenerationProgress(Translatable|string $done, Translatable|string $total, Translatable|string $percentageDone) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_SPAWNTERRAINGENERATIONPROGRESS, [
			"done" => $done,
			"total" => $total,
			"percentageDone" => $percentageDone,
		]);
	}

	public static function pocketmine_level_unknownFormat() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_UNKNOWNFORMAT, []);
	}

	public static function pocketmine_level_unknownGenerator(Translatable|string $generatorName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_UNKNOWNGENERATOR, [
			"generatorName" => $generatorName,
		]);
	}

	public static function pocketmine_level_unloading(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_UNLOADING, [
			0 => $param0,
		]);
	}

	public static function pocketmine_level_unsupportedFormat(Translatable|string $details) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_LEVEL_UNSUPPORTEDFORMAT, [
			"details" => $details,
		]);
	}

	public static function pocketmine_player_invalidEntity(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLAYER_INVALIDENTITY, [
			0 => $param0,
		]);
	}

	public static function pocketmine_player_invalidMove(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLAYER_INVALIDMOVE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_player_logIn(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2, Translatable|string $param3, Translatable|string $param4, Translatable|string $param5, Translatable|string $param6, Translatable|string $param7) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLAYER_LOGIN, [
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

	public static function pocketmine_player_logOut(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2, Translatable|string $param3) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLAYER_LOGOUT, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function pocketmine_plugin_aliasError(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_ALIASERROR, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function pocketmine_plugin_ambiguousMinAPI(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_AMBIGUOUSMINAPI, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_badDataFolder(Translatable|string $dataFolder) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_BADDATAFOLDER, [
			"dataFolder" => $dataFolder,
		]);
	}

	public static function pocketmine_plugin_circularDependency() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_CIRCULARDEPENDENCY, []);
	}

	public static function pocketmine_plugin_commandError(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_COMMANDERROR, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function pocketmine_plugin_deprecatedEvent(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_DEPRECATEDEVENT, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
		]);
	}

	public static function pocketmine_plugin_disable(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_DISABLE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_disallowedByBlacklist() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_DISALLOWEDBYBLACKLIST, []);
	}

	public static function pocketmine_plugin_disallowedByWhitelist() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_DISALLOWEDBYWHITELIST, []);
	}

	public static function pocketmine_plugin_duplicateError(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_DUPLICATEERROR, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_duplicatePermissionError(Translatable|string $permissionName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_DUPLICATEPERMISSIONERROR, [
			"permissionName" => $permissionName,
		]);
	}

	public static function pocketmine_plugin_emptyExtensionVersionConstraint(Translatable|string $constraintIndex, Translatable|string $extensionName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_EMPTYEXTENSIONVERSIONCONSTRAINT, [
			"constraintIndex" => $constraintIndex,
			"extensionName" => $extensionName,
		]);
	}

	public static function pocketmine_plugin_enable(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_ENABLE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_enableError(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_ENABLEERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_plugin_extensionNotLoaded(Translatable|string $extensionName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_EXTENSIONNOTLOADED, [
			"extensionName" => $extensionName,
		]);
	}

	public static function pocketmine_plugin_genericLoadError(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_GENERICLOADERROR, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatibleAPI(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEAPI, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatibleExtensionVersion(Translatable|string $extensionVersion, Translatable|string $extensionName, Translatable|string $pluginRequirement) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEEXTENSIONVERSION, [
			"extensionVersion" => $extensionVersion,
			"extensionName" => $extensionName,
			"pluginRequirement" => $pluginRequirement,
		]);
	}

	public static function pocketmine_plugin_incompatibleOS(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEOS, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatiblePhpVersion(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEPHPVERSION, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_incompatibleProtocol(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INCOMPATIBLEPROTOCOL, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_invalidAPI(Translatable|string $apiVersion) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INVALIDAPI, [
			"apiVersion" => $apiVersion,
		]);
	}

	public static function pocketmine_plugin_invalidExtensionVersionConstraint(Translatable|string $versionConstraint, Translatable|string $extensionName) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INVALIDEXTENSIONVERSIONCONSTRAINT, [
			"versionConstraint" => $versionConstraint,
			"extensionName" => $extensionName,
		]);
	}

	public static function pocketmine_plugin_invalidManifest(Translatable|string $details) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_INVALIDMANIFEST, [
			"details" => $details,
		]);
	}

	public static function pocketmine_plugin_load(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_LOAD, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_loadError(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_LOADERROR, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_plugin_mainClassAbstract() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_MAINCLASSABSTRACT, []);
	}

	public static function pocketmine_plugin_mainClassNotFound() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_MAINCLASSNOTFOUND, []);
	}

	public static function pocketmine_plugin_mainClassWrongType(Translatable|string $pluginInterface) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_MAINCLASSWRONGTYPE, [
			"pluginInterface" => $pluginInterface,
		]);
	}

	public static function pocketmine_plugin_restrictedName() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_RESTRICTEDNAME, []);
	}

	public static function pocketmine_plugin_someEnableErrors() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_SOMEENABLEERRORS, []);
	}

	public static function pocketmine_plugin_someLoadErrors() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_SOMELOADERRORS, []);
	}

	public static function pocketmine_plugin_spacesDiscouraged(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_SPACESDISCOURAGED, [
			0 => $param0,
		]);
	}

	public static function pocketmine_plugin_suicide() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_SUICIDE, []);
	}

	public static function pocketmine_plugin_unknownDependency(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGIN_UNKNOWNDEPENDENCY, [
			0 => $param0,
		]);
	}

	public static function pocketmine_save_start() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SAVE_START, []);
	}

	public static function pocketmine_save_success(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SAVE_SUCCESS, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_auth_disabled() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_AUTH_DISABLED, []);
	}

	public static function pocketmine_server_auth_enabled() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_AUTH_ENABLED, []);
	}

	public static function pocketmine_server_authProperty_disabled() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_AUTHPROPERTY_DISABLED, []);
	}

	public static function pocketmine_server_authProperty_enabled() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_AUTHPROPERTY_ENABLED, []);
	}

	public static function pocketmine_server_authWarning() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_AUTHWARNING, []);
	}

	public static function pocketmine_server_defaultGameMode(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEFAULTGAMEMODE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_error1(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR1, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_error2() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR2, []);
	}

	public static function pocketmine_server_devBuild_error3() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR3, []);
	}

	public static function pocketmine_server_devBuild_error4(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR4, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_error5(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_ERROR5, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_warning1(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING1, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_devBuild_warning2() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING2, []);
	}

	public static function pocketmine_server_devBuild_warning3() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DEVBUILD_WARNING3, []);
	}

	public static function pocketmine_server_donate(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_DONATE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_forcingShutdown() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_FORCINGSHUTDOWN, []);
	}

	public static function pocketmine_server_info(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_INFO, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_info_extended(Translatable|string $param0, Translatable|string $param1, Translatable|string $param2, Translatable|string $param3) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_INFO_EXTENDED, [
			0 => $param0,
			1 => $param1,
			2 => $param2,
			3 => $param3,
		]);
	}

	public static function pocketmine_server_license(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_LICENSE, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_networkStart(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_NETWORKSTART, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_networkStartFailed(Translatable|string $ipAddress, Translatable|string $port, Translatable|string $errorMessage) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_NETWORKSTARTFAILED, [
			"ipAddress" => $ipAddress,
			"port" => $port,
			"errorMessage" => $errorMessage,
		]);
	}

	public static function pocketmine_server_obsolete_warning1(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_OBSOLETE_WARNING1, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_obsolete_warning2(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_OBSOLETE_WARNING2, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_obsolete_warning3(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_OBSOLETE_WARNING3, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_query_running(Translatable|string $param0, Translatable|string $param1) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_QUERY_RUNNING, [
			0 => $param0,
			1 => $param1,
		]);
	}

	public static function pocketmine_server_start(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_START, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_startFinished(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_STARTFINISHED, [
			0 => $param0,
		]);
	}

	public static function pocketmine_server_tickOverload() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_SERVER_TICKOVERLOAD, []);
	}

	public static function pocketmine_plugins() : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_PLUGINS, []);
	}

	public static function pocketmine_will_start(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::POCKETMINE_WILL_START, [
			0 => $param0,
		]);
	}

	public static function port_warning() : Translatable{
		return new Translatable(KnownTranslationKeys::PORT_WARNING, []);
	}

	public static function potion_absorption() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_ABSORPTION, []);
	}

	public static function potion_blindness() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_BLINDNESS, []);
	}

	public static function potion_conduitPower() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_CONDUITPOWER, []);
	}

	public static function potion_confusion() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_CONFUSION, []);
	}

	public static function potion_damageBoost() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_DAMAGEBOOST, []);
	}

	public static function potion_digSlowDown() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_DIGSLOWDOWN, []);
	}

	public static function potion_digSpeed() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_DIGSPEED, []);
	}

	public static function potion_fireResistance() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_FIRERESISTANCE, []);
	}

	public static function potion_harm() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_HARM, []);
	}

	public static function potion_heal() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_HEAL, []);
	}

	public static function potion_healthBoost() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_HEALTHBOOST, []);
	}

	public static function potion_hunger() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_HUNGER, []);
	}

	public static function potion_invisibility() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_INVISIBILITY, []);
	}

	public static function potion_jump() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_JUMP, []);
	}

	public static function potion_levitation() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_LEVITATION, []);
	}

	public static function potion_moveSlowdown() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_MOVESLOWDOWN, []);
	}

	public static function potion_moveSpeed() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_MOVESPEED, []);
	}

	public static function potion_nightVision() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_NIGHTVISION, []);
	}

	public static function potion_poison() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_POISON, []);
	}

	public static function potion_regeneration() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_REGENERATION, []);
	}

	public static function potion_resistance() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_RESISTANCE, []);
	}

	public static function potion_saturation() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_SATURATION, []);
	}

	public static function potion_slowFalling() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_SLOWFALLING, []);
	}

	public static function potion_waterBreathing() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_WATERBREATHING, []);
	}

	public static function potion_weakness() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_WEAKNESS, []);
	}

	public static function potion_wither() : Translatable{
		return new Translatable(KnownTranslationKeys::POTION_WITHER, []);
	}

	public static function query_disable() : Translatable{
		return new Translatable(KnownTranslationKeys::QUERY_DISABLE, []);
	}

	public static function query_warning1() : Translatable{
		return new Translatable(KnownTranslationKeys::QUERY_WARNING1, []);
	}

	public static function query_warning2() : Translatable{
		return new Translatable(KnownTranslationKeys::QUERY_WARNING2, []);
	}

	public static function server_port() : Translatable{
		return new Translatable(KnownTranslationKeys::SERVER_PORT, []);
	}

	public static function server_port_v4() : Translatable{
		return new Translatable(KnownTranslationKeys::SERVER_PORT_V4, []);
	}

	public static function server_port_v6() : Translatable{
		return new Translatable(KnownTranslationKeys::SERVER_PORT_V6, []);
	}

	public static function server_properties() : Translatable{
		return new Translatable(KnownTranslationKeys::SERVER_PROPERTIES, []);
	}

	public static function setting_up_server_now() : Translatable{
		return new Translatable(KnownTranslationKeys::SETTING_UP_SERVER_NOW, []);
	}

	public static function skip_installer() : Translatable{
		return new Translatable(KnownTranslationKeys::SKIP_INSTALLER, []);
	}

	public static function tile_bed_noSleep() : Translatable{
		return new Translatable(KnownTranslationKeys::TILE_BED_NOSLEEP, []);
	}

	public static function tile_bed_occupied() : Translatable{
		return new Translatable(KnownTranslationKeys::TILE_BED_OCCUPIED, []);
	}

	public static function tile_bed_tooFar() : Translatable{
		return new Translatable(KnownTranslationKeys::TILE_BED_TOOFAR, []);
	}

	public static function view_distance() : Translatable{
		return new Translatable(KnownTranslationKeys::VIEW_DISTANCE, []);
	}

	public static function welcome_to_pocketmine(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::WELCOME_TO_POCKETMINE, [
			0 => $param0,
		]);
	}

	public static function whitelist_enable() : Translatable{
		return new Translatable(KnownTranslationKeys::WHITELIST_ENABLE, []);
	}

	public static function whitelist_info() : Translatable{
		return new Translatable(KnownTranslationKeys::WHITELIST_INFO, []);
	}

	public static function whitelist_warning() : Translatable{
		return new Translatable(KnownTranslationKeys::WHITELIST_WARNING, []);
	}

	public static function you_have_finished() : Translatable{
		return new Translatable(KnownTranslationKeys::YOU_HAVE_FINISHED, []);
	}

	public static function you_have_to_accept_the_license(Translatable|string $param0) : Translatable{
		return new Translatable(KnownTranslationKeys::YOU_HAVE_TO_ACCEPT_THE_LICENSE, [
			0 => $param0,
		]);
	}

}
