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

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandEnumConstraint;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\utils\BinaryDataException;
use function array_search;
use function count;
use function dechex;

class AvailableCommandsPacket extends DataPacket implements ClientboundPacket{
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
	public const ARG_TYPE_OPERATOR        = 0x05;
	public const ARG_TYPE_TARGET          = 0x06;

	public const ARG_TYPE_FILEPATH = 0x0e;

	public const ARG_TYPE_STRING   = 0x1d;

	public const ARG_TYPE_POSITION = 0x25;

	public const ARG_TYPE_MESSAGE  = 0x29;

	public const ARG_TYPE_RAWTEXT  = 0x2b;

	public const ARG_TYPE_JSON     = 0x2f;

	public const ARG_TYPE_COMMAND  = 0x36;

	/**
	 * Enums are a little different: they are composed as follows:
	 * ARG_FLAG_ENUM | ARG_FLAG_VALID | (enum index)
	 */
	public const ARG_FLAG_ENUM = 0x200000;

	/**
	 * This is used for /xp <level: int>L. It can only be applied to integer parameters.
	 */
	public const ARG_FLAG_POSTFIX = 0x1000000;

	public const HARDCODED_ENUM_NAMES = [
		"CommandName" => true
	];

	/**
	 * @var CommandData[]
	 * List of command data, including name, description, alias indexes and parameters.
	 */
	public $commandData = [];

	/**
	 * @var CommandEnum[]
	 * List of enums which aren't directly referenced by any vanilla command.
	 * This is used for the `CommandName` enum, which is a magic enum used by the `command` argument type.
	 */
	public $hardcodedEnums = [];

	/**
	 * @var CommandEnum[]
	 * List of dynamic command enums, also referred to as "soft" enums. These can by dynamically updated mid-game
	 * without resending this packet.
	 */
	public $softEnums = [];

	/**
	 * @var CommandEnumConstraint[]
	 * List of constraints for enum members. Used to constrain gamerules that can bechanged in nocheats mode and more.
	 */
	public $enumConstraints = [];

	protected function decodePayload() : void{
		/** @var string[] $enumValues */
		$enumValues = [];
		for($i = 0, $enumValuesCount = $this->buf->getUnsignedVarInt(); $i < $enumValuesCount; ++$i){
			$enumValues[] = $this->buf->getString();
		}

		/** @var string[] $postfixes */
		$postfixes = [];
		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$postfixes[] = $this->buf->getString();
		}

