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

namespace PocketMine\Level\Generator;

use PocketMine\Block\Air;
use PocketMine\Block\CoalOre;
use PocketMine\Block\DiamondOre;
use PocketMine\Block\Dirt;
use PocketMine\Block\GoldOre;
use PocketMine\Block\Gravel;
use PocketMine\Block\IronOre;
use PocketMine\Block\LapisOre;
use PocketMine\Block\RedstoneOre;
use PocketMine\Item\Item;
use PocketMine\Level\Generator\Populator\Ore;
use PocketMine\Level\Level;
use PocketMine\Math\Vector3 as Vector3;
use PocketMine\Utils\Random;

class Flat extends Generator{
	private $level, $random, $structure, $chunks, $options, $floorLevel, $preset, $populators = array();

	public function getSettings(){
		return $this->options;
	}

	public function getName(){
		return "flat";
	}

	public function __construct(array $options = array()){
		$this->preset = "2;7,59x1,3x3,2;1;spawn(radius=10 block=89),decoration(treecount=80 grasscount=45)";
		$this->options = $options;
		if(isset($options["preset"])){
			$this->parsePreset($options["preset"]);
		}else{
			$this->parsePreset($this->preset);
		}
		if(isset($this->options["decoration"])){
			$ores = new Ore();
			$ores->setOreTypes(array(
				new Object\OreType(new CoalOre(), 20, 16, 0, 128),
				new Object\OreType(New IronOre(), 20, 8, 0, 64),
				new Object\OreType(new RedstoneOre(), 8, 7, 0, 16),
				new Object\OreType(new LapisOre(), 1, 6, 0, 32),
				new Object\OreType(new GoldOre(), 2, 8, 0, 32),
				new Object\OreType(new DiamondOre(), 1, 7, 0, 16),
				new Object\OreType(new Dirt(), 20, 32, 0, 128),
				new Object\OreType(new Gravel(), 10, 16, 0, 128),
			));
			$this->populators[] = $ores;
		}

		/*if(isset($this->options["mineshaft"])){
			$this->populators[] = new MineshaftPopulator(isset($this->options["mineshaft"]["chance"]) ? floatval($this->options["mineshaft"]["chance"]) : 0.01);
		}*/
	}

	public function parsePreset($preset){
		$this->preset = $preset;
		$preset = explode(";", $preset);
		$version = (int) $preset[0];
		$blocks = @$preset[1];
		$biome = isset($preset[2]) ? $preset[2] : 1;
		$options = isset($preset[3]) ? $preset[3] : "";
		preg_match_all('#(([0-9]{0,})x?([0-9]{1,3}:?[0-9]{0,2})),?#', $blocks, $matches);
		$y = 0;
		$this->structure = array();
		$this->chunks = array();
		foreach($matches[3] as $i => $b){
			$b = Item::fromString($b);
			$cnt = $matches[2][$i] === "" ? 1 : intval($matches[2][$i]);
			for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
				$this->structure[$cY] = $b;
			}
		}

		$this->floorLevel = $y;

		for(; $y < 0xFF; ++$y){
			$this->structure[$y] = new Air();
		}


		for($Y = 0; $Y < 8; ++$Y){
			$this->chunks[$Y] = "";
			$startY = $Y << 4;
			$endY = $startY + 16;
			for($Z = 0; $Z < 16; ++$Z){
				for($X = 0; $X < 16; ++$X){
					$blocks = "";
					$metas = "";
					for($y = $startY; $y < $endY; ++$y){
						$blocks .= chr($this->structure[$y]->getID());
						$metas .= substr(dechex($this->structure[$y]->getMetadata()), -1);
					}
					$this->chunks[$Y] .= $blocks . hex2bin($metas) . "\x00\x00\x00\x00\x00\x00\x00\x00";
				}
			}
		}

		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $options, $matches);
		foreach($matches[2] as $i => $option){
			$params = true;
			if($matches[3][$i] !== ""){
				$params = array();
				$p = explode(" ", $matches[3][$i]);
				foreach($p as $k){
					$k = explode("=", $k);
					if(isset($k[1])){
						$params[$k[0]] = $k[1];
					}
				}
			}
			$this->options[$option] = $params;
		}
	}

	public function init(Level $level, Random $random){
		$this->level = $level;
		$this->random = $random;
	}

	public function generateChunk($chunkX, $chunkZ){
		for($Y = 0; $Y < 8; ++$Y){
			$this->level->setMiniChunk($chunkX, $chunkZ, $Y, $this->chunks[$Y]);
		}
	}

	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function getSpawn(){
		return new Vector3(128, $this->floorLevel, 128);
	}
}

Generator::addGenerator(__NAMESPACE__ . "\\Flat", "flat");
