<?php

/**
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

abstract class Entity extends Position{

}

/***REM_START***/
require_once("entity/DamageableEntity.php");
require_once("entity/ProjectileSourceEntity.php");
require_once("entity/RideableEntity.php");
require_once("entity/AttachableEntity.php");
require_once("entity/ExplosiveEntity.php");

require_once("entity/LivingEntity.php");
require_once("entity/CreatureEntity.php");
require_once("entity/MonsterEntity.php");
require_once("entity/AgeableEntity.php");
require_once("entity/AnimalEntity.php");
require_once("entity/HumanEntity.php");
require_once("entity/ProjectileEntity.php");
require_once("entity/VehicleEntity.php");
require_once("entity/HangingEntity.php");
/***REM_END***/