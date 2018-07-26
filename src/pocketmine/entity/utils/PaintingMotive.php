<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\utils;

class PaintingMotive{
    /** @var PaintingMotive[] */
    protected static $motives = [];

    public static function init() : void{
        foreach([
                    new PaintingMotive(1, 1, "Alban"),
                    new PaintingMotive(1, 1, "Aztec"),
                    new PaintingMotive(1, 1, "Aztec2"),
                    new PaintingMotive(1, 1, "Bomb"),
                    new PaintingMotive(1, 1, "Kebab"),
                    new PaintingMotive(1, 1, "Plant"),
                    new PaintingMotive(1, 1, "Wasteland"),
                    new PaintingMotive(1, 2, "Graham"),
                    new PaintingMotive(1, 2, "Wanderer"),
                    new PaintingMotive(2, 1, "Courbet"),
                    new PaintingMotive(2, 1, "Creebet"),
                    new PaintingMotive(2, 1, "Pool"),
                    new PaintingMotive(2, 1, "Sea"),
                    new PaintingMotive(2, 1, "Sunset"),
                    new PaintingMotive(2, 2, "Bust"),
                    new PaintingMotive(2, 2, "Earth"),
                    new PaintingMotive(2, 2, "Fire"),
                    new PaintingMotive(2, 2, "Match"),
                    new PaintingMotive(2, 2, "SkullAndRoses"),
                    new PaintingMotive(2, 2, "Stage"),
                    new PaintingMotive(2, 2, "Void"),
                    new PaintingMotive(2, 2, "Water"),
                    new PaintingMotive(2, 2, "Wind"),
                    new PaintingMotive(2, 2, "Wither"),
                    new PaintingMotive(4, 2, "Fighters"),
                    new PaintingMotive(4, 3, "DonkeyKong"),
                    new PaintingMotive(4, 3, "Skeleton"),
                    new PaintingMotive(4, 4, "BurningSkull"),
                    new PaintingMotive(4, 4, "Pigscene"),
                    new PaintingMotive(4, 4, "Pointer")
                ] as $motive){
            self::registerMotive($motive);
        }
    }

    /**
     * @param PaintingMotive $motive
     */
    public static function registerMotive(PaintingMotive $motive) : void{
        self::$motives[$motive->getName()] = $motive;
    }

    /**
     * @param string $name
     * @return PaintingMotive|null
     */
    public static function getMotiveByName(string $name) : ?PaintingMotive{
        return self::$motives[$name] ?? null;
    }

    /**
     * @return PaintingMotive[]
     */
    public static function getAll() : array{
        return self::$motives;
    }

    /** @var string */
    protected $name;
    /** @var int */
    protected $width;
    /** @var int */
    protected $height;


    public function __construct(int $width, int $height, string $name){
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return $this->name;
    }

    /**
     * @return int
     */
    public function getWidth() : int{
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight() : int{
        return $this->height;
    }

    public function __toString() : string{
        return "PaintingMotive(name: " . $this->getName() . ", height: " . $this->getHeight() . ", width: " . $this->getWidth() . ")";
    }
}