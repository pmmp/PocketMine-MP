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

namespace pocketmine\data\bedrock\block\upgrade;

use PHPUnit\Framework\TestCase;
use pocketmine\block\Block;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use const PHP_INT_MAX;

class BlockStateUpgraderTest extends TestCase{

	private const TEST_BLOCK = "pocketmine:test_block";
	private const TEST_BLOCK_2 = "pocketmine:test_block_2";
	private const TEST_PROPERTY = "test_property";
	private const TEST_PROPERTY_2 = "test_property_2";
	private const TEST_VERSION = 1;

	private const TEST_PROPERTY_VALUE_1 = 1;
	private const TEST_PROPERTY_VALUE_2 = 2;
	private const TEST_PROPERTY_VALUE_3 = 3;

	private BlockStateUpgrader $upgrader;

	public function setUp() : void{
		$this->upgrader = new BlockStateUpgrader([]);
	}

	private function getNewSchema() : BlockStateUpgradeSchema{
		return $this->getNewSchemaVersion(PHP_INT_MAX, 0);
	}

	private function getNewSchemaVersion(int $versionId, int $schemaId) : BlockStateUpgradeSchema{
		$schema = new BlockStateUpgradeSchema(($versionId >> 24) & 0xff, ($versionId >> 16) & 0xff, ($versionId >> 8) & 0xff, $versionId & 0xff, $schemaId);
		$this->upgrader->addSchema($schema);
		return $schema;
	}

	/**
	 * @phpstan-param \Closure() : BlockStateData $getStateData
	 */
	private function upgrade(BlockStateData $stateData, \Closure $getStateData) : BlockStateData{
		$result = $this->upgrader->upgrade($stateData);
		self::assertTrue($stateData->equals($getStateData()), "Upgrading states must not alter the original input");

		return $result;
	}

	public function testRenameId() : void{
		$this->getNewSchema()->renamedIds[self::TEST_BLOCK] = self::TEST_BLOCK_2;

		$getStateData = fn() => self::getEmptyPreimage();
		$upgradedStateData = $this->upgrade($getStateData(), $getStateData);

		self::assertSame($upgradedStateData->getName(), self::TEST_BLOCK_2);
	}

	private function prepareAddPropertySchema(BlockStateUpgradeSchema $schema) : void{
		$schema->addedProperties[self::TEST_BLOCK][self::TEST_PROPERTY] = new IntTag(self::TEST_PROPERTY_VALUE_1);
	}

	private static function getEmptyPreimage() : BlockStateData{
		return new BlockStateData(self::TEST_BLOCK, [], self::TEST_VERSION);
	}

	private static function getPreimageOneProperty(string $propertyName, int $value) : BlockStateData{
		return new BlockStateData(
			self::TEST_BLOCK,
			[$propertyName => new IntTag($value)],
			self::TEST_VERSION
		);
	}

	public function testAddNewProperty() : void{
		$this->prepareAddPropertySchema($this->getNewSchema());

		$getStateData = fn() => self::getEmptyPreimage();
		$upgradedStateData = $this->upgrade($getStateData(), $getStateData);

		self::assertSame(self::TEST_PROPERTY_VALUE_1, $upgradedStateData->getState(self::TEST_PROPERTY)?->getValue());
	}

	public function testAddPropertyAlreadyExists() : void{
		$this->prepareAddPropertySchema($this->getNewSchema());

		$getStateData = fn() => self::getPreimageOneProperty(self::TEST_PROPERTY, self::TEST_PROPERTY_VALUE_1 + 1);
		$stateData = $getStateData();
		$upgradedStateData = $this->upgrade($stateData, $getStateData);

		//the object may not be the same due to
		self::assertTrue($stateData->equals($upgradedStateData), "Adding a property that already exists with a different value should not alter the state");
	}

	private function prepareRemovePropertySchema(BlockStateUpgradeSchema $schema) : void{
		$schema->removedProperties[self::TEST_BLOCK][] = self::TEST_PROPERTY;
	}

	/**
	 * @phpstan-return \Generator<int, array{\Closure() : BlockStateData}, void, void>
	 */
	public static function removePropertyProvider() : \Generator{
		yield [fn() => self::getEmptyPreimage()];
		yield [fn() => self::getPreimageOneProperty(self::TEST_PROPERTY, self::TEST_PROPERTY_VALUE_1)];
	}

	/**
	 * @dataProvider removePropertyProvider
	 * @phpstan-param \Closure() : BlockStateData $getStateData
	 */
	public function testRemoveProperty(\Closure $getStateData) : void{
		$this->prepareRemovePropertySchema($this->getNewSchema());

		$upgradedStateData = $this->upgrade($getStateData(), $getStateData);

		self::assertNull($upgradedStateData->getState(self::TEST_PROPERTY));
	}

	private function prepareRenamePropertySchema(BlockStateUpgradeSchema $schema) : void{
		$schema->renamedProperties[self::TEST_BLOCK][self::TEST_PROPERTY] = self::TEST_PROPERTY_2;
	}

	/**
	 * @phpstan-return \Generator<int, array{\Closure() : BlockStateData, ?int}, void, void>
	 */
	public static function renamePropertyProvider() : \Generator{
		yield [fn() => self::getEmptyPreimage(), null];
		yield [fn() => self::getPreimageOneProperty(self::TEST_PROPERTY, self::TEST_PROPERTY_VALUE_1), self::TEST_PROPERTY_VALUE_1];
		yield [fn() => self::getPreimageOneProperty(self::TEST_PROPERTY_2, self::TEST_PROPERTY_VALUE_1), self::TEST_PROPERTY_VALUE_1];
	}

