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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\Colored;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use pocketmine\data\bedrock\block\convert\property\CommonProperties;
use pocketmine\data\bedrock\block\convert\property\StringProperty;
use function array_map;
use function count;
use function implode;
use function is_string;

/**
 * Registers serializers and deserializers for block data in a unified style, to avoid code duplication.
 * Not all blocks can be registered this way, but we can avoid a lot of repetition for the ones that can.
 */
final class BlockSerializerDeserializerRegistrar{

	public function __construct(
		public readonly BlockStateToObjectDeserializer $deserializer,
		public readonly BlockObjectToStateSerializer $serializer
	){}

	/**
	 * @param string[]|StringProperty[] $components
	 *
	 * @phpstan-param list<string|StringProperty<*>> $components
	 *
	 * @return string[][]
	 * @phpstan-return list<list<string>>
	 */
	private static function compileFlattenedIdPartMatrix(array $components) : array{
		$result = [];
		foreach($components as $component){
			$column = is_string($component) ? [$component] : $component->getPossibleValues();

			if(count($result) === 0){
				$result = array_map(fn($value) => [$value], $column);
			}else{
				$stepResult = [];
				foreach($result as $parts){
					foreach($column as $value){
						$stepPart = $parts;
						$stepPart[] = $value;
						$stepResult[] = $stepPart;
					}
				}

				$result = $stepResult;
			}
		}

		return $result;
	}

	/**
	 * @param string[]|StringProperty[] $idComponents
	 *
	 * @phpstan-template TBlock of Block
	 *
	 * @phpstan-param TBlock            $block
	 * @phpstan-param list<string|StringProperty<contravariant TBlock>> $idComponents
	 */
	private static function serializeFlattenedId(Block $block, array $idComponents) : string{
		$id = "";
		foreach($idComponents as $infix){
			$id .= is_string($infix) ? $infix : $infix->serializePlain($block);
		}
		return $id;
	}

	/**
	 * @param string[]|StringProperty[] $idComponents
	 * @param string[]                  $idPropertyValues
	 *
	 * @phpstan-template TBlock of Block
	 *
	 * @phpstan-param TBlock            $baseBlock
	 * @phpstan-param list<string|StringProperty<contravariant TBlock>> $idComponents
	 * @phpstan-param list<string>      $idPropertyValues
	 *
	 * @phpstan-return TBlock
	 */
	private static function deserializeFlattenedId(Block $baseBlock, array $idComponents, array $idPropertyValues) : Block{
		$preparedBlock = clone $baseBlock;
		foreach($idComponents as $k => $component){
			if($component instanceof StringProperty){
				$fakeValue = $idPropertyValues[$k];
				$component->deserializePlain($preparedBlock, $fakeValue);
			}
		}

		return $preparedBlock;
	}

	public function mapSimple(Block $block, string $id) : void{
		$this->deserializer->mapSimple($id, fn() => clone $block);
		$this->serializer->mapSimple($block, $id);
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param FlattenedIdModel<TBlock, true> $model
	 */
	public function mapFlattenedId(FlattenedIdModel $model) : void{
		$block = $model->getBlock();

		$idComponents = $model->getIdComponents();
		if(count($idComponents) === 0){
			throw new \InvalidArgumentException("No ID components provided");
		}
		$properties = $model->getProperties();

		//This is a really cursed hack that lets us essentially write flattened properties as blockstate properties, and
		//then pull them out to compile an ID :D
		//This works surprisingly well and is much more elegant than I would've expected

		if(count($properties) > 0){
			$this->serializer->map($block, function(Block $block) use ($idComponents, $properties) : Writer{
				$id = self::serializeFlattenedId($block, $idComponents);

				$writer = new Writer($id);
				foreach($properties as $property){
					$property->serialize($block, $writer);
				}

				return $writer;
			});
		}else{
			$this->serializer->map($block, function(Block $block) use ($idComponents) : BlockStateData{
				//fast path for blocks with no state properties
				$id = self::serializeFlattenedId($block, $idComponents);
				return BlockStateData::current($id, []);
			});
		}

		$idPermutations = self::compileFlattenedIdPartMatrix($idComponents);
		foreach($idPermutations as $idParts){
			//deconstruct the ID into a partial state
			//we can do this at registration time since there will be multiple deserializers
			$preparedBlock = self::deserializeFlattenedId($block, $idComponents, $idParts);
			$id = implode("", $idParts);

			if(count($properties) > 0){
				$this->deserializer->map($id, function(Reader $reader) use ($preparedBlock, $properties) : Block{
					$block = clone $preparedBlock;

					foreach($properties as $property){
						$property->deserialize($block, $reader);
					}
					return $block;
				});
			}else{
				//fast path for blocks with no state properties
				$this->deserializer->map($id, fn() => clone $preparedBlock);
			}
		}
	}

	/**
	 * @phpstan-template TBlock of Block&Colored
	 * @phpstan-param TBlock $block
	 */
	public function mapColored(Block $block, string $idPrefix, string $idSuffix) : void{
		$this->mapFlattenedId(FlattenedIdModel::create($block)
			->idComponents([
				$idPrefix,
				CommonProperties::getInstance()->dyeColorIdInfix,
				$idSuffix
			])
		);
	}

	public function mapSlab(Slab $block, string $type) : void{
		$commonProperties = CommonProperties::getInstance();
		$this->mapFlattenedId(FlattenedIdModel::create($block)
			->idComponents(["minecraft:", $type, "_", $commonProperties->slabIdInfix, "slab"])
			->properties([$commonProperties->slabPositionProperty])
		);
	}

	public function mapStairs(Stair $block, string $id) : void{
		$this->mapModel(Model::create($block, $id)->properties(CommonProperties::getInstance()->stairProperties));
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param Model<TBlock> $model
	 */
	public function mapModel(Model $model) : void{
		$id = $model->getId();
		$block = $model->getBlock();
		$propertyDescriptors = $model->getProperties();

		$this->deserializer->map($id, static function(Reader $in) use ($block, $propertyDescriptors) : Block{
			$newBlock = clone $block;
			foreach($propertyDescriptors as $descriptor){
				$descriptor->deserialize($newBlock, $in);
			}
			return $newBlock;
		});
		$this->serializer->map($block, static function(Block $block) use ($id, $propertyDescriptors) : Writer{
			$writer = new Writer($id);
			foreach($propertyDescriptors as $descriptor){
				$descriptor->serialize($block, $writer);
			}
			return $writer;
		});
	}
}
