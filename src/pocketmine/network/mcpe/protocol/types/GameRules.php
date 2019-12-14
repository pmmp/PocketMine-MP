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

namespace pocketmine\network\mcpe\protocol\types;

final class GameRules
{
    private function __construct(){
        //NOOP
    }

    public const COMMAND_BLOCK_OUTPUT = "commandBlockOutput";
    public const DO_DAY_LIGHT_CYCLE = "doDayLightCycle";
    public const DO_ENTITY_DROPS = "doEntityDrops";
    public const DO_FIRE_TICK = "doFireTick";
    public const DO_MOB_LOOT = "doMobLoot";
    public const DO_MOB_SPAWNING = "doMobSpawning";
    public const DO_TILE_DROPS = "doTileDrops";
    public const DO_WEATHER_CYCLE = "doWeatherCycle";
    public const DROWNING_DAMAGE = "drowningDamage";
    public const FALL_DAMAGE = "fallDamage";
    public const FIRE_DAMAGE = "fireDamage";
    public const KEEP_INVENTORY = "keepInventory";
    public const MOB_GRIEFING = "mobGriefing";
    public const PVP = "pvp";
    public const SHOW_COORDINATES = "showCoordinates";
    public const NATURAL_REGENERATION = "naturalRegeneration";
    public const TNT_EXPLODES = "TntExplodes";
    public const SEND_COMMAND_FEEDBACK = "sendCommandFeedBack";
    public const EXPERIMENTAL_GAMEPLAY = "experimentalGamePlay";
    public const MAX_COMMAND_CHAIN_LENGTH = "maxCommandChainLength";
    public const DO_INSOMNIA = "doInsomnia";
    public const COMMAND_BLOCKS_ENABLED = "commandBlocksEnabled";
    public const RANDOM_TICK_SPEED = "randomTickSpeed";
    public const DO_IMMEDIATE_RESPAWN = "doImmediateRespawn";
    public const SHOW_DEATH_MESSAGES = "showDeathMessages";
    public const FUNCTION_COMMAND_LIMIT = "functionCommandLimit";
    public const SPAWN_RADIUS = "spawnRadius";
    public const SHOW_TAGS = "showTags";
}