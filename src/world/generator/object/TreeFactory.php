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

namespace pocketmine\world\generator\object;

use pocketmine\block\utils\TreeType;
use pocketmine\utils\Random;

final class TreeFactory{

	/**
	 * @param TreeType|null $type default oak
	 */
	public static function get(Random $random, ?TreeType $type = null) : ?Tree{
		$type = $type ?? TreeType::OAK();
		if($type->equals(TreeType::SPRUCE())){
			return new SpruceTree();
		}elseif($type->equals(TreeType::BIRCH())){
			if($random->nextBoundedInt(39) === 0){
				return new BirchTree(true);
			}else{
				return new BirchTree();
			}
		}elseif($type->equals(TreeType::JUNGLE())){
			return new JungleTree();
		}elseif($type->equals(TreeType::OAK())){ //default
			return new OakTree();
			/*if($random->nextRange(0, 9) === 0){
				$tree = new BigTree();
			}else{*/

			//}
		}
		return null;
	}
}
