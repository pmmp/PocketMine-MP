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

namespace pocketmine\level\format\io;

use pocketmine\level\format\io\leveldb\LevelDB;
use pocketmine\level\format\io\region\Anvil;
use pocketmine\level\format\io\region\McRegion;
use pocketmine\level\format\io\region\PMAnvil;

abstract class LevelProviderManager{
    protected static $providers = [];

    public static function init() : void{
        self::addProvider(Anvil::class);
        self::addProvider(McRegion::class);
        self::addProvider(PMAnvil::class);
        self::addProvider(LevelDB::class);
    }

    /**
     * @param string $class
     *
     * @throws \InvalidArgumentException
     */
    public static function addProvider(string $class){
        try{
            $reflection = new \ReflectionClass($class);
        }catch(\ReflectionException $e){
            throw new \InvalidArgumentException("Class $class does not exist");
        }
        if(!$reflection->implementsInterface(LevelProvider::class)){
            throw new \InvalidArgumentException("Class $class does not implement " . LevelProvider::class);
        }
        if(!$reflection->isInstantiable()){
            throw new \InvalidArgumentException("Class $class cannot be constructed");
        }

        /** @var LevelProvider $class */
        self::$providers[strtolower($class::getProviderName())] = $class;
    }

    /**
     * Returns a LevelProvider class for this path, or null
     *
     * @param string $path
     *
     * @return string|null
     */
    public static function getProvider(string $path){
        foreach(self::$providers as $provider){
            /** @var $provider LevelProvider */
            if($provider::isValid($path)){
                return $provider;
            }
        }

        return null;
    }

    /**
     * Returns a LevelProvider by name, or null if not found
     *
     * @param string $name
     *
     * @return string|null
     */
    public static function getProviderByName(string $name){
        return self::$providers[trim(strtolower($name))] ?? null;
    }
}