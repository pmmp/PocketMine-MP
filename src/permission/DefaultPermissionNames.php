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

namespace pocketmine\permission;

final class DefaultPermissionNames{
	public const BROADCAST_ADMIN = "pocketmine.broadcast.admin";
	public const BROADCAST_USER = "pocketmine.broadcast.user";
	public const COMMAND_BAN_IP = "pocketmine.command.ban.ip";
	public const COMMAND_BAN_LIST = "pocketmine.command.ban.list";
	public const COMMAND_BAN_PLAYER = "pocketmine.command.ban.player";
	public const COMMAND_CLEAR_OTHER = "pocketmine.command.clear.other";
	public const COMMAND_CLEAR_SELF = "pocketmine.command.clear.self";
	public const COMMAND_DEFAULTGAMEMODE = "pocketmine.command.defaultgamemode";
	public const COMMAND_DIFFICULTY = "pocketmine.command.difficulty";
	public const COMMAND_DUMPMEMORY = "pocketmine.command.dumpmemory";
	public const COMMAND_EFFECT_OTHER = "pocketmine.command.effect.other";
	public const COMMAND_EFFECT_SELF = "pocketmine.command.effect.self";
	public const COMMAND_ENCHANT_OTHER = "pocketmine.command.enchant.other";
	public const COMMAND_ENCHANT_SELF = "pocketmine.command.enchant.self";
	public const COMMAND_GAMEMODE_OTHER = "pocketmine.command.gamemode.other";
	public const COMMAND_GAMEMODE_SELF = "pocketmine.command.gamemode.self";
	public const COMMAND_GC = "pocketmine.command.gc";
	public const COMMAND_GIVE_OTHER = "pocketmine.command.give.other";
	public const COMMAND_GIVE_SELF = "pocketmine.command.give.self";
	public const COMMAND_HELP = "pocketmine.command.help";
	public const COMMAND_KICK = "pocketmine.command.kick";
	public const COMMAND_KILL_OTHER = "pocketmine.command.kill.other";
	public const COMMAND_KILL_SELF = "pocketmine.command.kill.self";
	public const COMMAND_LIST = "pocketmine.command.list";
	public const COMMAND_ME = "pocketmine.command.me";
	public const COMMAND_OP_GIVE = "pocketmine.command.op.give";
	public const COMMAND_OP_TAKE = "pocketmine.command.op.take";
	public const COMMAND_PARTICLE = "pocketmine.command.particle";
	public const COMMAND_PLUGINS = "pocketmine.command.plugins";
	public const COMMAND_SAVE_DISABLE = "pocketmine.command.save.disable";
	public const COMMAND_SAVE_ENABLE = "pocketmine.command.save.enable";
	public const COMMAND_SAVE_PERFORM = "pocketmine.command.save.perform";
	public const COMMAND_SAY = "pocketmine.command.say";
	public const COMMAND_SEED = "pocketmine.command.seed";
	public const COMMAND_SETWORLDSPAWN = "pocketmine.command.setworldspawn";
	public const COMMAND_SPAWNPOINT_OTHER = "pocketmine.command.spawnpoint.other";
	public const COMMAND_SPAWNPOINT_SELF = "pocketmine.command.spawnpoint.self";
	public const COMMAND_STATUS = "pocketmine.command.status";
	public const COMMAND_STOP = "pocketmine.command.stop";
	public const COMMAND_TELEPORT_OTHER = "pocketmine.command.teleport.other";
	public const COMMAND_TELEPORT_SELF = "pocketmine.command.teleport.self";
	public const COMMAND_TELL = "pocketmine.command.tell";
	public const COMMAND_TIME_ADD = "pocketmine.command.time.add";
	public const COMMAND_TIME_QUERY = "pocketmine.command.time.query";
	public const COMMAND_TIME_SET = "pocketmine.command.time.set";
	public const COMMAND_TIME_START = "pocketmine.command.time.start";
	public const COMMAND_TIME_STOP = "pocketmine.command.time.stop";
	public const COMMAND_TIMINGS = "pocketmine.command.timings";
	public const COMMAND_TITLE_OTHER = "pocketmine.command.title.other";
	public const COMMAND_TITLE_SELF = "pocketmine.command.title.self";
	public const COMMAND_TRANSFERSERVER = "pocketmine.command.transferserver";
	public const COMMAND_UNBAN_IP = "pocketmine.command.unban.ip";
	public const COMMAND_UNBAN_PLAYER = "pocketmine.command.unban.player";
	public const COMMAND_VERSION = "pocketmine.command.version";
	public const COMMAND_WHITELIST_ADD = "pocketmine.command.whitelist.add";
	public const COMMAND_WHITELIST_DISABLE = "pocketmine.command.whitelist.disable";
	public const COMMAND_WHITELIST_ENABLE = "pocketmine.command.whitelist.enable";
	public const COMMAND_WHITELIST_LIST = "pocketmine.command.whitelist.list";
	public const COMMAND_WHITELIST_RELOAD = "pocketmine.command.whitelist.reload";
	public const COMMAND_WHITELIST_REMOVE = "pocketmine.command.whitelist.remove";
	public const COMMAND_XP_OTHER = "pocketmine.command.xp.other";
	public const COMMAND_XP_SELF = "pocketmine.command.xp.self";
	public const GROUP_CONSOLE = "pocketmine.group.console";
	public const GROUP_OPERATOR = "pocketmine.group.operator";
	public const GROUP_USER = "pocketmine.group.user";
}
