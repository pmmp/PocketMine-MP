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
	public const LAB_TABLE_COMBINE = 7;
	public const BEACON_PAYMENT = 8;
	public const MINE_BLOCK = 9;
	public const CRAFTING_RECIPE = 10;
	public const CRAFTING_RECIPE_AUTO = 11; //recipe book?
	public const CREATIVE_CREATE = 12;
	public const CRAFTING_RECIPE_OPTIONAL = 13; //anvil/cartography table rename
	public const CRAFTING_NON_IMPLEMENTED_DEPRECATED_ASK_TY_LAING = 14;
	public const CRAFTING_RESULTS_DEPRECATED_ASK_TY_LAING = 15; //no idea what this is for
}
