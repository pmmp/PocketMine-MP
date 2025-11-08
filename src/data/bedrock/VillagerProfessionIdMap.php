<?php


/*
 *    ______           _ _        ___  ____                  ___  _________ 
 *    | ___ \         | | |       |  \/  (_)                 |  \/  || ___ \
 *    | |_/ / ___  ___| | |_ _   _| .  . |_ _ __   ___ ______| .  . || |_/ /
 *    | ___ \/ _ \/ _ \ | __| | | | |\/| | | '_ \ / _ \______| |\/| ||  __/ 
 *    | |_/ /  __/  __/ | |_| |_| | |  | | | | | |  __/      | |  | || |    
 *    \____/ \___|\___|_|\__|\__, \_|  |_/_|_| |_|\___|      \_|  |_/\_|    
 *                            __/ |                                         
 *                           |___/   
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * @author BeeltyMine-MP Team                                        
 * @link https://github.com/BeeltyMine
 * @developer AyrzDev
 * @license https://opensource.org/licenses/LGPL-3.0
 * @note This software is provided "as is" without any warranty.
 * 
 */

declare(strict_types=1);

namespace pocketmine\data\bedrock;

use pocketmine\entity\profession\VanillaVillagerProfessions;
use pocketmine\entity\profession\VillagerProfession;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;

final class VillagerProfessionIdMap
{
    use SingletonTrait;
    /** @use IntSaveIdMapTrait<VillagerProfession> */
    use IntSaveIdMapTrait;

    public function __construct()
    {
        foreach (Utils::stringifyKeys(VanillaVillagerProfessions::getAll()) as $name => $profession) {
            $this->register($profession->getId(), $profession);
        }
    }
}
