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


namespace pocketmine\resourcepacks;


class ZippedBehaviorPack extends ZippedResourcePack implements BehaviorPack {

	/** @var bool */
	private $hasClientScripts = false;

	/**
	 * @param string $zipPath Path to the behavior pack zip
	 */
	public function __construct(string $zipPath){
		parent::__construct($zipPath);

		if(is_array($this->manifest->modules)){
			foreach($this->manifest->modules as $module){
				if(isset($module->type) && $module->type === "client_data"){
					$this->hasClientScripts = true;
				}
			}
		}
	}


	/**
	 * Returns whether the behavior pack contains a client-side script.
	 * @return bool
	 */
	public function hasClientScripts() : bool{
		return $this->hasClientScripts;
	}
}
