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

use pocketmine\network\mcpe\NetworkSession;

class AvailableCommandsPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_COMMANDS_PACKET;


	/**
	 * This flag is set on all types EXCEPT the TEMPLATE type. Not completely sure what this is for, but it is required
	 * for the argtype to work correctly. VALID seems as good a name as any.
	 */
	public const ARG_FLAG_VALID = 0x100000;

	/**
	 * Basic parameter types. These must be combined with the ARG_FLAG_VALID constant.
	 * ARG_FLAG_VALID | (type const)
	 */
	public const ARG_TYPE_INT      = 0x01;
	public const ARG_TYPE_FLOAT    = 0x02;
	public const ARG_TYPE_VALUE    = 0x03;
	public const ARG_TYPE_TARGET   = 0x04;

	public const ARG_TYPE_STRING   = 0x0d;
	public const ARG_TYPE_POSITION = 0x0e;

	public const ARG_TYPE_RAWTEXT  = 0x11;

	public const ARG_TYPE_TEXT     = 0x13;

	public const ARG_TYPE_JSON     = 0x16;

	public const ARG_TYPE_COMMAND  = 0x1d;

	/**
	 * Enums are a little different: they are composed as follows:
	 * ARG_FLAG_ENUM | ARG_FLAG_VALID | (enum index)
	 */
	public const ARG_FLAG_ENUM = 0x200000;

	/**
	 * This is used for for /xp <level: int>L.
	 */
	public const ARG_FLAG_POSTFIX = 0x1000000;

	/**
	 * @var string[]
	 * A list of every single enum value for every single command in the packet, including alias names.
	 */
	public $enumValues = [];
	/** @var int */
	private $enumValuesCount = 0;

	/**
	 * @var string[]
	 * A list of argument postfixes. Used for the /xp command's <int>L.
	 */
	public $postfixes = [];

	/**
	 * @var array
	 * List of enum names, along with a list of ints indicating the enum's possible values from the enumValues array.
	 */
	public $enums = [];

	/**
	 * @var array
	 * List of command data, including name, description, alias indexes and parameters.
	 */
	public $commandData = [];

	protected function decodePayload(){
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->enumValues[] = $this->getString();
		}

		$this->enumValuesCount = count($this->enumValues);


		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->postfixes[] = $this->getString();
		}

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->enums[] = $this->getEnum();
		}

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->commandData[] = $this->getCommandData();
		}
	}

	protected function getEnum(){
		$retval = [];
		$retval["enumName"] = $this->getString();

		$enumValues = [];

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			//Get the enum value from the initial pile of mess
			$enumValues[] = $this->enumValues[$this->getEnumValueIndex()];
		}

		$retval["enumValues"] = $enumValues;

		return $retval;
	}

	protected function getEnumValueIndex() : int{
		if($this->enumValuesCount < 256){
			return $this->getByte();
		}elseif($this->enumValuesCount < 65536){
			return $this->getLShort();
		}else{
			return $this->getLInt();
		}
	}

	protected function getCommandData(){
		$retval = [];
		$retval["commandName"] = $commandName = $this->getString();
		$retval["commandDescription"] = $this->getString();
		$retval["byte1"] = $this->getByte();
		$retval["byte2"] = $this->getByte();
		$retval["aliasesEnum"] = $this->enums[$this->getLInt()] ?? null;

		for($i = 0, $overloadCount = $this->getUnsignedVarInt(); $i < $overloadCount; ++$i){
			for($j = 0, $paramCount = $this->getUnsignedVarInt(); $j < $paramCount; ++$j){
				$retval["overloads"][$i]["params"][$j]["paramName"] = $this->getString();
				$retval["overloads"][$i]["params"][$j]["paramType"] = $type = $this->getLInt();
				$retval["overloads"][$i]["params"][$j]["optional"] = $this->getBool();
				if($type & self::ARG_FLAG_ENUM){
					$index = ($type & 0xffff);
					if(isset($this->enums[$index])){
						$retval["overloads"][$i]["params"][$j]["enum"] = $this->enums[$index];
					}else{
						$retval["overloads"][$i]["params"][$j]["enum"] = null;
					}
				}
				$retval["overloads"][$i]["params"][$j]["paramTypeString"] = $this->argTypeToString($type);
			}
		}

		return $retval;
	}

	private function argTypeToString(int $argtype) : string{
		if($argtype & self::ARG_FLAG_VALID){
			if($argtype & self::ARG_FLAG_ENUM){
				return "stringenum (" . ($argtype & 0xffff) . ")";
			}

			switch($argtype & 0xffff){
				case self::ARG_TYPE_INT:
					return "int";
				case self::ARG_TYPE_FLOAT:
					return "float";
				case self::ARG_TYPE_VALUE:
					return "mixed";
				case self::ARG_TYPE_TARGET:
					return "target";
				case self::ARG_TYPE_STRING:
					return "string";
				case self::ARG_TYPE_POSITION:
					return "xyz";
				case self::ARG_TYPE_RAWTEXT:
					return "rawtext";
				case self::ARG_TYPE_TEXT:
					return "text";
				case self::ARG_TYPE_JSON:
					return "json";
				case self::ARG_TYPE_COMMAND:
					return "command";
			}
		}elseif($argtype !== 0){
			//guessed
			$baseType = $argtype >> 24;
			$typeName = $this->argTypeToString(self::ARG_FLAG_VALID | $baseType);
			$postfix = $this->postfixes[$argtype & 0xffff];

			return $typeName . " (postfix $postfix)";
		}

		return "unknown ($argtype)";
	}

	protected function encodePayload(){
		//TODO
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAvailableCommands($this);
	}

}