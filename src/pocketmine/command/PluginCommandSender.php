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

namespace pocketmine\command;

use pocketmine\lang\TextContainer;
use pocketmine\plugin\Plugin;

class PluginCommandSender extends SuperCommandSender{
	/** @var Plugin */
	private $plugin;
	/** @var int */
	private $lineHeight = PHP_INT_MAX;

	public function __construct(Plugin $plugin){
		parent::__construct();
		$this->plugin = $plugin;
	}

	/**
	 * @param TextContainer|string $message
	 */
	public function sendMessage($message){
		if($message instanceof TextContainer){
			$message = $this->getServer()->getLanguage()->translate($message);
		}else{
			$message = $this->getServer()->getLanguage()->translateString($message);
		}

		foreach(explode("\n", trim($message)) as $line){
			$this->plugin->getLogger()->info($line);
		}
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->plugin->getName();
	}

	public function getScreenLineHeight() : int{
		return $this->lineHeight;
	}

	public function setScreenLineHeight(int $height = null) : void{
		$this->lineHeight = $height ?? PHP_INT_MAX;
	}
}
