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

namespace pocketmine\generate_schemas;

use PHPModelGenerator\Model\GeneratorConfiguration;
use PHPModelGenerator\ModelGenerator;
use PHPModelGenerator\SchemaProcessor\PostProcessor\AdditionalPropertiesAccessorPostProcessor;
use PHPModelGenerator\SchemaProvider\RecursiveDirectoryProvider;
use function dirname;

require dirname(__DIR__) . '/vendor/autoload.php';

(new ModelGenerator(
	(new GeneratorConfiguration())
		->setNamespacePrefix('pocketmine\\plugin\\model')
		->setCollectErrors(false)
		->setImmutable(true)
))
	->generateModelDirectory(dirname(__DIR__) . '/gen/plugin/model')
	->addPostProcessor(new AdditionalPropertiesAccessorPostProcessor(false))
	->generateModels(
		new RecursiveDirectoryProvider(dirname(__DIR__) . '/resources/schema'), dirname(__DIR__) . '/gen/plugin/model'
	);
