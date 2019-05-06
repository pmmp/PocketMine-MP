<?php

/*
    _____ _                 _        __  __ _____
  / ____| |               | |      |  \/  |  __ \
 | |    | | ___  _   _  __| |______| \  / | |__) |
 | |    | |/ _ \| | | |/ _` |______| |\/| |  ___/
 | |____| | (_) | |_| | (_| |      | |  | | |
  \_____|_|\___/ \__,_|\__,_|      |_|  |_|_|

     Make of Things.
 */

declare(strict_types=1);

namespace pocketmine\item;

class Seagrass extends Item{
	public function __construct(int $meta = 0){
		parent::__construct(self::SEAGRASS, $meta, "Seagrass");
	}

	public function getMaxStackSize() : int{
		return 1;
	}
}
