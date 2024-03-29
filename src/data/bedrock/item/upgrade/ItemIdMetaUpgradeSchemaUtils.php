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

namespace pocketmine\data\bedrock\item\upgrade;

use pocketmine\data\bedrock\item\upgrade\model\ItemIdMetaUpgradeSchemaModel;
use pocketmine\utils\Filesystem;
use Symfony\Component\Filesystem\Path;
use function gettype;
use function is_object;
use function json_decode;
use function ksort;
use const JSON_THROW_ON_ERROR;
use const SORT_NUMERIC;

final class ItemIdMetaUpgradeSchemaUtils{

	/**
	 * @return ItemIdMetaUpgradeSchema[]
	 * @phpstan-return array<int, ItemIdMetaUpgradeSchema>
	 */
	public static function loadSchemas(string $path, int $maxSchemaId) : array{
		$iterator = new \RegexIterator(
			new \FilesystemIterator(
				$path,
				\FilesystemIterator::KEY_AS_FILENAME | \FilesystemIterator::SKIP_DOTS
			),
			'/^(\d{4}).*\.json$/',
			\RegexIterator::GET_MATCH,
			\RegexIterator::USE_KEY
		);

		$result = [];

		/** @var string[] $matches */
		foreach($iterator as $matches){
			$filename = $matches[0];
			$schemaId = (int) $matches[1];
			if($schemaId > $maxSchemaId){
				continue;
			}

			$fullPath = Path::join($path, $filename);

			$raw = Filesystem::fileGetContents($fullPath);

			try{
				$schema = self::loadSchemaFromString($raw, $schemaId);
			}catch(\RuntimeException $e){
				throw new \RuntimeException("Loading schema file $fullPath: " . $e->getMessage(), 0, $e);
			}

			$result[$schemaId] = $schema;
		}

		ksort($result, SORT_NUMERIC);
		return $result;
	}

	public static function loadSchemaFromString(string $raw, int $schemaId) : ItemIdMetaUpgradeSchema{
		try{
			$json = json_decode($raw, false, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}
		if(!is_object($json)){
			throw new \RuntimeException("Unexpected root type of schema file " . gettype($json) . ", expected object");
		}

		$jsonMapper = new \JsonMapper();
		$jsonMapper->bExceptionOnMissingData = true;
		$jsonMapper->bExceptionOnUndefinedProperty = true;
		$jsonMapper->bStrictObjectTypeChecking = true;
		try{
			$model = $jsonMapper->map($json, new ItemIdMetaUpgradeSchemaModel());
		}catch(\JsonMapper_Exception $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}

		return new ItemIdMetaUpgradeSchema($model->renamedIds, $model->remappedMetas, $schemaId);
	}
}
