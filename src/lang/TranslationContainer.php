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

class TranslationContainer extends TextContainer{

	/** @var string[] $params */
	protected $params = [];

	/**
	 * @param string   $text
	 * @param string[] $params
	 */
	public function __construct(string $text, array $params = []){
		parent::__construct($text);

		$this->setParameters($params);
	}

	/**
	 * @return string[]
	 */
	public function getParameters() : array{
		return $this->params;
	}

	/**
	 * @param int $i
	 *
	 * @return string|null
	 */
	public function getParameter(int $i){
		return $this->params[$i] ?? null;
	}

	/**
	 * @param int    $i
	 * @param string $str
	 */
	public function setParameter(int $i, string $str){
		if($i < 0 or $i > count($this->params)){ //Intended, allow to set the last
			throw new \InvalidArgumentException("Invalid index $i, have " . count($this->params));
		}

		$this->params[$i] = $str;
	}

	/**
	 * @param string[] $params
	 */
	public function setParameters(array $params){
		$i = 0;
		foreach($params as $str){
			$this->params[$i] = (string) $str;

			++$i;
		}
	}
}
