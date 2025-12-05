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
		$data = file_get_contents($this->getDataFolder() . "/create-data");
		if($data !== "successful") {
			$this->getLogger()->critical("create-data is not successful");
			exit(1);
		}
		$this->getLogger()->notice("Test passed: create-data is successful");
	}
}
