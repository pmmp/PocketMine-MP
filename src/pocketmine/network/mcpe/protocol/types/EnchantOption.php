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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;

final class EnchantOption{
	/** @var int */
	private $cost;

	/** @var int */
	private $slotFlags;
	/** @var Enchant[] */
	private $equipActivatedEnchantments;
	/** @var Enchant[] */
	private $heldActivatedEnchantments;
	/** @var Enchant[] */
	private $selfActivatedEnchantments;

	/** @var string */
	private $name;

	/** @var int */
	private $optionId;

	/**
	 * @param Enchant[] $equipActivatedEnchantments
	 * @param Enchant[] $heldActivatedEnchantments
	 * @param Enchant[] $selfActivatedEnchantments
	 */
	public function __construct(int $cost, int $slotFlags, array $equipActivatedEnchantments, array $heldActivatedEnchantments, array $selfActivatedEnchantments, string $name, int $optionId){
		$this->cost = $cost;
		$this->slotFlags = $slotFlags;
		$this->equipActivatedEnchantments = $equipActivatedEnchantments;
		$this->heldActivatedEnchantments = $heldActivatedEnchantments;
		$this->selfActivatedEnchantments = $selfActivatedEnchantments;
		$this->name = $name;
		$this->optionId = $optionId;
	}

	public function getCost() : int{ return $this->cost; }

	public function getSlotFlags() : int{ return $this->slotFlags; }

	/** @return Enchant[] */
	public function getEquipActivatedEnchantments() : array{ return $this->equipActivatedEnchantments; }

	/** @return Enchant[] */
	public function getHeldActivatedEnchantments() : array{ return $this->heldActivatedEnchantments; }

	/** @return Enchant[] */
	public function getSelfActivatedEnchantments() : array{ return $this->selfActivatedEnchantments; }

	public function getName() : string{ return $this->name; }

	public function getOptionId() : int{ return $this->optionId; }

	/**
	 * @return Enchant[]
	 */
	private static function readEnchantList(NetworkBinaryStream $in) : array{
		$result = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$result[] = Enchant::read($in);
		}
		return $result;
	}

	/**
	 * @param Enchant[] $list
	 */
	private static function writeEnchantList(NetworkBinaryStream $out, array $list) : void{
		$out->putUnsignedVarInt(count($list));
		foreach($list as $item){
			$item->write($out);
		}
	}

	public static function read(NetworkBinaryStream $in) : self{
		$cost = $in->getUnsignedVarInt();

		$slotFlags = $in->getLInt();
		$equipActivatedEnchants = self::readEnchantList($in);
		$heldActivatedEnchants = self::readEnchantList($in);
		$selfActivatedEnchants = self::readEnchantList($in);

		$name = $in->getString();
		$optionId = $in->readGenericTypeNetworkId();

		return new self($cost, $slotFlags, $equipActivatedEnchants, $heldActivatedEnchants, $selfActivatedEnchants, $name, $optionId);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt($this->cost);

		$out->putLInt($this->slotFlags);
		self::writeEnchantList($out, $this->equipActivatedEnchantments);
		self::writeEnchantList($out, $this->heldActivatedEnchantments);
		self::writeEnchantList($out, $this->selfActivatedEnchantments);

		$out->putString($this->name);
		$out->writeGenericTypeNetworkId($this->optionId);
	}
}
