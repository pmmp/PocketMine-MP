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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class RedstoneWire extends RedstoneSource{

	const ON = 1;
	const OFF = 2;
	const PLACE = 3;
	const DESTROY = 4;

	protected $id = self::REDSTONE_WIRE;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Redstone Wire";
	}

	public function getStrength(){
		return $this->meta;
	}

	public function isActivated(Block $from = null){
		return ($this->meta > 0);
	}

	public function getHighestStrengthAround(){
		$strength = 0;
		$hasChecked = [
			Vector3::SIDE_WEST => false,
			Vector3::SIDE_EAST => false,
			Vector3::SIDE_NORTH => false,
			Vector3::SIDE_SOUTH => false
		];
		//check blocks around
		foreach($hasChecked as $side => $bool){
			/** @var RedstoneSource $block */
			$block = $this->getSide($side);
			if($block instanceof RedstoneSource){
				if(($block->getStrength() > $strength) and $block->isActivated($this)) $strength = $block->getStrength();
				$hasChecked[$side] = true;
			}
		}

		//check blocks above
		$baseBlock = $this->add(0, 1, 0);
		foreach($hasChecked as $side => $bool){
			if(!$bool){
				$block = $this->getLevel()->getBlock($baseBlock->getSide($side));
				if($block->getId() == $this->id){
					if($block->getStrength() > $strength) $strength = $block->getStrength();
					$hasChecked[$side] = true;
				}
			}
		}

		//check blocks below
		$baseBlock = $this->add(0, -1, 0);
		foreach($hasChecked as $side => $bool){
			if(!$bool){
				$block = $this->getLevel()->getBlock($baseBlock->getSide($side));
				if($block->getId() == $this->id){
					if($block->getStrength() > $strength) $strength = $block->getStrength();
					$hasChecked[$side] = true;
				}
			}
		}

		unset($block);
		unset($hasChecked);

		return $strength;
	}

	public function getConnectedWires(){
		$hasChecked = [
			Vector3::SIDE_WEST => false,
			Vector3::SIDE_EAST => false,
			Vector3::SIDE_NORTH => false,
			Vector3::SIDE_SOUTH => false
		];
		//check blocks around
		foreach($hasChecked as $side => $bool){
			$block = $this->getSide($side);
			if($block instanceof RedstoneSource and !$block instanceof PoweredRepeater){
				$hasChecked[$side] = true;
			}
			if($block instanceof PoweredRepeater){
				if($this->equals($block->getSide($block->getOppositeDirection()))){
					$hasChecked[$side] = true;
				}
			}
		}

		//check blocks above
		$baseBlock = $this->add(0, 1, 0);
		foreach($hasChecked as $side => $bool){
			if(!$bool){
				$block = $this->getLevel()->getBlock($baseBlock->getSide($side));
				if($block->getId() == $this->id){
					$hasChecked[$side] = true;
				}
			}
		}

		//check blocks below
		$baseBlock = $this->add(0, -1, 0);
		foreach($hasChecked as $side => $bool){
			if(!$bool){
				$block = $this->getLevel()->getBlock($baseBlock->getSide($side));
				if($block->getId() == $this->id){
					$hasChecked[$side] = true;
				}
			}
		}

		unset($block);

		return $hasChecked;
	}

	public function getUnconnectedSide(){
		$connected = [];
		$notConnected = [];

		foreach($this->getConnectedWires() as $key => $bool){
			if($bool){
				$connected[] = $key;
			}else $notConnected[] = $key;
		}

		if(count($connected) == 1){
			return [$this->getOppositeSide($connected[0]), $connected];
		}elseif(count($connected) == 3){
			return [$notConnected[0], $connected];
		}else return [false, $connected];
	}

	public function activate(array $ignore = []){
		if($this->canCalc()){
			$block = $this->getSide(Vector3::SIDE_DOWN);
			/** @var ActiveRedstoneLamp $block */
			if($block->getId() == Block::INACTIVE_REDSTONE_LAMP or $block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOn();

			$side = $this->getUnconnectedSide();

			$sides = [Vector3::SIDE_WEST, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];
			foreach($sides as $s){
				if(!in_array($s, $side[1])) {
					$block = $this->getSide(Vector3::SIDE_DOWN)->getSide($s);
					$this->activateBlock($block);
				}
			}

			if($side[0] == false) return;
			$block = $this->getSide($side[0]);
			$this->activateBlock($block);

			if(!$block->isTransparent()){
				$sides = [Vector3::SIDE_WEST, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];
				foreach($sides as $s){
					if($s != $this->getOppositeSide($side[0])){
						$this->activateBlockWithoutWire($block->getSide($s));
					}
				}
			}

			$this->checkTorchOn($block, [$this->getOppositeSide($side)]);

			unset($connected, $notConnected);
		}
	}

	public function deactivate(array $ignore = []){
		if($this->canCalc()){
			$block = $this->getSide(Vector3::SIDE_DOWN);
			if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) {
				/** @var ActiveRedstoneLamp $block */
				if(!$this->checkPower($block, [Vector3::SIDE_UP], true)) $block->turnOff();
			}

			$side = $this->getUnconnectedSide();

			$sides = [Vector3::SIDE_WEST, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];
			foreach($sides as $s){
				if(!in_array($s, $side[1])) {
					$this->deactivateBlock($this->getSide(Vector3::SIDE_DOWN)->getSide($s));
				}
			}

			if($side[0] == false) return;
			$block = $this->getSide($side[0]);
			$this->deactivateBlockWithoutWire($block);

			if(!$block->isTransparent()){
				$sides = [Vector3::SIDE_WEST, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];
				foreach($sides as $s){
					if($s != $this->getOppositeSide($side[0])){
						$this->deactivateBlockWithoutWire($block->getSide($s));
					}
				}
			}

			$this->checkTorchOff($block, [$this->getOppositeSide($side)]);

			unset($connected, $notConnected);
		}
	}

	public function getPowerSources(RedstoneWire $wire, array $powers = [], array $hasUpdated = [], $isStart = false){
		if(!$isStart){
			$wire->meta = 0;
			$wire->getLevel()->setBlock($wire, $wire, true, false);
			$wire->deactivate();
		}
		$hasChecked = [
			Vector3::SIDE_WEST => false,
			Vector3::SIDE_EAST => false,
			Vector3::SIDE_NORTH => false,
			Vector3::SIDE_SOUTH => false
		];
		$hash = Level::blockHash($wire->x, $wire->y, $wire->z);
		if(!isset($hasUpdated[$hash])) $hasUpdated[$hash] = true;
		else return [$powers, $hasUpdated];

		//check blocks around
		foreach($hasChecked as $side => $bool){
			/** @var RedstoneWire $block */
			$block = $wire->getSide($side);
			if($block instanceof RedstoneSource){
				if($block->isActivated($wire)){
					if($block->getId() != $this->id){
						$powers[] = $block;
					}else{
						$result = $this->getPowerSources($block, $powers, $hasUpdated);
						$powers = $result[0];
						$hasUpdated = $result[1];
					}
					$hasChecked[$side] = true;
				}
			}
		}

		//check blocks above
		$baseBlock = $wire->add(0, 1, 0);
		foreach($hasChecked as $side => $bool){
			if(!$bool){
				$block = $this->getLevel()->getBlock($baseBlock->getSide($side));
				if($block instanceof RedstoneSource){
					if($block->isActivated($wire)){
						if($block->getId() == $this->id){
							$result = $this->getPowerSources($block, $powers, $hasUpdated);
							$powers = $result[0];
							$hasUpdated = $result[1];
							$hasChecked[$side] = true;
						}
					}
				}
			}
		}

		//check blocks below
		$baseBlock = $wire->add(0, -1, 0);
		foreach($hasChecked as $side => $bool){
			if(!$bool){
				$block = $this->getLevel()->getBlock($baseBlock->getSide($side));
				if($block instanceof RedstoneSource){
					if($block->isActivated($wire)){
						if($block->getId() == $this->id){
							$result = $this->getPowerSources($block, $powers, $hasUpdated);
							$powers = $result[0];
							$hasUpdated = $result[1];
							$hasChecked[$side] = true;
						}
					}
				}
			}
		}

		return [$powers, $hasUpdated];
	}

	public function calcSignal($strength = 15, $type = self::ON, array $hasUpdated = []){
		//This algorithm is provided by Stary and written by PeratX
		$hash = Level::blockHash($this->x, $this->y, $this->z);
		if(!in_array($hash, $hasUpdated)){
			$hasUpdated[] = $hash;
			if($type == self::DESTROY or $type == self::OFF){
				$this->meta = $strength;
				$this->getLevel()->setBlock($this, $this, true, false);
				if($type == self::DESTROY) $this->getLevel()->setBlock($this, new Air(), true, false);
				if($strength <= 0) $this->deactivate();
				$powers = $this->getPowerSources($this, [], [], true);
				/** @var RedstoneSource $power */
				foreach($powers[0] as $power){
					$power->activate();
				}
			}else{
				if($strength <= 0) return $hasUpdated;
				if($type == self::PLACE) $strength = $this->getHighestStrengthAround() - 1;
				if($type == self::ON) $type = self::PLACE;
				if($this->getStrength() < $strength){
					$this->meta = $strength;
					$this->getLevel()->setBlock($this, $this, true, false);
					$this->activate();

					$hasChecked = [
						Vector3::SIDE_WEST => false,
						Vector3::SIDE_EAST => false,
						Vector3::SIDE_NORTH => false,
						Vector3::SIDE_SOUTH => false
					];

					foreach($hasChecked as $side => $bool){
						$needUpdate = $this->getSide($side);
						if(!in_array(Level::blockHash($needUpdate->x, $needUpdate->y, $needUpdate->z), $hasUpdated)){
							$result = $this->updateNormalWire($needUpdate, $strength - 1, $type, $hasUpdated);
							if(count($result) != count($hasUpdated)){
								$hasUpdated = $result;
								$hasChecked[$side] = true;
							}
						}
					}

					$baseBlock = $this->add(0, 1, 0);
					foreach($hasChecked as $side => $bool){
						if(!$bool){
							$needUpdate = $this->getLevel()->getBlock($baseBlock->getSide($side));
							if(!in_array(Level::blockHash($needUpdate->x, $needUpdate->y, $needUpdate->z), $hasUpdated)){
								$result = $this->updateNormalWire($needUpdate, $strength - 1, $type, $hasUpdated);
								if(count($result) != count($hasUpdated)){
									$hasUpdated = $result;
									$hasChecked[$side] = true;
								}
							}
						}
					}

					$baseBlock = $this->add(0, -1, 0);
					foreach($hasChecked as $side => $bool){
						if(!$bool){
							$needUpdate = $this->getLevel()->getBlock($baseBlock->getSide($side));
							if(!in_array(Level::blockHash($needUpdate->x, $needUpdate->y, $needUpdate->z), $hasUpdated)){
								$result = $this->updateNormalWire($needUpdate, $strength - 1, $type, $hasUpdated);
								if(count($result) != count($hasUpdated)){
									$hasUpdated = $result;
									$hasChecked[$side] = true;
								}
							}
						}
					}
				}
			}
		}


		return $hasUpdated;
	}

	public function updateNormalWire(Block $block, $strength, $type, array $hasUpdated){
		/** @var RedstoneWire $block */
		if($block->getId() == Block::REDSTONE_WIRE){
			if($block->getStrength() < $strength){
				return $block->calcSignal($strength, $type, $hasUpdated);
			}
		}
		return $hasUpdated;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(Vector3::SIDE_DOWN);
			if($down instanceof Transparent and $down->getId() != Block::INACTIVE_REDSTONE_LAMP and $down->getId() != Block::ACTIVE_REDSTONE_LAMP){
				$this->getLevel()->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return true;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down instanceof Transparent and $down->getId() != Block::INACTIVE_REDSTONE_LAMP and $down->getId() != Block::ACTIVE_REDSTONE_LAMP) return;
		else{
			$this->getLevel()->setBlock($block, $this, true, false);
			$this->calcSignal(15, self::PLACE);
		}
	}

	public function onBreak(Item $item){
		if($this->canCalc()) $this->calcSignal(0, self::DESTROY);
		else $this->getLevel()->setBlock($this, new Air());
	}

	public function getDrops(Item $item) : array {
		return [
			[Item::REDSTONE, 0, 1]
		];
	}
}
