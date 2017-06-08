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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;


abstract class DataPacket extends BinaryStream{

	const NETWORK_ID = 0;

	public $isEncoded = false;

	public function pid(){
		return $this::NETWORK_ID;
	}

	public function getName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}

	public function canBeBatched() : bool{
		return true;
	}

	public function canBeSentBeforeLogin() : bool{
		return false;
	}

	abstract public function encode();

	abstract public function decode();

	/**
	 * Performs handling for this packet. Usually you'll want an appropriately named method in the NetworkSession for this.
	 *
	 * This method returns a bool to indicate whether the packet was handled or not. If the packet was unhandled, a debug message will be logged with a hexdump of the packet.
	 * Typically this method returns the return value of the handler in the supplied NetworkSession. See other packets for examples how to implement this.
	 *
	 * @param NetworkSession $session
	 *
	 * @return bool true if the packet was handled successfully, false if not.
	 */
	abstract public function handle(NetworkSession $session) : bool;

	public function reset(){
		$this->buffer = chr($this::NETWORK_ID);
		$this->offset = 0;
	}

	public function clean(){
		$this->buffer = null;
		$this->isEncoded = false;
		$this->offset = 0;
		return $this;
	}

	public function __debugInfo(){
		$data = [];
		foreach($this as $k => $v){
			if($k === "buffer"){
				$data[$k] = bin2hex($v);
			}elseif(is_string($v) or (is_object($v) and method_exists($v, "__toString"))){
				$data[$k] = Utils::printable((string) $v);
			}else{
				$data[$k] = $v;
			}
		}

		return $data;
	}

	/**
	 * Decodes entity metadata from the stream.
	 *
	 * @param bool $types Whether to include metadata types along with values in the returned array
	 *
	 * @return array
	 */
	public function getEntityMetadata(bool $types = true) : array{
		$count = $this->getUnsignedVarInt();
		$data = [];
		for($i = 0; $i < $count; ++$i){
			$key = $this->getUnsignedVarInt();
			$type = $this->getUnsignedVarInt();
			$value = null;
			switch($type){
				case Entity::DATA_TYPE_BYTE:
					$value = $this->getByte();
					break;
				case Entity::DATA_TYPE_SHORT:
					$value = $this->getSignedLShort();
					break;
				case Entity::DATA_TYPE_INT:
					$value = $this->getVarInt();
					break;
				case Entity::DATA_TYPE_FLOAT:
					$value = $this->getLFloat();
					break;
				case Entity::DATA_TYPE_STRING:
					$value = $this->getString();
					break;
				case Entity::DATA_TYPE_SLOT:
					//TODO: use objects directly
					$value = [];
					$item = $this->getSlot();
					$value[0] = $item->getId();
					$value[1] = $item->getCount();
					$value[2] = $item->getDamage();
					break;
				case Entity::DATA_TYPE_POS:
					$value = [0, 0, 0];
					$this->getSignedBlockPosition(...$value);
					break;
				case Entity::DATA_TYPE_LONG:
					$value = $this->getVarLong();
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					$value = [0.0, 0.0, 0.0];
					$this->getVector3f(...$value);
					break;
				default:
					$value = [];
			}
			if($types === true){
				$data[$key] = [$type, $value];
			}else{
				$data[$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * Writes entity metadata to the packet buffer.
	 *
	 * @param array $metadata
	 */
	public function putEntityMetadata(array $metadata){
		$this->putUnsignedVarInt(count($metadata));
		foreach($metadata as $key => $d){
			$this->putUnsignedVarInt($key); //data key
			$this->putUnsignedVarInt($d[0]); //data type
			switch($d[0]){
				case Entity::DATA_TYPE_BYTE:
					$this->putByte($d[1]);
					break;
				case Entity::DATA_TYPE_SHORT:
					$this->putLShort($d[1]); //SIGNED short!
					break;
				case Entity::DATA_TYPE_INT:
					$this->putVarInt($d[1]);
					break;
				case Entity::DATA_TYPE_FLOAT:
					$this->putLFloat($d[1]);
					break;
				case Entity::DATA_TYPE_STRING:
					$this->putString($d[1]);
					break;
				case Entity::DATA_TYPE_SLOT:
					//TODO: change this implementation (use objects)
					$this->putSlot(Item::get($d[1][0], $d[1][2], $d[1][1])); //ID, damage, count
					break;
				case Entity::DATA_TYPE_POS:
					//TODO: change this implementation (use objects)
					$this->putSignedBlockPosition(...$d[1]);
					break;
				case Entity::DATA_TYPE_LONG:
					$this->putVarLong($d[1]);
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					//TODO: change this implementation (use objects)
					$this->putVector3f(...$d[1]); //x, y, z
			}
		}
	}

	/**
	 * Reads a list of Attributes from the stream.
	 * @return Attribute[]
	 *
	 * @throws \UnexpectedValueException if reading an attribute with an unrecognized name
	 */
	public function getAttributeList() : array{
		$list = [];
		$count = $this->getUnsignedVarInt();

		for($i = 0; $i < $count; ++$i){
			$min = $this->getLFloat();
			$max = $this->getLFloat();
			$current = $this->getLFloat();
			$default = $this->getLFloat();
			$name = $this->getString();

			$attr = Attribute::getAttributeByName($name);
			if($attr !== null){
				$attr->setMinValue($min);
				$attr->setMaxValue($max);
				$attr->setValue($current);
				$attr->setDefaultValue($default);

				$list[] = $attr;
			}else{
				throw new \UnexpectedValueException("Unknown attribute type \"$name\"");
			}
		}

		return $list;
	}

	/**
	 * Writes a list of Attributes to the packet buffer using the standard format.
	 * @param Attribute[] ...$attributes
	 */
	public function putAttributeList(Attribute ...$attributes){
		$this->putUnsignedVarInt(count($attributes));
		foreach($attributes as $attribute){
			$this->putLFloat($attribute->getMinValue());
			$this->putLFloat($attribute->getMaxValue());
			$this->putLFloat($attribute->getValue());
			$this->putLFloat($attribute->getDefaultValue());
			$this->putString($attribute->getName());
		}
	}

	/**
	 * Reads and returns an EntityUniqueID
	 * @return int|string
	 */
	public function getEntityUniqueId(){
		return $this->getVarLong();
	}

	/**
	 * Writes an EntityUniqueID
	 * @param int|string $eid
	 */
	public function putEntityUniqueId($eid){
		$this->putVarLong($eid);
	}

	/**
	 * Reads and returns an EntityRuntimeID
	 * @return int|string
	 */
	public function getEntityRuntimeId(){
		return $this->getUnsignedVarLong();
	}

	/**
	 * Writes an EntityUniqueID
	 * @param int|string $eid
	 */
	public function putEntityRuntimeId($eid){
		$this->putUnsignedVarLong($eid);
	}

	/**
	 * Reads an block position with unsigned Y coordinate.
	 * @param int $x
	 * @param int $y 0-255
	 * @param int $z
	 */
	public function getBlockPosition(&$x, &$y, &$z){
		$x = $this->getVarInt();
		$y = $this->getUnsignedVarInt();
		$z = $this->getVarInt();
	}

	/**
	 * Writes a block position with unsigned Y coordinate.
	 * @param int &$x
	 * @param int &$y
	 * @param int &$z
	 */
	public function putBlockPosition($x, $y, $z){
		$this->putVarInt($x);
		$this->putUnsignedVarInt($y);
		$this->putVarInt($z);
	}

	/**
	 * Reads a block position with a signed Y coordinate.
	 * @param int &$x
	 * @param int &$y
	 * @param int &$z
	 */
	public function getSignedBlockPosition(&$x, &$y, &$z){
		$x = $this->getVarInt();
		$y = $this->getVarInt();
		$z = $this->getVarInt();
	}

	/**
	 * Writes a block position with a signed Y coordinate.
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 */
	public function putSignedBlockPosition($x, $y, $z){
		$this->putVarInt($x);
		$this->putVarInt($y);
		$this->putVarInt($z);
	}

	/**
	 * Reads a floating-point vector3 rounded to 4dp.
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 */
	public function getVector3f(&$x, &$y, &$z){
		$x = $this->getRoundedLFloat(4);
		$y = $this->getRoundedLFloat(4);
		$z = $this->getRoundedLFloat(4);
	}

	/**
	 * Writes a floating-point vector3
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 */
	public function putVector3f($x, $y, $z){
		$this->putLFloat($x);
		$this->putLFloat($y);
		$this->putLFloat($z);
	}

	public function getByteRotation() : float{
		return (float) ($this->getByte() * (360 / 256));
	}

	public function putByteRotation(float $rotation){
		$this->putByte((int) ($rotation / (360 / 256)));
	}
}
