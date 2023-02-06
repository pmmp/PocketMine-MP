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

namespace pocketmine\data\bedrock\item\downgrade;

use function ksort;
use const SORT_NUMERIC;

/**
 * Downgrades old item string IDs and metas to newer ones according to the given schemas.
 */
final class ItemIdMetaDowngrader{

	/**
	 * @var ItemIdMetaDowngradeSchema[]
	 * @phpstan-var array<int, ItemIdMetaDowngradeSchema>
	 */
	private array $idMetaDowngradeSchemas = [];

	/**
	 * @param ItemIdMetaDowngradeSchema[] $idMetaDowngradeSchemas
	 * @phpstan-param array<int, ItemIdMetaDowngradeSchema> $idMetaDowngradeSchemas
	 */
	public function __construct(
		array $idMetaDowngradeSchemas,
	){
		foreach($idMetaDowngradeSchemas as $schema){
			$this->addIdMetaDowngradeSchema($schema);
		}
	}

	public function addIdMetaDowngradeSchema(ItemIdMetaDowngradeSchema $schema) : void{
		if(isset($this->idMetaDowngradeSchemas[$schema->getSchemaId()])){
			throw new \InvalidArgumentException("Already have a schema with priority " . $schema->getSchemaId());
		}
		$this->idMetaDowngradeSchemas[$schema->getSchemaId()] = $schema;
		ksort($this->idMetaDowngradeSchemas, SORT_NUMERIC);
	}

	/**
	 * @phpstan-return array{string, int}
	 */
	public function downgradeStringIdMeta(string $id, int $meta) : array{
		$newId = $id;
		$newMeta = $meta;
		foreach($this->idMetaDowngradeSchemas as $schema){
			if(($remappedMeta = $schema->remapMeta($newId)) !== null){
				[$newId, $newMeta] = $remappedMeta;
			}elseif(($renamedId = $schema->renameId($newId)) !== null){
				$newId = $renamedId;
			}
		}

		return [$newId, $newMeta];
	}
}
