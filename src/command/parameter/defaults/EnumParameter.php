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

namespace pocketmine\command\parameter\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\parameter\Parameter;
use function mb_strpos;
use function mb_strtolower;

abstract class EnumParameter extends Parameter{
	/** @var bool */
	protected $exact = false;

	/** @var bool */
	protected $caseSensitive = false;

	public function __construct(string $name, bool $optional = false, bool $exact = false, bool $caseSensitive = false){
		parent::__construct($name, $optional);
		$this->exact = $exact;
		$this->caseSensitive = $caseSensitive;
	}

	public function setExact(bool $exact) : self{
		$this->exact = $exact;
		return $this;
	}

	public function isExact() : bool{
		return $this->exact;
	}

	public function setCaseSensitive(bool $caseSensitive) : self{
		$this->caseSensitive = $caseSensitive;
		return $this;
	}

	public function isCaseSensitive() : bool{
		return $this->caseSensitive;
	}

	public function parseSilent(CommandSender $sender, string $argument){
		if($this->isExact()){
			foreach($this->enum->getValues() as $name => $value){
				if(($this->isCaseSensitive() ? $argument : mb_strtolower($argument)) === $value){
					return $value;
				}
			}
			return null;
		}
		foreach($this->enum->getValues() as $name => $value){
			if(mb_strpos($value, $this->isCaseSensitive() ? $argument : mb_strtolower($argument)) !== false){
				return $value;
			}
		}
		return null;
	}
}