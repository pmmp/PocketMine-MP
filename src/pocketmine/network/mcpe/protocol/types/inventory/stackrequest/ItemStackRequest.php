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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;

final class ItemStackRequest{

	/** @var int */
	private $requestId;
	/** @var ItemStackRequestAction[] */
	private $actions;
	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	private $filterStrings;

	/**
	 * @param ItemStackRequestAction[] $actions
	 * @param string[]                 $filterStrings
	 * @phpstan-param list<string> $filterStrings
	 */
	public function __construct(int $requestId, array $actions, array $filterStrings){
		$this->requestId = $requestId;
		$this->actions = $actions;
		$this->filterStrings = $filterStrings;
	}

	public function getRequestId() : int{ return $this->requestId; }

	/** @return ItemStackRequestAction[] */
	public function getActions() : array{ return $this->actions; }

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getFilterStrings() : array{ return $this->filterStrings; }

	private static function readAction(NetworkBinaryStream $in, int $typeId) : ItemStackRequestAction{
		switch($typeId){
			case TakeStackRequestAction::getTypeId(): return TakeStackRequestAction::read($in);
			case PlaceStackRequestAction::getTypeId(): return PlaceStackRequestAction::read($in);
			case SwapStackRequestAction::getTypeId(): return SwapStackRequestAction::read($in);
			case DropStackRequestAction::getTypeId(): return DropStackRequestAction::read($in);
			case DestroyStackRequestAction::getTypeId(): return DestroyStackRequestAction::read($in);
			case CraftingConsumeInputStackRequestAction::getTypeId(): return CraftingConsumeInputStackRequestAction::read($in);
			case CraftingMarkSecondaryResultStackRequestAction::getTypeId(): return CraftingMarkSecondaryResultStackRequestAction::read($in);
			case LabTableCombineStackRequestAction::getTypeId(): return LabTableCombineStackRequestAction::read($in);
			case BeaconPaymentStackRequestAction::getTypeId(): return BeaconPaymentStackRequestAction::read($in);
			case MineBlockStackRequestAction::getTypeId(): return MineBlockStackRequestAction::read($in);
			case CraftRecipeStackRequestAction::getTypeId(): return CraftRecipeStackRequestAction::read($in);
			case CraftRecipeAutoStackRequestAction::getTypeId(): return CraftRecipeAutoStackRequestAction::read($in);
			case CreativeCreateStackRequestAction::getTypeId(): return CreativeCreateStackRequestAction::read($in);
			case CraftRecipeOptionalStackRequestAction::getTypeId(): return CraftRecipeOptionalStackRequestAction::read($in);
			case DeprecatedCraftingNonImplementedStackRequestAction::getTypeId(): return DeprecatedCraftingNonImplementedStackRequestAction::read($in);
			case DeprecatedCraftingResultsStackRequestAction::getTypeId(): return DeprecatedCraftingResultsStackRequestAction::read($in);
		}
		throw new \UnexpectedValueException("Unhandled item stack request action type $typeId");
	}

	public static function read(NetworkBinaryStream $in) : self{
		$requestId = $in->readGenericTypeNetworkId();
		$actions = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$typeId = $in->getByte();
			$actions[] = self::readAction($in, $typeId);
		}
		$filterStrings = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$filterStrings[] = $in->getString();
		}
		return new self($requestId, $actions, $filterStrings);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->writeGenericTypeNetworkId($this->requestId);
		$out->putUnsignedVarInt(count($this->actions));
		foreach($this->actions as $action){
			$out->putByte($action::getTypeId());
			$action->write($out);
		}
		$out->putUnsignedVarInt(count($this->filterStrings));
		foreach($this->filterStrings as $string){
			$out->putString($string);
		}
	}
}
