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

namespace pocketmine\plugin;

use Particle\Validator\Validator;
use function array_filter;
use function array_flip;
use function count;
use function implode;

class PluginGraylist{

	/** @var string[] */
	private $plugins;
	/** @var bool */
	private $isWhitelist = false;

	public function __construct(array $plugins = [], bool $whitelist = false){
		$this->plugins = array_flip($plugins);
		$this->isWhitelist = $whitelist;
	}

	/**
	 * @return string[]
	 */
	public function getPlugins() : array{
		return array_flip($this->plugins);
	}

	/**
	 * @return bool
	 */
	public function isWhitelist() : bool{
		return $this->isWhitelist;
	}

	/**
	 * Returns whether the given name is permitted by this graylist.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isAllowed(string $name) : bool{
		return $this->isWhitelist() === isset($this->plugins[$name]);
	}

	public static function fromArray(array $array) : PluginGraylist{
		$v = new Validator();
		$v->required("mode")->inArray(['whitelist', 'blacklist'], true);
		$v->required("plugins")->isArray()->allowEmpty(true)->callback(function(array $elements) : bool{ return count(array_filter($elements, '\is_string')) === count($elements); });

		$result = $v->validate($array);
		if($result->isNotValid()){
			$messages = [];
			foreach($result->getFailures() as $f){
				$messages[] = $f->format();
			}
			throw new \InvalidArgumentException("Invalid data: " . implode(", ", $messages));
		}
		return new PluginGraylist($array["plugins"], $array["mode"] === 'whitelist');
	}

	public function toArray() : array{
		return [
			"mode" => $this->isWhitelist ? 'whitelist' : 'blacklist',
			"plugins" => $this->plugins
		];
	}
}
