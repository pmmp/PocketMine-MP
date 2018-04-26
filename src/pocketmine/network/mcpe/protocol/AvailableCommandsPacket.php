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
use pocketmine\network\mcpe\protocol\types\CommandData;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;

class AvailableCommandsPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_COMMANDS_PACKET;


	/**
	 * This flag is set on all types EXCEPT the POSTFIX type. Not completely sure what this is for, but it is required
	 * for the argtype to work correctly. VALID seems as good a name as any.
	 */
	public const ARG_FLAG_VALID = 0x100000;

	/**
	 * Basic parameter types. These must be combined with the ARG_FLAG_VALID constant.
	 * ARG_FLAG_VALID | (type const)
	 */
	public const ARG_TYPE_INT             = 0x01;
	public const ARG_TYPE_FLOAT           = 0x02;
	public const ARG_TYPE_VALUE           = 0x03;
	public const ARG_TYPE_WILDCARD_INT    = 0x04;
	public const ARG_TYPE_TARGET          = 0x05;
	public const ARG_TYPE_WILDCARD_TARGET = 0x06;

	public const ARG_TYPE_STRING   = 0x0f;
	public const ARG_TYPE_POSITION = 0x10;

	public const ARG_TYPE_MESSAGE  = 0x13;

	public const ARG_TYPE_RAWTEXT  = 0x15;

	public const ARG_TYPE_JSON     = 0x18;

	public const ARG_TYPE_COMMAND  = 0x1f;

	/**
	 * Enums are a little different: they are composed as follows:
	 * ARG_FLAG_ENUM | ARG_FLAG_VALID | (enum index)
	 */
	public const ARG_FLAG_ENUM = 0x200000;

	/**
	 * This is used for /xp <level: int>L. It can only be applied to integer parameters.
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
	 * @var CommandEnum[]
	 * List of command enums, from command aliases to argument enums.
	 */
	public $enums = [];
	/**
	 * @var int[] string => int map of enum name to index
	 */
	private $enumMap = [];

	/**
	 * @var CommandData[]
	 * List of command data, including name, description, alias indexes and parameters.
	 */
	public $commandData = [];

	protected function decodePayload(){
		for($i = 0, $this->enumValuesCount = $this->getUnsignedVarInt(); $i < $this->enumValuesCount; ++$i){
			$this->enumValues[] = $this->getString();
		}

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

	protected function getEnum() : CommandEnum{
		$retval = new CommandEnum();
		$retval->enumName = $this->getString();

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			//Get the enum value from the initial pile of mess
			$retval->enumValues[] = $this->enumValues[$this->getEnumValueIndex()];
		}

		return $retval;
	}

	protected function putEnum(CommandEnum $enum){
		$this->putString($enum->enumName);

		$this->putUnsignedVarInt(count($enum->enumValues));
		foreach($enum->enumValues as $value){
			//Dumb bruteforce search. I hate this packet.
			$index = array_search($value, $this->enumValues, true);
			if($index === false){
				throw new \InvalidStateException("Enum value '$value' not found");
			}
			$this->putEnumValueIndex($index);
		}
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

	protected function putEnumValueIndex(int $index){
		if($this->enumValuesCount < 256){
			$this->putByte($index);
		}elseif($this->enumValuesCount < 65536){
			$this->putLShort($index);
		}else{
			$this->putLInt($index);
		}
	}

	protected function getCommandData() : CommandData{
		$retval = new CommandData();
		$retval->commandName = $this->getString();
		$retval->commandDescription = $this->getString();
		$retval->flags = $this->getByte();
		$retval->permission = $this->getByte();
		$retval->aliases = $this->enums[$this->getLInt()] ?? null;

		for($overloadIndex = 0, $overloadCount = $this->getUnsignedVarInt(); $overloadIndex < $overloadCount; ++$overloadIndex){
			for($paramIndex = 0, $paramCount = $this->getUnsignedVarInt(); $paramIndex < $paramCount; ++$paramIndex){
				$parameter = new CommandParameter();
				$parameter->paramName = $this->getString();
				$parameter->paramType = $this->getLInt();
				$parameter->isOptional = $this->getBool();

				if($parameter->paramType & self::ARG_FLAG_ENUM){
					$index = ($parameter->paramType & 0xffff);
					$parameter->enum = $this->enums[$index] ?? null;

					assert($parameter->enum !== null, "expected enum at $index, but got none");
				}elseif(($parameter->paramType & self::ARG_FLAG_VALID) === 0){ //postfix (guessing)
					$index = ($parameter->paramType & 0xffff);
					$parameter->postfix = $this->postfixes[$index] ?? null;

					assert($parameter->postfix !== null, "expected postfix at $index, but got none");
				}

				$retval->overloads[$overloadIndex][$paramIndex] = $parameter;
			}
		}

		return $retval;
	}

	protected function putCommandData(CommandData $data){
		$this->putString($data->commandName);
		$this->putString($data->commandDescription);
		$this->putByte($data->flags);
		$this->putByte($data->permission);

		if($data->aliases !== null){
			$this->putLInt($this->enumMap[$data->aliases->enumName] ?? -1);
		}else{
			$this->putLInt(-1);
		}

		$this->putUnsignedVarInt(count($data->overloads));
		foreach($data->overloads as $overload){
			/** @var CommandParameter[] $overload */
			$this->putUnsignedVarInt(count($overload));
			foreach($overload as $parameter){
				$this->putString($parameter->paramName);

				if($parameter->enum !== null){
					$type = self::ARG_FLAG_ENUM | self::ARG_FLAG_VALID | ($this->enumMap[$parameter->enum->enumName] ?? -1);
				}elseif($parameter->postfix !== null){
					$key = array_search($parameter->postfix, $this->postfixes, true);
					if($key === false){
						throw new \InvalidStateException("Postfix '$parameter->postfix' not in postfixes array");
					}
					$type = $parameter->paramType << 24 | $key;
				}else{
					$type = $parameter->paramType;
				}

				$this->putLInt($type);
				$this->putBool($parameter->isOptional);
			}
		}
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
				case self::ARG_TYPE_MESSAGE:
					return "message";
				case self::ARG_TYPE_RAWTEXT:
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
		$enumValuesMap = [];
		$postfixesMap = [];
		$enumMap = [];
		foreach($this->commandData as $commandData){
			if($commandData->aliases !== null){
				$enumMap[$commandData->aliases->enumName] = $commandData->aliases;

				foreach($commandData->aliases->enumValues as $str){
					$enumValuesMap[$str] = true;
				}
			}

			foreach($commandData->overloads as $overload){
				/**
				 * @var CommandParameter[] $overload
				 * @var CommandParameter $parameter
				 */
				foreach($overload as $parameter){
					if($parameter->enum !== null){
						$enumMap[$parameter->enum->enumName] = $parameter->enum;
						foreach($parameter->enum->enumValues as $str){
							$enumValuesMap[$str] = true;
						}
					}

					if($parameter->postfix !== null){
						$postfixesMap[$parameter->postfix] = true;
					}
				}
			}
		}

		$this->enumValues = array_map('strval', array_keys($enumValuesMap)); //stupid PHP key casting D:
		$this->putUnsignedVarInt($this->enumValuesCount = count($this->enumValues));
		foreach($this->enumValues as $enumValue){
			$this->putString($enumValue);
		}

		$this->postfixes = array_map('strval', array_keys($postfixesMap));
		$this->putUnsignedVarInt(count($this->postfixes));
		foreach($this->postfixes as $postfix){
			$this->putString($postfix);
		}

		$this->enums = array_values($enumMap);
		$this->enumMap = array_flip(array_keys($enumMap));
		$this->putUnsignedVarInt(count($this->enums));
		foreach($this->enums as $enum){
			$this->putEnum($enum);
		}

		$this->putUnsignedVarInt(count($this->commandData));
		foreach($this->commandData as $data){
			$this->putCommandData($data);
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAvailableCommands($this);
	}

}
