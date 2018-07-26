<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class CommandParameter{

	public const ARG_TYPE_INT             = AvailableCommandsPacket::ARG_TYPE_INT;
	public const ARG_TYPE_FLOAT           = AvailableCommandsPacket::ARG_TYPE_FLOAT;
	public const ARG_TYPE_VALUE           = AvailableCommandsPacket::ARG_TYPE_VALUE;
	public const ARG_TYPE_WILDCARD_INT    = AvailableCommandsPacket::ARG_TYPE_WILDCARD_INT;
	public const ARG_TYPE_TARGET          = AvailableCommandsPacket::ARG_TYPE_TARGET;
	public const ARG_TYPE_WILDCARD_TARGET = AvailableCommandsPacket::ARG_TYPE_WILDCARD_TARGET;
	public const ARG_TYPE_STRING          = AvailableCommandsPacket::ARG_TYPE_STRING;
	public const ARG_TYPE_POSITION        = AvailableCommandsPacket::ARG_TYPE_POSITION;
	public const ARG_TYPE_MESSAGE         = AvailableCommandsPacket::ARG_TYPE_MESSAGE;
	public const ARG_TYPE_RAWTEXT         = AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
	public const ARG_TYPE_JSON            = AvailableCommandsPacket::ARG_TYPE_JSON;
	public const ARG_TYPE_COMMAND         = AvailableCommandsPacket::ARG_TYPE_COMMAND;

	/** @var string */
	public $paramName;
	/** @var int */
	public $paramType;
	/** @var bool */
	public $isOptional;
	/** @var int */
	public $flag;
	/** @var CommandEnum|null */
	public $enum;
	/** @var string|null */
	public $postfix;

    /**
     * CommandParameter constructor.
     * @param string       $name
     * @param int          $type
     * @param bool         $optional
     * @param string|array $extraData
     * @param int|null     $flag
     */
    public function __construct(string $name, int $type, bool $optional = true, $extraData = null, int $flag = null){
        $this->flag = $flag;
        if($extraData != null){
            if($extraData instanceof CommandEnum){
                $flag = AvailableCommandsPacket::ARG_FLAG_ENUM;
                $this->enum = $extraData;
            }elseif(is_string($extraData)){
                $flag = AvailableCommandsPacket::ARG_FLAG_POSTFIX;
                $this->postfix = $extraData;
            }else{
                throw new \InvalidArgumentException("Extra data is not valid");
            }
        }else{
            $flag = AvailableCommandsPacket::ARG_FLAG_VALID;
        }
        $this->flag = $this->flag ?? $flag;

        $this->paramName = $name;
        $this->paramType = $type;
        $this->flag = $flag;
        $this->isOptional = $optional;
    }
}