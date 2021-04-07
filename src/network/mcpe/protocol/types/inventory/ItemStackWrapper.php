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

namespace pocketmine\network\mcpe\protocol\types\inventory;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class ItemStackWrapper{

	/** @var int */
	private $stackId;
	/** @var ItemStack */
	private $itemStack;

	public function __construct(int $stackId, ItemStack $itemStack){
		$this->stackId = $stackId;
		$this->itemStack = $itemStack;
	}

	public static function legacy(ItemStack $itemStack) : self{
		return new self($itemStack->getId() === 0 ? 0 : 1, $itemStack);
	}

	public function getStackId() : int{ return $this->stackId; }

	public function getItemStack() : ItemStack{ return $this->itemStack; }

	public static function read(PacketSerializer $in) : self{
		$stackId = 0;
		$stack = $in->getItemStack(function(PacketSerializer $in) use (&$stackId) : void{
			$hasNetId = $in->getBool();
			if($hasNetId){
				$stackId = $in->readGenericTypeNetworkId();
			}
		});
		return new self($stackId, $stack);
	}

	public function write(PacketSerializer $out) : void{
		$out->putItemStack($this->itemStack, function(PacketSerializer $out) : void{
			$out->putBool($this->stackId !== 0);
			if($this->stackId !== 0){
				$out->writeGenericTypeNetworkId($this->stackId);
			}
		});
	}
}
