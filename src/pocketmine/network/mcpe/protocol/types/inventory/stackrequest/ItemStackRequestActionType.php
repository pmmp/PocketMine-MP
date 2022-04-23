<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

final class ItemStackRequestActionType{

	private function __construct(){
		//NOOP
	}

	public const TAKE = 0;
	public const PLACE = 1;
	public const SWAP = 2;
	public const DROP = 3;
	public const DESTROY = 4;
	public const CRAFTING_CONSUME_INPUT = 5;
	public const CRAFTING_MARK_SECONDARY_RESULT_SLOT = 6;
	public const PLACE_INTO_BUNDLE = 7;
	public const TAKE_FROM_BUNDLE = 8;
	public const LAB_TABLE_COMBINE = 9;
	public const BEACON_PAYMENT = 10;
	public const MINE_BLOCK = 11;
	public const CRAFTING_RECIPE = 12;
	public const CRAFTING_RECIPE_AUTO = 13; //recipe book?
	public const CREATIVE_CREATE = 14;
	public const CRAFTING_RECIPE_OPTIONAL = 15; //anvil/cartography table rename
	public const CRAFTING_GRINDSTONE = 16;
	public const CRAFTING_LOOM = 17;
	public const CRAFTING_NON_IMPLEMENTED_DEPRECATED_ASK_TY_LAING = 18;
	public const CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING = 19; //no idea what this is for
}
