<?php

/**
 * @name data-test
 * @version 1.0.0
 * @api 5.0.0
 * @main DataTest
 * @author PMMP Team
 */

class DataTest extends \pocketmine\plugin\PluginBase {
	public function onEnable() : void {
		file_put_contents($this->getDataFolder() . "/create-data", "successful");
	}
}