		/** @var CommandEnum[] $enums */
		$enums = [];
		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$enums[] = $enum = $this->getEnum($enumValues);
			if(isset(self::HARDCODED_ENUM_NAMES[$enum->getName()])){
				$this->hardcodedEnums[] = $enum;
			}
		}

		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$this->commandData[] = $this->getCommandData($enums, $postfixes);
		}

		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$this->softEnums[] = $this->getSoftEnum();
		}

		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$this->enumConstraints[] = $this->getEnumConstraint($enums, $enumValues);
		}
	}

	/**
	 * @param string[] $enumValueList
	 *
	 * @throws BadPacketException
	 * @throws BinaryDataException
	 */
	protected function getEnum(array $enumValueList) : CommandEnum{
		$enumName = $this->buf->getString();
		$enumValues = [];

		$listSize = count($enumValueList);

		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$index = $this->getEnumValueIndex($listSize);
			if(!isset($enumValueList[$index])){
				throw new BadPacketException("Invalid enum value index $index");
			}
			//Get the enum value from the initial pile of mess
			$enumValues[] = $enumValueList[$index];
		}

		return new CommandEnum($enumName, $enumValues);
	}

	/**
	 * @throws BinaryDataException
	 */
	protected function getSoftEnum() : CommandEnum{
		$enumName = $this->buf->getString();
		$enumValues = [];

		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			//Get the enum value from the initial pile of mess
			$enumValues[] = $this->buf->getString();
		}

		return new CommandEnum($enumName, $enumValues);
	}

	/**
	 * @param int[]       $enumValueMap
	 */
	protected function putEnum(CommandEnum $enum, array $enumValueMap) : void{
		$this->buf->putString($enum->getName());

		$values = $enum->getValues();
		$this->buf->putUnsignedVarInt(count($values));
		$listSize = count($enumValueMap);
		foreach($values as $value){
			$index = $enumValueMap[$value] ?? -1;
			if($index === -1){
				throw new \InvalidStateException("Enum value '$value' not found");
			}
			$this->putEnumValueIndex($index, $listSize);
		}
	}

	protected function putSoftEnum(CommandEnum $enum) : void{
		$this->buf->putString($enum->getName());

		$values = $enum->getValues();
		$this->buf->putUnsignedVarInt(count($values));
		foreach($values as $value){
			$this->buf->putString($value);
		}
	}

	/**
	 * @throws BinaryDataException
	 */
	protected function getEnumValueIndex(int $valueCount) : int{
		if($valueCount < 256){
			return $this->buf->getByte();
		}elseif($valueCount < 65536){
			return $this->buf->getLShort();
		}else{
			return $this->buf->getLInt();
		}
	}

	protected function putEnumValueIndex(int $index, int $valueCount) : void{
		if($valueCount < 256){
			$this->buf->putByte($index);
		}elseif($valueCount < 65536){
			$this->buf->putLShort($index);
		}else{
			$this->buf->putLInt($index);
		}
	}

	/**
	 * @param CommandEnum[] $enums
	 * @param string[]      $enumValues
	 *
	 * @throws BadPacketException
	 * @throws BinaryDataException
	 */
	protected function getEnumConstraint(array $enums, array $enumValues) : CommandEnumConstraint{
		//wtf, what was wrong with an offset inside the enum? :(
		$valueIndex = $this->buf->getLInt();
		if(!isset($enumValues[$valueIndex])){
			throw new BadPacketException("Enum constraint refers to unknown enum value index $valueIndex");
		}
		$enumIndex = $this->buf->getLInt();
		if(!isset($enums[$enumIndex])){
			throw new BadPacketException("Enum constraint refers to unknown enum index $enumIndex");
		}
		$enum = $enums[$enumIndex];
		$valueOffset = array_search($enumValues[$valueIndex], $enum->getValues(), true);
		if($valueOffset === false){
			throw new BadPacketException("Value \"" . $enumValues[$valueIndex] . "\" does not belong to enum \"" . $enum->getName() . "\"");
		}

		$constraintIds = [];
		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$constraintIds[] = $this->buf->getByte();
		}

		return new CommandEnumConstraint($enum, $valueOffset, $constraintIds);
	}

	/**
	 * @param int[]                 $enumIndexes string enum name -> int index
	 * @param int[]                 $enumValueIndexes string value -> int index
	 */
	protected function putEnumConstraint(CommandEnumConstraint $constraint, array $enumIndexes, array $enumValueIndexes) : void{
		$this->buf->putLInt($enumValueIndexes[$constraint->getAffectedValue()]);
		$this->buf->putLInt($enumIndexes[$constraint->getEnum()->getName()]);
		$this->buf->putUnsignedVarInt(count($constraint->getConstraints()));
		foreach($constraint->getConstraints() as $v){
			$this->buf->putByte($v);
		}
	}

	/**
	 * @param CommandEnum[] $enums
	 * @param string[]      $postfixes
	 *
	 * @throws BadPacketException
	 * @throws BinaryDataException
	 */
	protected function getCommandData(array $enums, array $postfixes) : CommandData{
		$name = $this->buf->getString();
		$description = $this->buf->getString();
		$flags = $this->buf->getByte();
		$permission = $this->buf->getByte();
		$aliases = $enums[$this->buf->getLInt()] ?? null;
		$overloads = [];

		for($overloadIndex = 0, $overloadCount = $this->buf->getUnsignedVarInt(); $overloadIndex < $overloadCount; ++$overloadIndex){
			$overloads[$overloadIndex] = [];
			for($paramIndex = 0, $paramCount = $this->buf->getUnsignedVarInt(); $paramIndex < $paramCount; ++$paramIndex){
				$parameter = new CommandParameter();
				$parameter->paramName = $this->buf->getString();
				$parameter->paramType = $this->buf->getLInt();
				$parameter->isOptional = $this->buf->getBool();
				$parameter->flags = $this->buf->getByte();

				if(($parameter->paramType & self::ARG_FLAG_ENUM) !== 0){
					$index = ($parameter->paramType & 0xffff);
					$parameter->enum = $enums[$index] ?? null;
					if($parameter->enum === null){
						throw new BadPacketException("deserializing $name parameter $parameter->paramName: expected enum at $index, but got none");
					}
				}elseif(($parameter->paramType & self::ARG_FLAG_POSTFIX) !== 0){
					$index = ($parameter->paramType & 0xffff);
					$parameter->postfix = $postfixes[$index] ?? null;
					if($parameter->postfix === null){
						throw new BadPacketException("deserializing $name parameter $parameter->paramName: expected postfix at $index, but got none");
					}
				}elseif(($parameter->paramType & self::ARG_FLAG_VALID) === 0){
					throw new BadPacketException("deserializing $name parameter $parameter->paramName: Invalid parameter type 0x" . dechex($parameter->paramType));
				}

				$overloads[$overloadIndex][$paramIndex] = $parameter;
			}
		}

		return new CommandData($name, $description, $flags, $permission, $aliases, $overloads);
	}

	/**
	 * @param int[]       $enumIndexes string enum name -> int index
	 * @param int[]       $postfixIndexes
	 */
	protected function putCommandData(CommandData $data, array $enumIndexes, array $postfixIndexes) : void{
		$this->buf->putString($data->name);
		$this->buf->putString($data->description);
		$this->buf->putByte($data->flags);
		$this->buf->putByte($data->permission);

		if($data->aliases !== null){
			$this->buf->putLInt($enumIndexes[$data->aliases->getName()] ?? -1);
		}else{
			$this->buf->putLInt(-1);
		}

		$this->buf->putUnsignedVarInt(count($data->overloads));
		foreach($data->overloads as $overload){
			/** @var CommandParameter[] $overload */
			$this->buf->putUnsignedVarInt(count($overload));
			foreach($overload as $parameter){
				$this->buf->putString($parameter->paramName);

				if($parameter->enum !== null){
					$type = self::ARG_FLAG_ENUM | self::ARG_FLAG_VALID | ($enumIndexes[$parameter->enum->getName()] ?? -1);
				}elseif($parameter->postfix !== null){
					$key = $postfixIndexes[$parameter->postfix] ?? -1;
					if($key === -1){
						throw new \InvalidStateException("Postfix '$parameter->postfix' not in postfixes array");
					}
					$type = self::ARG_FLAG_POSTFIX | $key;
				}else{
					$type = $parameter->paramType;
				}

				$this->buf->putLInt($type);
				$this->buf->putBool($parameter->isOptional);
				$this->buf->putByte($parameter->flags);
			}
		}
	}

	/**
	 * @param string[] $postfixes
	 * @phpstan-param array<int, string> $postfixes
	 */
	private function argTypeToString(int $argtype, array $postfixes) : string{
		if(($argtype & self::ARG_FLAG_VALID) !== 0){
			if(($argtype & self::ARG_FLAG_ENUM) !== 0){
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
		}elseif(($argtype & self::ARG_FLAG_POSTFIX) !== 0){
			$postfix = $postfixes[$argtype & 0xffff];

			return "int (postfix $postfix)";
		}else{
			throw new \UnexpectedValueException("Unknown arg type 0x" . dechex($argtype));
		}

		return "unknown ($argtype)";
	}

	protected function encodePayload() : void{
		/** @var int[] $enumValueIndexes */
		$enumValueIndexes = [];
		/** @var int[] $postfixIndexes */
		$postfixIndexes = [];
		/** @var int[] $enumIndexes */
		$enumIndexes = [];
		/** @var CommandEnum[] $enums */
		$enums = [];

		$addEnumFn = static function(CommandEnum $enum) use (&$enums, &$enumIndexes, &$enumValueIndexes) : void{
			if(!isset($enumIndexes[$enum->getName()])){
				$enums[$enumIndexes[$enum->getName()] = count($enumIndexes)] = $enum;
			}
			foreach($enum->getValues() as $str){
				$enumValueIndexes[$str] = $enumValueIndexes[$str] ?? count($enumValueIndexes); //latest index
			}
		};
		foreach($this->hardcodedEnums as $enum){
			$addEnumFn($enum);
		}
		foreach($this->commandData as $commandData){
			if($commandData->aliases !== null){
				$addEnumFn($commandData->aliases);
			}
			/** @var CommandParameter[] $overload */
			foreach($commandData->overloads as $overload){
				/** @var CommandParameter $parameter */
				foreach($overload as $parameter){
					if($parameter->enum !== null){
						$addEnumFn($parameter->enum);
					}

					if($parameter->postfix !== null){
						$postfixIndexes[$parameter->postfix] = $postfixIndexes[$parameter->postfix] ?? count($postfixIndexes);
					}
				}
			}
		}

		$this->buf->putUnsignedVarInt(count($enumValueIndexes));
		foreach($enumValueIndexes as $enumValue => $index){
			$this->buf->putString((string) $enumValue); //stupid PHP key casting D:
		}

		$this->buf->putUnsignedVarInt(count($postfixIndexes));
		foreach($postfixIndexes as $postfix => $index){
			$this->buf->putString((string) $postfix); //stupid PHP key casting D:
		}

		$this->buf->putUnsignedVarInt(count($enums));
		foreach($enums as $enum){
			$this->putEnum($enum, $enumValueIndexes);
		}

		$this->buf->putUnsignedVarInt(count($this->commandData));
		foreach($this->commandData as $data){
			$this->putCommandData($data, $enumIndexes, $postfixIndexes);
		}

		$this->buf->putUnsignedVarInt(count($this->softEnums));
		foreach($this->softEnums as $enum){
			$this->putSoftEnum($enum);
		}

		$this->buf->putUnsignedVarInt(count($this->enumConstraints));
		foreach($this->enumConstraints as $constraint){
			$this->putEnumConstraint($constraint, $enumIndexes, $enumValueIndexes);
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleAvailableCommands($this);
	}
}
