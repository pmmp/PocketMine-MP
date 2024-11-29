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

namespace pocketmine\lang;

use pocketmine\utils\Utils;

final class Translatable{
	/** @var string[]|Translatable[] $params */
	protected array $params = [];

	/**
	 * @param (float|int|string|Translatable)[] $params
	 */
	public function __construct(
		protected string $text,
		array $params = []
	){
		foreach(Utils::promoteKeys($params) as $k => $param){
			if(!($param instanceof Translatable)){
				$this->params[$k] = (string) $param;
			}else{
				$this->params[$k] = $param;
			}
		}
	}

	public function getText() : string{
		return $this->text;
	}

	/**
	 * @return string[]|Translatable[]
	 */
	public function getParameters() : array{
		return $this->params;
	}

	public function getParameter(int|string $i) : Translatable|string|null{
		return $this->params[$i] ?? null;
	}

	public function format(string $before, string $after) : self{
		return new self("$before%$this->text$after", $this->params);
	}

	public function prefix(string $prefix) : self{
		return new self("$prefix%$this->text", $this->params);
	}

	public function postfix(string $postfix) : self{
		return new self("%$this->text" . $postfix);
	}
}