	/**
	 * @dataProvider renamePropertyProvider
	 * @phpstan-param \Closure() : BlockStateData $getStateData
	 */
	public function testRenameProperty(\Closure $getStateData, ?int $valueAfter) : void{
		$this->prepareRenamePropertySchema($this->getNewSchema());

		$upgradedStateData = $this->upgrade($getStateData(), $getStateData);

		self::assertSame($valueAfter, $upgradedStateData->getState(self::TEST_PROPERTY_2)?->getValue());
	}

	private function prepareRemapPropertyValueSchema(BlockStateUpgradeSchema $schema) : void{
		$schema->remappedPropertyValues[self::TEST_BLOCK][self::TEST_PROPERTY][] = new BlockStateUpgradeSchemaValueRemap(
			new IntTag(self::TEST_PROPERTY_VALUE_1),
			new IntTag(self::TEST_PROPERTY_VALUE_2)
		);
	}

	/**
	 * @phpstan-return \Generator<int, array{\Closure() : BlockStateData, ?int}, void, void>
	 */
	public static function remapPropertyValueProvider() : \Generator{
		//no property to remap
		yield [fn() => self::getEmptyPreimage(), null];

		//value that will be remapped
		yield [fn() => self::getPreimageOneProperty(self::TEST_PROPERTY, self::TEST_PROPERTY_VALUE_1), self::TEST_PROPERTY_VALUE_2];

		//value that is already at the target value
		yield [fn() => self::getPreimageOneProperty(self::TEST_PROPERTY, self::TEST_PROPERTY_VALUE_2), self::TEST_PROPERTY_VALUE_2];

		//value that is not remapped and is different from target value (to detect unconditional overwrite bugs)
		yield [fn() => self::getPreimageOneProperty(self::TEST_PROPERTY, self::TEST_PROPERTY_VALUE_3), self::TEST_PROPERTY_VALUE_3];
	}

	/**
	 * @dataProvider remapPropertyValueProvider
	 * @phpstan-param \Closure() : BlockStateData $getStateData
	 */
	public function testRemapPropertyValue(\Closure $getStateData, ?int $valueAfter) : void{
		$this->prepareRemapPropertyValueSchema($this->getNewSchema());

		$upgradedStateData = $this->upgrade($getStateData(), $getStateData);

		self::assertSame($upgradedStateData->getState(self::TEST_PROPERTY)?->getValue(), $valueAfter);
	}

	/**
	 * @dataProvider remapPropertyValueProvider
	 * @phpstan-param \Closure() : BlockStateData $getStateData
	 */
	public function testRemapAndRenameProperty(\Closure $getStateData, ?int $valueAfter) : void{
		$schema = $this->getNewSchema();
		$this->prepareRenamePropertySchema($schema);
		$this->prepareRemapPropertyValueSchema($schema);

		$upgradedStateData = $this->upgrade($getStateData(), $getStateData);

		self::assertSame($upgradedStateData->getState(self::TEST_PROPERTY_2)?->getValue(), $valueAfter);
	}

	public function testFlattenProperty() : void{
		$schema = $this->getNewSchema();
		$schema->flattenedProperties[self::TEST_BLOCK] = new BlockStateUpgradeSchemaFlattenInfo(
			"minecraft:",
			"test",
			"_suffix",
			[],
			StringTag::class
		);

		$stateData = new BlockStateData(self::TEST_BLOCK, ["test" => new StringTag("value1")], 0);
		$upgradedStateData = $this->upgrade($stateData, fn() => $stateData);

		self::assertSame("minecraft:value1_suffix", $upgradedStateData->getName());
		self::assertEmpty($upgradedStateData->getStates());
	}

	/**
	 * @phpstan-return \Generator<int, array{int, int, bool, int}, void, void>
	 */
	public static function upgraderVersionCompatibilityProvider() : \Generator{
		yield [0x1_00_00_00, 0x1_00_00_00, true, 2]; //Same version, multiple schemas targeting version - must be altered, we don't know which schemas are applicable
		yield [0x1_00_00_00, 0x1_00_00_00, false, 1]; //Same version, one schema targeting version - do not change
		yield [0x1_00_01_00, 0x1_00_00_00, true, 1]; //Schema newer than block: must be altered
		yield [0x1_00_00_00, 0x1_00_01_00, false, 1]; //Block newer than schema: block must NOT be altered
	}

	/**
	 * @dataProvider upgraderVersionCompatibilityProvider
	 */
	public function testUpgraderVersionCompatibility(int $schemaVersion, int $stateVersion, bool $shouldChange, int $schemaCount) : void{
		for($i = 0; $i < $schemaCount; $i++){
			$schema = $this->getNewSchemaVersion($schemaVersion, $i);
			$schema->renamedIds[self::TEST_BLOCK] = self::TEST_BLOCK_2;
		}

		$getStateData = fn() => new BlockStateData(
			self::TEST_BLOCK,
			[],
			$stateVersion
		);

		$upgradedStateData = $this->upgrade($getStateData(), $getStateData);
		$originalStateData = $getStateData();

		self::assertNotSame($shouldChange, $upgradedStateData->equals($originalStateData));
	}
}
