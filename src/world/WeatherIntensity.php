<?php

declare(strict_types=1);

namespace pocketmine\world;

final class WeatherIntensity{

// Rain
public const RAIN_LIGHT = 50000;
public const RAIN_NORMAL = 100000;
public const RAIN_HEAVY = 160000;

// Thunder
public const THUNDER_LIGHT = 100000;
public const THUNDER_NORMAL = 160000;
public const THUNDER_HEAVY = 200000;

// No weather
public const NONE = 0;

private function __construct(){
//NOOP
}
}