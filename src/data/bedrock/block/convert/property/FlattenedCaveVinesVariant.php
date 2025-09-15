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

namespace pocketmine\data\bedrock\block\convert\property;

/**
 * In PM we treat head/body as a bool and berries/no berries as a second bool.
 * However, Bedrock doesn't have IDs to represent a separate head/body without berries, so this enum lets us use an
 * EnumFromStringProperty to deal with this using special getter/setter logic.
 */
enum FlattenedCaveVinesVariant : string{
	case NO_BERRIES = "";
	case HEAD_WITH_BERRIES = "_head_with_berries";
	case BODY_WITH_BERRIES = "_body_with_berries";
}
