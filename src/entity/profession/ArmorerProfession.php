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

namespace pocketmine\entity\profession;

use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\VillagerProfessionTypeIds;
use pocketmine\entity\trade\TradeRecipe;

final class ArmorerProfession extends VillagerProfession{

	public function __construct(){
		parent::__construct(VillagerProfessionTypeIds::ARMORER, "entity.villager.armor", VanillaBlocks::BLAST_FURNACE());
	}

	/** @phpstan-return list<TradeRecipe> */
	public function getRecipes(int $biomeId) : array{
		return [];
	}
}