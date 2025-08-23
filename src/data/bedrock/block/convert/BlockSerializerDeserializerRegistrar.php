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

use pocketmine\block\ActivatorRail;
use pocketmine\block\AmethystCluster;
use pocketmine\block\Anvil;
use pocketmine\block\Bamboo;
use pocketmine\block\BambooSapling;
use pocketmine\block\Barrel;
use pocketmine\block\Bed;
use pocketmine\block\Bedrock;
use pocketmine\block\Bell;
use pocketmine\block\BigDripleafHead;
use pocketmine\block\BigDripleafStem;
use pocketmine\block\Block;
use pocketmine\block\BrewingStand;
use pocketmine\block\Cactus;
use pocketmine\block\Cake;
use pocketmine\block\Candle;
use pocketmine\block\CaveVines;
use pocketmine\block\ChiseledBookshelf;
use pocketmine\block\ChorusFlower;
use pocketmine\block\CocoaBlock;
use pocketmine\block\Copper;
use pocketmine\block\DaylightSensor;
use pocketmine\block\DetectorRail;
use pocketmine\block\Dirt;
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\DoublePlant;
use pocketmine\block\EndPortalFrame;
use pocketmine\block\EndRod;
use pocketmine\block\Farmland;
use pocketmine\block\FillableCauldron;
use pocketmine\block\Fire;
use pocketmine\block\FloorCoralFan;
use pocketmine\block\Froglight;
use pocketmine\block\FrostedIce;
use pocketmine\block\GlazedTerracotta;
use pocketmine\block\Hopper;
use pocketmine\block\Lantern;
use pocketmine\block\Leaves;
use pocketmine\block\Lectern;
use pocketmine\block\Lever;
use pocketmine\block\Light;
use pocketmine\block\MobHead;
use pocketmine\block\NetherPortal;
use pocketmine\block\NetherVines;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\PinkPetals;
use pocketmine\block\PitcherCrop;
use pocketmine\block\PoweredRail;
use pocketmine\block\Rail;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\RedstoneTorch;
use pocketmine\block\RespawnAnchor;
use pocketmine\block\Sapling;
use pocketmine\block\SeaPickle;
use pocketmine\block\Slab;
use pocketmine\block\SmallDripleaf;
use pocketmine\block\SnowLayer;
use pocketmine\block\Sponge;
use pocketmine\block\Stair;
use pocketmine\block\StraightOnlyRail;
use pocketmine\block\Sugarcane;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\TNT;
use pocketmine\block\TorchflowerCrop;
use pocketmine\block\Tripwire;
use pocketmine\block\TripwireHook;
use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\ChiseledBookshelfSlot;
use pocketmine\block\utils\Colored;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\block\utils\PoweredByRedstone;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\block\Vine;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\data\bedrock\block\convert\BlockStateSerializerHelper as Helper;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use pocketmine\data\bedrock\block\convert\property\BoolFromStringProperty;
use pocketmine\data\bedrock\block\convert\property\BoolProperty;
use pocketmine\data\bedrock\block\convert\property\CommonProperties;
use pocketmine\data\bedrock\block\convert\property\DummyProperty;
use pocketmine\data\bedrock\block\convert\property\EnumFromRawStateMap;
use pocketmine\data\bedrock\block\convert\property\FlattenedCaveVinesVariant;
use pocketmine\data\bedrock\block\convert\property\IntFromRawStateMap;
use pocketmine\data\bedrock\block\convert\property\IntProperty;
use pocketmine\data\bedrock\block\convert\property\OptionSetFromIntProperty;
use pocketmine\data\bedrock\block\convert\property\StringProperty;
use pocketmine\data\bedrock\block\convert\property\ValueFromIntProperty;
use pocketmine\data\bedrock\block\convert\property\ValueFromStringProperty;
use pocketmine\data\bedrock\block\convert\property\ValueMappings;
use pocketmine\math\Facing;
use function array_map;
use function count;
use function implode;
use function is_string;
use function min;
use function range;

/**
 * Registers serializers and deserializers for block data in a unified style, to avoid code duplication.
 * Not all blocks can be registered this way, but we can avoid a lot of repetition for the ones that can.
 */
final class BlockSerializerDeserializerRegistrar{

	public function __construct(
		private ?BlockStateToObjectDeserializer $deserializer,
		private ?BlockObjectToStateSerializer $serializer
	){
		$commonProperties = CommonProperties::getInstance();

		$this->registerSimpleIdOnlyMappings();
		$this->registerColoredMappings($commonProperties);
		$this->registerCandleMappings($commonProperties);
		$this->registerLeavesMappings();
		$this->registerSaplingMappings();
		$this->registerPlantMappings($commonProperties);
		$this->registerCoralMappings($commonProperties);
		$this->registerCopperMappings($commonProperties);
		$this->registerFlattenedEnumMappings($commonProperties);
		$this->registerFlattenedBoolMappings($commonProperties);
		$this->registerStoneLikeSlabMappings();
		$this->registerStoneLikeStairMappings();
		$this->registerStoneLikeWallMappings($commonProperties);

		$this->registerWoodMappings($commonProperties);
		$this->registerTorchMappings($commonProperties);
		$this->registerChemistryMappings($commonProperties);
		$this->register1to1CustomMappings($commonProperties);

		$this->registerSplitMappings();
	}

	private function mapSimple(Block $block, string $id) : void{
		$this->deserializer?->mapSimple($id, fn() => clone $block);
		$this->serializer?->mapSimple($block, $id);
	}

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

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param FlattenedIdModel<TBlock, true> $model
	 */
	private function mapFlattenedId(FlattenedIdModel $model) : void{
		$block = $model->getBlock();

		$idComponents = $model->getIdComponents();
		if(count($idComponents) === 0){
			throw new \InvalidArgumentException("No ID components provided");
		}
		$properties = $model->getProperties();

		//This is a really cursed hack that lets us essentially write flattened properties as blockstate properties, and
		//then pull them out to compile an ID :D
		//This works surprisingly well and is much more elegant than I would've expected

		if($this->serializer !== null){
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
		}

		if($this->deserializer !== null){
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
	}

	/**
	 * @phpstan-template TBlock of Block&Colored
	 * @phpstan-param TBlock $block
	 */
	private function mapColored(Block $block, string $idPrefix, string $idSuffix) : void{
		$this->mapFlattenedId(FlattenedIdModel::create($block)
			->idComponents([
				$idPrefix,
				CommonProperties::getInstance()->dyeColorIdInfix,
				$idSuffix
			])
		);
	}

	private function mapSlab(Slab $block, string $type) : void{
		$commonProperties = CommonProperties::getInstance();
		$this->mapFlattenedId(FlattenedIdModel::create($block)
			->idComponents(["minecraft:", $type, "_", $commonProperties->slabIdInfix, "slab"])
			->properties([$commonProperties->slabPositionProperty])
		);
	}

	private function mapStairs(Stair $block, string $id) : void{
		$this->mapModel(Model::create($block, $id)->properties(CommonProperties::getInstance()->stairProperties));
	}

	private function registerSimpleIdOnlyMappings() : void{
		$this->mapSimple(Blocks::AIR(), Ids::AIR);
		$this->mapSimple(Blocks::AMETHYST(), Ids::AMETHYST_BLOCK);
		$this->mapSimple(Blocks::ANCIENT_DEBRIS(), Ids::ANCIENT_DEBRIS);
		$this->mapSimple(Blocks::ANDESITE(), Ids::ANDESITE);
		$this->mapSimple(Blocks::BARRIER(), Ids::BARRIER);
		$this->mapSimple(Blocks::BEACON(), Ids::BEACON);
		$this->mapSimple(Blocks::BLACKSTONE(), Ids::BLACKSTONE);
		$this->mapSimple(Blocks::BLUE_ICE(), Ids::BLUE_ICE);
		$this->mapSimple(Blocks::BOOKSHELF(), Ids::BOOKSHELF);
		$this->mapSimple(Blocks::BRICKS(), Ids::BRICK_BLOCK);
		$this->mapSimple(Blocks::BROWN_MUSHROOM(), Ids::BROWN_MUSHROOM);
		$this->mapSimple(Blocks::BUDDING_AMETHYST(), Ids::BUDDING_AMETHYST);
		$this->mapSimple(Blocks::CALCITE(), Ids::CALCITE);
		$this->mapSimple(Blocks::CARTOGRAPHY_TABLE(), Ids::CARTOGRAPHY_TABLE);
		$this->mapSimple(Blocks::CHEMICAL_HEAT(), Ids::CHEMICAL_HEAT);
		$this->mapSimple(Blocks::CHISELED_DEEPSLATE(), Ids::CHISELED_DEEPSLATE);
		$this->mapSimple(Blocks::CHISELED_NETHER_BRICKS(), Ids::CHISELED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CHISELED_POLISHED_BLACKSTONE(), Ids::CHISELED_POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::CHISELED_RED_SANDSTONE(), Ids::CHISELED_RED_SANDSTONE);
		$this->mapSimple(Blocks::CHISELED_RESIN_BRICKS(), Ids::CHISELED_RESIN_BRICKS);
		$this->mapSimple(Blocks::CHISELED_SANDSTONE(), Ids::CHISELED_SANDSTONE);
		$this->mapSimple(Blocks::CHISELED_STONE_BRICKS(), Ids::CHISELED_STONE_BRICKS);
		$this->mapSimple(Blocks::CHISELED_TUFF(), Ids::CHISELED_TUFF);
		$this->mapSimple(Blocks::CHISELED_TUFF_BRICKS(), Ids::CHISELED_TUFF_BRICKS);
		$this->mapSimple(Blocks::CHORUS_PLANT(), Ids::CHORUS_PLANT);
		$this->mapSimple(Blocks::CLAY(), Ids::CLAY);
		$this->mapSimple(Blocks::COAL(), Ids::COAL_BLOCK);
		$this->mapSimple(Blocks::COAL_ORE(), Ids::COAL_ORE);
		$this->mapSimple(Blocks::COBBLED_DEEPSLATE(), Ids::COBBLED_DEEPSLATE);
		$this->mapSimple(Blocks::COBBLESTONE(), Ids::COBBLESTONE);
		$this->mapSimple(Blocks::COBWEB(), Ids::WEB);
		$this->mapSimple(Blocks::COPPER_ORE(), Ids::COPPER_ORE);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_BRICKS(), Ids::CRACKED_DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_TILES(), Ids::CRACKED_DEEPSLATE_TILES);
		$this->mapSimple(Blocks::CRACKED_NETHER_BRICKS(), Ids::CRACKED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CRACKED_POLISHED_BLACKSTONE_BRICKS(), Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::CRACKED_STONE_BRICKS(), Ids::CRACKED_STONE_BRICKS);
		$this->mapSimple(Blocks::CRAFTING_TABLE(), Ids::CRAFTING_TABLE);
		$this->mapSimple(Blocks::CRIMSON_ROOTS(), Ids::CRIMSON_ROOTS);
		$this->mapSimple(Blocks::CRYING_OBSIDIAN(), Ids::CRYING_OBSIDIAN);
		$this->mapSimple(Blocks::DANDELION(), Ids::DANDELION);
		$this->mapSimple(Blocks::CUT_RED_SANDSTONE(), Ids::CUT_RED_SANDSTONE);
		$this->mapSimple(Blocks::CUT_SANDSTONE(), Ids::CUT_SANDSTONE);
		$this->mapSimple(Blocks::DARK_PRISMARINE(), Ids::DARK_PRISMARINE);
		$this->mapSimple(Blocks::DEAD_BUSH(), Ids::DEADBUSH);
		$this->mapSimple(Blocks::DEEPSLATE_BRICKS(), Ids::DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::DEEPSLATE_COAL_ORE(), Ids::DEEPSLATE_COAL_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_COPPER_ORE(), Ids::DEEPSLATE_COPPER_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_DIAMOND_ORE(), Ids::DEEPSLATE_DIAMOND_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_EMERALD_ORE(), Ids::DEEPSLATE_EMERALD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_GOLD_ORE(), Ids::DEEPSLATE_GOLD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_IRON_ORE(), Ids::DEEPSLATE_IRON_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_LAPIS_LAZULI_ORE(), Ids::DEEPSLATE_LAPIS_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_TILES(), Ids::DEEPSLATE_TILES);
		$this->mapSimple(Blocks::DIAMOND(), Ids::DIAMOND_BLOCK);
		$this->mapSimple(Blocks::DIAMOND_ORE(), Ids::DIAMOND_ORE);
		$this->mapSimple(Blocks::DIORITE(), Ids::DIORITE);
		$this->mapSimple(Blocks::DRAGON_EGG(), Ids::DRAGON_EGG);
		$this->mapSimple(Blocks::DRIED_KELP(), Ids::DRIED_KELP_BLOCK);
		$this->mapSimple(Blocks::ELEMENT_ACTINIUM(), Ids::ELEMENT_89);
		$this->mapSimple(Blocks::ELEMENT_ALUMINUM(), Ids::ELEMENT_13);
		$this->mapSimple(Blocks::ELEMENT_AMERICIUM(), Ids::ELEMENT_95);
		$this->mapSimple(Blocks::ELEMENT_ANTIMONY(), Ids::ELEMENT_51);
		$this->mapSimple(Blocks::ELEMENT_ARGON(), Ids::ELEMENT_18);
		$this->mapSimple(Blocks::ELEMENT_ARSENIC(), Ids::ELEMENT_33);
		$this->mapSimple(Blocks::ELEMENT_ASTATINE(), Ids::ELEMENT_85);
		$this->mapSimple(Blocks::ELEMENT_BARIUM(), Ids::ELEMENT_56);
		$this->mapSimple(Blocks::ELEMENT_BERKELIUM(), Ids::ELEMENT_97);
		$this->mapSimple(Blocks::ELEMENT_BERYLLIUM(), Ids::ELEMENT_4);
		$this->mapSimple(Blocks::ELEMENT_BISMUTH(), Ids::ELEMENT_83);
		$this->mapSimple(Blocks::ELEMENT_BOHRIUM(), Ids::ELEMENT_107);
		$this->mapSimple(Blocks::ELEMENT_BORON(), Ids::ELEMENT_5);
		$this->mapSimple(Blocks::ELEMENT_BROMINE(), Ids::ELEMENT_35);
		$this->mapSimple(Blocks::ELEMENT_CADMIUM(), Ids::ELEMENT_48);
		$this->mapSimple(Blocks::ELEMENT_CALCIUM(), Ids::ELEMENT_20);
		$this->mapSimple(Blocks::ELEMENT_CALIFORNIUM(), Ids::ELEMENT_98);
		$this->mapSimple(Blocks::ELEMENT_CARBON(), Ids::ELEMENT_6);
		$this->mapSimple(Blocks::ELEMENT_CERIUM(), Ids::ELEMENT_58);
		$this->mapSimple(Blocks::ELEMENT_CESIUM(), Ids::ELEMENT_55);
		$this->mapSimple(Blocks::ELEMENT_CHLORINE(), Ids::ELEMENT_17);
		$this->mapSimple(Blocks::ELEMENT_CHROMIUM(), Ids::ELEMENT_24);
		$this->mapSimple(Blocks::ELEMENT_COBALT(), Ids::ELEMENT_27);
		$this->mapSimple(Blocks::ELEMENT_COPERNICIUM(), Ids::ELEMENT_112);
		$this->mapSimple(Blocks::ELEMENT_COPPER(), Ids::ELEMENT_29);
		$this->mapSimple(Blocks::ELEMENT_CURIUM(), Ids::ELEMENT_96);
		$this->mapSimple(Blocks::ELEMENT_DARMSTADTIUM(), Ids::ELEMENT_110);
		$this->mapSimple(Blocks::ELEMENT_DUBNIUM(), Ids::ELEMENT_105);
		$this->mapSimple(Blocks::ELEMENT_DYSPROSIUM(), Ids::ELEMENT_66);
		$this->mapSimple(Blocks::ELEMENT_EINSTEINIUM(), Ids::ELEMENT_99);
		$this->mapSimple(Blocks::ELEMENT_ERBIUM(), Ids::ELEMENT_68);
		$this->mapSimple(Blocks::ELEMENT_EUROPIUM(), Ids::ELEMENT_63);
		$this->mapSimple(Blocks::ELEMENT_FERMIUM(), Ids::ELEMENT_100);
		$this->mapSimple(Blocks::ELEMENT_FLEROVIUM(), Ids::ELEMENT_114);
		$this->mapSimple(Blocks::ELEMENT_FLUORINE(), Ids::ELEMENT_9);
		$this->mapSimple(Blocks::ELEMENT_FRANCIUM(), Ids::ELEMENT_87);
		$this->mapSimple(Blocks::ELEMENT_GADOLINIUM(), Ids::ELEMENT_64);
		$this->mapSimple(Blocks::ELEMENT_GALLIUM(), Ids::ELEMENT_31);
		$this->mapSimple(Blocks::ELEMENT_GERMANIUM(), Ids::ELEMENT_32);
		$this->mapSimple(Blocks::ELEMENT_GOLD(), Ids::ELEMENT_79);
		$this->mapSimple(Blocks::ELEMENT_HAFNIUM(), Ids::ELEMENT_72);
		$this->mapSimple(Blocks::ELEMENT_HASSIUM(), Ids::ELEMENT_108);
		$this->mapSimple(Blocks::ELEMENT_HELIUM(), Ids::ELEMENT_2);
		$this->mapSimple(Blocks::ELEMENT_HOLMIUM(), Ids::ELEMENT_67);
		$this->mapSimple(Blocks::ELEMENT_HYDROGEN(), Ids::ELEMENT_1);
		$this->mapSimple(Blocks::ELEMENT_INDIUM(), Ids::ELEMENT_49);
		$this->mapSimple(Blocks::ELEMENT_IODINE(), Ids::ELEMENT_53);
		$this->mapSimple(Blocks::ELEMENT_IRIDIUM(), Ids::ELEMENT_77);
		$this->mapSimple(Blocks::ELEMENT_IRON(), Ids::ELEMENT_26);
		$this->mapSimple(Blocks::ELEMENT_KRYPTON(), Ids::ELEMENT_36);
		$this->mapSimple(Blocks::ELEMENT_LANTHANUM(), Ids::ELEMENT_57);
		$this->mapSimple(Blocks::ELEMENT_LAWRENCIUM(), Ids::ELEMENT_103);
		$this->mapSimple(Blocks::ELEMENT_LEAD(), Ids::ELEMENT_82);
		$this->mapSimple(Blocks::ELEMENT_LITHIUM(), Ids::ELEMENT_3);
		$this->mapSimple(Blocks::ELEMENT_LIVERMORIUM(), Ids::ELEMENT_116);
		$this->mapSimple(Blocks::ELEMENT_LUTETIUM(), Ids::ELEMENT_71);
		$this->mapSimple(Blocks::ELEMENT_MAGNESIUM(), Ids::ELEMENT_12);
		$this->mapSimple(Blocks::ELEMENT_MANGANESE(), Ids::ELEMENT_25);
		$this->mapSimple(Blocks::ELEMENT_MEITNERIUM(), Ids::ELEMENT_109);
		$this->mapSimple(Blocks::ELEMENT_MENDELEVIUM(), Ids::ELEMENT_101);
		$this->mapSimple(Blocks::ELEMENT_MERCURY(), Ids::ELEMENT_80);
		$this->mapSimple(Blocks::ELEMENT_MOLYBDENUM(), Ids::ELEMENT_42);
		$this->mapSimple(Blocks::ELEMENT_MOSCOVIUM(), Ids::ELEMENT_115);
		$this->mapSimple(Blocks::ELEMENT_NEODYMIUM(), Ids::ELEMENT_60);
		$this->mapSimple(Blocks::ELEMENT_NEON(), Ids::ELEMENT_10);
		$this->mapSimple(Blocks::ELEMENT_NEPTUNIUM(), Ids::ELEMENT_93);
		$this->mapSimple(Blocks::ELEMENT_NICKEL(), Ids::ELEMENT_28);
		$this->mapSimple(Blocks::ELEMENT_NIHONIUM(), Ids::ELEMENT_113);
		$this->mapSimple(Blocks::ELEMENT_NIOBIUM(), Ids::ELEMENT_41);
		$this->mapSimple(Blocks::ELEMENT_NITROGEN(), Ids::ELEMENT_7);
		$this->mapSimple(Blocks::ELEMENT_NOBELIUM(), Ids::ELEMENT_102);
		$this->mapSimple(Blocks::ELEMENT_OGANESSON(), Ids::ELEMENT_118);
		$this->mapSimple(Blocks::ELEMENT_OSMIUM(), Ids::ELEMENT_76);
		$this->mapSimple(Blocks::ELEMENT_OXYGEN(), Ids::ELEMENT_8);
		$this->mapSimple(Blocks::ELEMENT_PALLADIUM(), Ids::ELEMENT_46);
		$this->mapSimple(Blocks::ELEMENT_PHOSPHORUS(), Ids::ELEMENT_15);
		$this->mapSimple(Blocks::ELEMENT_PLATINUM(), Ids::ELEMENT_78);
		$this->mapSimple(Blocks::ELEMENT_PLUTONIUM(), Ids::ELEMENT_94);
		$this->mapSimple(Blocks::ELEMENT_POLONIUM(), Ids::ELEMENT_84);
		$this->mapSimple(Blocks::ELEMENT_POTASSIUM(), Ids::ELEMENT_19);
		$this->mapSimple(Blocks::ELEMENT_PRASEODYMIUM(), Ids::ELEMENT_59);
		$this->mapSimple(Blocks::ELEMENT_PROMETHIUM(), Ids::ELEMENT_61);
		$this->mapSimple(Blocks::ELEMENT_PROTACTINIUM(), Ids::ELEMENT_91);
		$this->mapSimple(Blocks::ELEMENT_RADIUM(), Ids::ELEMENT_88);
		$this->mapSimple(Blocks::ELEMENT_RADON(), Ids::ELEMENT_86);
		$this->mapSimple(Blocks::ELEMENT_RHENIUM(), Ids::ELEMENT_75);
		$this->mapSimple(Blocks::ELEMENT_RHODIUM(), Ids::ELEMENT_45);
		$this->mapSimple(Blocks::ELEMENT_ROENTGENIUM(), Ids::ELEMENT_111);
		$this->mapSimple(Blocks::ELEMENT_RUBIDIUM(), Ids::ELEMENT_37);
		$this->mapSimple(Blocks::ELEMENT_RUTHENIUM(), Ids::ELEMENT_44);
		$this->mapSimple(Blocks::ELEMENT_RUTHERFORDIUM(), Ids::ELEMENT_104);
		$this->mapSimple(Blocks::ELEMENT_SAMARIUM(), Ids::ELEMENT_62);
		$this->mapSimple(Blocks::ELEMENT_SCANDIUM(), Ids::ELEMENT_21);
		$this->mapSimple(Blocks::ELEMENT_SEABORGIUM(), Ids::ELEMENT_106);
		$this->mapSimple(Blocks::ELEMENT_SELENIUM(), Ids::ELEMENT_34);
		$this->mapSimple(Blocks::ELEMENT_SILICON(), Ids::ELEMENT_14);
		$this->mapSimple(Blocks::ELEMENT_SILVER(), Ids::ELEMENT_47);
		$this->mapSimple(Blocks::ELEMENT_SODIUM(), Ids::ELEMENT_11);
		$this->mapSimple(Blocks::ELEMENT_STRONTIUM(), Ids::ELEMENT_38);
		$this->mapSimple(Blocks::ELEMENT_SULFUR(), Ids::ELEMENT_16);
		$this->mapSimple(Blocks::ELEMENT_TANTALUM(), Ids::ELEMENT_73);
		$this->mapSimple(Blocks::ELEMENT_TECHNETIUM(), Ids::ELEMENT_43);
		$this->mapSimple(Blocks::ELEMENT_TELLURIUM(), Ids::ELEMENT_52);
		$this->mapSimple(Blocks::ELEMENT_TENNESSINE(), Ids::ELEMENT_117);
		$this->mapSimple(Blocks::ELEMENT_TERBIUM(), Ids::ELEMENT_65);
		$this->mapSimple(Blocks::ELEMENT_THALLIUM(), Ids::ELEMENT_81);
		$this->mapSimple(Blocks::ELEMENT_THORIUM(), Ids::ELEMENT_90);
		$this->mapSimple(Blocks::ELEMENT_THULIUM(), Ids::ELEMENT_69);
		$this->mapSimple(Blocks::ELEMENT_TIN(), Ids::ELEMENT_50);
		$this->mapSimple(Blocks::ELEMENT_TITANIUM(), Ids::ELEMENT_22);
		$this->mapSimple(Blocks::ELEMENT_TUNGSTEN(), Ids::ELEMENT_74);
		$this->mapSimple(Blocks::ELEMENT_URANIUM(), Ids::ELEMENT_92);
		$this->mapSimple(Blocks::ELEMENT_VANADIUM(), Ids::ELEMENT_23);
		$this->mapSimple(Blocks::ELEMENT_XENON(), Ids::ELEMENT_54);
		$this->mapSimple(Blocks::ELEMENT_YTTERBIUM(), Ids::ELEMENT_70);
		$this->mapSimple(Blocks::ELEMENT_YTTRIUM(), Ids::ELEMENT_39);
		$this->mapSimple(Blocks::ELEMENT_ZERO(), Ids::ELEMENT_0);
		$this->mapSimple(Blocks::ELEMENT_ZINC(), Ids::ELEMENT_30);
		$this->mapSimple(Blocks::ELEMENT_ZIRCONIUM(), Ids::ELEMENT_40);
		$this->mapSimple(Blocks::EMERALD(), Ids::EMERALD_BLOCK);
		$this->mapSimple(Blocks::EMERALD_ORE(), Ids::EMERALD_ORE);
		$this->mapSimple(Blocks::ENCHANTING_TABLE(), Ids::ENCHANTING_TABLE);
		$this->mapSimple(Blocks::END_STONE(), Ids::END_STONE);
		$this->mapSimple(Blocks::END_STONE_BRICKS(), Ids::END_BRICKS);
		$this->mapSimple(Blocks::FERN(), Ids::FERN);
		$this->mapSimple(Blocks::FLETCHING_TABLE(), Ids::FLETCHING_TABLE);
		$this->mapSimple(Blocks::GILDED_BLACKSTONE(), Ids::GILDED_BLACKSTONE);
		$this->mapSimple(Blocks::GLASS(), Ids::GLASS);
		$this->mapSimple(Blocks::GLASS_PANE(), Ids::GLASS_PANE);
		$this->mapSimple(Blocks::GLOWING_OBSIDIAN(), Ids::GLOWINGOBSIDIAN);
		$this->mapSimple(Blocks::GLOWSTONE(), Ids::GLOWSTONE);
		$this->mapSimple(Blocks::GOLD(), Ids::GOLD_BLOCK);
		$this->mapSimple(Blocks::GOLD_ORE(), Ids::GOLD_ORE);
		$this->mapSimple(Blocks::GRANITE(), Ids::GRANITE);
		$this->mapSimple(Blocks::GRASS(), Ids::GRASS_BLOCK);
		$this->mapSimple(Blocks::GRASS_PATH(), Ids::GRASS_PATH);
		$this->mapSimple(Blocks::GRAVEL(), Ids::GRAVEL);
		$this->mapSimple(Blocks::HANGING_ROOTS(), Ids::HANGING_ROOTS);
		$this->mapSimple(Blocks::HARDENED_CLAY(), Ids::HARDENED_CLAY);
		$this->mapSimple(Blocks::HARDENED_GLASS(), Ids::HARD_GLASS);
		$this->mapSimple(Blocks::HARDENED_GLASS_PANE(), Ids::HARD_GLASS_PANE);
		$this->mapSimple(Blocks::HONEYCOMB(), Ids::HONEYCOMB_BLOCK);
		$this->mapSimple(Blocks::ICE(), Ids::ICE);
		$this->mapSimple(Blocks::INFESTED_CHISELED_STONE_BRICK(), Ids::INFESTED_CHISELED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_COBBLESTONE(), Ids::INFESTED_COBBLESTONE);
		$this->mapSimple(Blocks::INFESTED_CRACKED_STONE_BRICK(), Ids::INFESTED_CRACKED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_MOSSY_STONE_BRICK(), Ids::INFESTED_MOSSY_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_STONE(), Ids::INFESTED_STONE);
		$this->mapSimple(Blocks::INFESTED_STONE_BRICK(), Ids::INFESTED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFO_UPDATE(), Ids::INFO_UPDATE);
		$this->mapSimple(Blocks::INFO_UPDATE2(), Ids::INFO_UPDATE2);
		$this->mapSimple(Blocks::INVISIBLE_BEDROCK(), Ids::INVISIBLE_BEDROCK);
		$this->mapSimple(Blocks::IRON(), Ids::IRON_BLOCK);
		$this->mapSimple(Blocks::IRON_BARS(), Ids::IRON_BARS);
		$this->mapSimple(Blocks::IRON_ORE(), Ids::IRON_ORE);
		$this->mapSimple(Blocks::JUKEBOX(), Ids::JUKEBOX);
		$this->mapSimple(Blocks::LAPIS_LAZULI(), Ids::LAPIS_BLOCK);
		$this->mapSimple(Blocks::LAPIS_LAZULI_ORE(), Ids::LAPIS_ORE);
		$this->mapSimple(Blocks::LEGACY_STONECUTTER(), Ids::STONECUTTER);
		$this->mapSimple(Blocks::LILY_PAD(), Ids::WATERLILY);
		$this->mapSimple(Blocks::MAGMA(), Ids::MAGMA);
		$this->mapSimple(Blocks::MANGROVE_ROOTS(), Ids::MANGROVE_ROOTS);
		$this->mapSimple(Blocks::MELON(), Ids::MELON_BLOCK);
		$this->mapSimple(Blocks::MONSTER_SPAWNER(), Ids::MOB_SPAWNER);
		$this->mapSimple(Blocks::MOSSY_COBBLESTONE(), Ids::MOSSY_COBBLESTONE);
		$this->mapSimple(Blocks::MOSSY_STONE_BRICKS(), Ids::MOSSY_STONE_BRICKS);
		$this->mapSimple(Blocks::MUD(), Ids::MUD);
		$this->mapSimple(Blocks::MUD_BRICKS(), Ids::MUD_BRICKS);
		$this->mapSimple(Blocks::MYCELIUM(), Ids::MYCELIUM);
		$this->mapSimple(Blocks::NETHERITE(), Ids::NETHERITE_BLOCK);
		$this->mapSimple(Blocks::NETHERRACK(), Ids::NETHERRACK);
		$this->mapSimple(Blocks::NETHER_BRICKS(), Ids::NETHER_BRICK);
		$this->mapSimple(Blocks::NETHER_BRICK_FENCE(), Ids::NETHER_BRICK_FENCE);
		$this->mapSimple(Blocks::NETHER_GOLD_ORE(), Ids::NETHER_GOLD_ORE);
		$this->mapSimple(Blocks::NETHER_QUARTZ_ORE(), Ids::QUARTZ_ORE);
		$this->mapSimple(Blocks::NETHER_REACTOR_CORE(), Ids::NETHERREACTOR);
		$this->mapSimple(Blocks::NETHER_WART_BLOCK(), Ids::NETHER_WART_BLOCK);
		$this->mapSimple(Blocks::NOTE_BLOCK(), Ids::NOTEBLOCK);
		$this->mapSimple(Blocks::OBSIDIAN(), Ids::OBSIDIAN);
		$this->mapSimple(Blocks::PACKED_ICE(), Ids::PACKED_ICE);
		$this->mapSimple(Blocks::PACKED_MUD(), Ids::PACKED_MUD);
		$this->mapSimple(Blocks::PODZOL(), Ids::PODZOL);
		$this->mapSimple(Blocks::POLISHED_ANDESITE(), Ids::POLISHED_ANDESITE);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE(), Ids::POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE_BRICKS(), Ids::POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::POLISHED_DEEPSLATE(), Ids::POLISHED_DEEPSLATE);
		$this->mapSimple(Blocks::POLISHED_DIORITE(), Ids::POLISHED_DIORITE);
		$this->mapSimple(Blocks::POLISHED_GRANITE(), Ids::POLISHED_GRANITE);
		$this->mapSimple(Blocks::POLISHED_TUFF(), Ids::POLISHED_TUFF);
		$this->mapSimple(Blocks::PRISMARINE(), Ids::PRISMARINE);
		$this->mapSimple(Blocks::PRISMARINE_BRICKS(), Ids::PRISMARINE_BRICKS);
		$this->mapSimple(Blocks::QUARTZ_BRICKS(), Ids::QUARTZ_BRICKS);
		$this->mapSimple(Blocks::RAW_COPPER(), Ids::RAW_COPPER_BLOCK);
		$this->mapSimple(Blocks::RAW_GOLD(), Ids::RAW_GOLD_BLOCK);
		$this->mapSimple(Blocks::RAW_IRON(), Ids::RAW_IRON_BLOCK);
		$this->mapSimple(Blocks::REDSTONE(), Ids::REDSTONE_BLOCK);
		$this->mapSimple(Blocks::RED_MUSHROOM(), Ids::RED_MUSHROOM);
		$this->mapSimple(Blocks::RED_NETHER_BRICKS(), Ids::RED_NETHER_BRICK);
		$this->mapSimple(Blocks::RED_SAND(), Ids::RED_SAND);
		$this->mapSimple(Blocks::RED_SANDSTONE(), Ids::RED_SANDSTONE);
		$this->mapSimple(Blocks::REINFORCED_DEEPSLATE(), Ids::REINFORCED_DEEPSLATE);
		$this->mapSimple(Blocks::RESERVED6(), Ids::RESERVED6);
		$this->mapSimple(Blocks::RESIN(), Ids::RESIN_BLOCK);
		$this->mapSimple(Blocks::RESIN_BRICKS(), Ids::RESIN_BRICKS);
		$this->mapSimple(Blocks::SAND(), Ids::SAND);
		$this->mapSimple(Blocks::SANDSTONE(), Ids::SANDSTONE);
		$this->mapSimple(Blocks::SCULK(), Ids::SCULK);
		$this->mapSimple(Blocks::SEA_LANTERN(), Ids::SEA_LANTERN);
		$this->mapSimple(Blocks::SHROOMLIGHT(), Ids::SHROOMLIGHT);
		$this->mapSimple(Blocks::SHULKER_BOX(), Ids::UNDYED_SHULKER_BOX);
		$this->mapSimple(Blocks::SLIME(), Ids::SLIME);
		$this->mapSimple(Blocks::SMITHING_TABLE(), Ids::SMITHING_TABLE);
		$this->mapSimple(Blocks::SMOOTH_BASALT(), Ids::SMOOTH_BASALT);
		$this->mapSimple(Blocks::SMOOTH_RED_SANDSTONE(), Ids::SMOOTH_RED_SANDSTONE);
		$this->mapSimple(Blocks::SMOOTH_SANDSTONE(), Ids::SMOOTH_SANDSTONE);
		$this->mapSimple(Blocks::SMOOTH_STONE(), Ids::SMOOTH_STONE);
		$this->mapSimple(Blocks::SNOW(), Ids::SNOW);
		$this->mapSimple(Blocks::SOUL_SAND(), Ids::SOUL_SAND);
		$this->mapSimple(Blocks::SOUL_SOIL(), Ids::SOUL_SOIL);
		$this->mapSimple(Blocks::SPORE_BLOSSOM(), Ids::SPORE_BLOSSOM);
		$this->mapSimple(Blocks::STONE(), Ids::STONE);
		$this->mapSimple(Blocks::STONE_BRICKS(), Ids::STONE_BRICKS);
		$this->mapSimple(Blocks::TALL_GRASS(), Ids::SHORT_GRASS);  //no, this is not a typo - tall_grass is now the double block, just to be confusing :(
		$this->mapSimple(Blocks::TINTED_GLASS(), Ids::TINTED_GLASS);
		$this->mapSimple(Blocks::TORCHFLOWER(), Ids::TORCHFLOWER);
		$this->mapSimple(Blocks::TUFF(), Ids::TUFF);
		$this->mapSimple(Blocks::TUFF_BRICKS(), Ids::TUFF_BRICKS);
		$this->mapSimple(Blocks::WARPED_WART_BLOCK(), Ids::WARPED_WART_BLOCK);
		$this->mapSimple(Blocks::WARPED_ROOTS(), Ids::WARPED_ROOTS);
		$this->mapSimple(Blocks::WITHER_ROSE(), Ids::WITHER_ROSE);

		$this->mapSimple(Blocks::ALLIUM(), Ids::ALLIUM);
		$this->mapSimple(Blocks::CORNFLOWER(), Ids::CORNFLOWER);
		$this->mapSimple(Blocks::AZURE_BLUET(), Ids::AZURE_BLUET);
		$this->mapSimple(Blocks::LILY_OF_THE_VALLEY(), Ids::LILY_OF_THE_VALLEY);
		$this->mapSimple(Blocks::BLUE_ORCHID(), Ids::BLUE_ORCHID);
		$this->mapSimple(Blocks::OXEYE_DAISY(), Ids::OXEYE_DAISY);
		$this->mapSimple(Blocks::POPPY(), Ids::POPPY);
		$this->mapSimple(Blocks::ORANGE_TULIP(), Ids::ORANGE_TULIP);
		$this->mapSimple(Blocks::PINK_TULIP(), Ids::PINK_TULIP);
		$this->mapSimple(Blocks::RED_TULIP(), Ids::RED_TULIP);
		$this->mapSimple(Blocks::WHITE_TULIP(), Ids::WHITE_TULIP);
	}

	private function registerColoredMappings(CommonProperties $commonProperties) : void{
		$this->mapColored(Blocks::STAINED_HARDENED_GLASS(), "minecraft:hard_", "_stained_glass");
		$this->mapColored(Blocks::STAINED_HARDENED_GLASS_PANE(), "minecraft:hard_", "_stained_glass_pane");

		$this->mapColored(Blocks::CARPET(), "minecraft:", "_carpet");
		$this->mapColored(Blocks::CONCRETE(), "minecraft:", "_concrete");
		$this->mapColored(Blocks::CONCRETE_POWDER(), "minecraft:", "_concrete_powder");
		$this->mapColored(Blocks::DYED_SHULKER_BOX(), "minecraft:", "_shulker_box");
		$this->mapColored(Blocks::STAINED_CLAY(), "minecraft:", "_terracotta");
		$this->mapColored(Blocks::STAINED_GLASS(), "minecraft:", "_stained_glass");
		$this->mapColored(Blocks::STAINED_GLASS_PANE(), "minecraft:", "_stained_glass_pane");
		$this->mapColored(Blocks::WOOL(), "minecraft:", "_wool");

		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::GLAZED_TERRACOTTA())
			->idComponents([
				"minecraft:",
				new ValueFromStringProperty("color", ValueMappings::getInstance()->dyeColorWithSilver, fn(GlazedTerracotta $b) => $b->getColor(), fn(GlazedTerracotta $b, DyeColor $v) => $b->setColor($v)),
				"_glazed_terracotta"
			])
			->properties([$commonProperties->horizontalFacingClassic])
		);
	}

	private function registerCandleMappings(CommonProperties $commonProperties) : void{
		$candleProperties = [
			$commonProperties->lit,
			new IntProperty(StateNames::CANDLES, 0, 3, fn(Candle $b) => $b->getCount(), fn(Candle $b, int $v) => $b->setCount($v), offset: 1),
		];
		$cakeWithCandleProperties = [$commonProperties->lit];
		$this->mapModel(Model::create(Blocks::CANDLE(), Ids::CANDLE)->properties($candleProperties));
		$this->mapModel(Model::create(Blocks::CAKE_WITH_CANDLE(), Ids::CANDLE_CAKE)->properties($cakeWithCandleProperties));

		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::DYED_CANDLE())
			->idComponents([
				"minecraft:",
				$commonProperties->dyeColorIdInfix,
				"_candle"
			])
			->properties($candleProperties)
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CAKE_WITH_DYED_CANDLE())
			->idComponents([
				"minecraft:",
				$commonProperties->dyeColorIdInfix,
				"_candle_cake"
			])
			->properties($cakeWithCandleProperties)
		);
	}

	private function registerLeavesMappings() : void{
		$properties = [
			new BoolProperty(StateNames::PERSISTENT_BIT, fn(Leaves $b) => $b->isNoDecay(), fn(Leaves $b, bool $v) => $b->setNoDecay($v)),
			new BoolProperty(StateNames::UPDATE_BIT, fn(Leaves $b) => $b->isCheckDecay(), fn(Leaves $b, bool $v) => $b->setCheckDecay($v)),
		];
		foreach([
			Ids::ACACIA_LEAVES => Blocks::ACACIA_LEAVES(),
			Ids::AZALEA_LEAVES => Blocks::AZALEA_LEAVES(),
			Ids::AZALEA_LEAVES_FLOWERED => Blocks::FLOWERING_AZALEA_LEAVES(),
			Ids::BIRCH_LEAVES => Blocks::BIRCH_LEAVES(),
			Ids::CHERRY_LEAVES => Blocks::CHERRY_LEAVES(),
			Ids::DARK_OAK_LEAVES => Blocks::DARK_OAK_LEAVES(),
			Ids::JUNGLE_LEAVES => Blocks::JUNGLE_LEAVES(),
			Ids::MANGROVE_LEAVES => Blocks::MANGROVE_LEAVES(),
			Ids::OAK_LEAVES => Blocks::OAK_LEAVES(),
			Ids::PALE_OAK_LEAVES => Blocks::PALE_OAK_LEAVES(),
			Ids::SPRUCE_LEAVES => Blocks::SPRUCE_LEAVES()
		] as $id => $block){
			$this->mapModel(Model::create($block, $id)->properties($properties));
		}
	}

	private function registerSaplingMappings() : void{
		$properties = [
			new BoolProperty(StateNames::AGE_BIT, fn(Sapling $b) => $b->isReady(), fn(Sapling $b, bool $v) => $b->setReady($v)),
		];
		foreach([
			Ids::ACACIA_SAPLING => Blocks::ACACIA_SAPLING(),
			Ids::BIRCH_SAPLING => Blocks::BIRCH_SAPLING(),
			Ids::DARK_OAK_SAPLING => Blocks::DARK_OAK_SAPLING(),
			Ids::JUNGLE_SAPLING => Blocks::JUNGLE_SAPLING(),
			Ids::OAK_SAPLING => Blocks::OAK_SAPLING(),
			Ids::SPRUCE_SAPLING => Blocks::SPRUCE_SAPLING(),
		] as $id => $block){
			$this->mapModel(Model::create($block, $id)->properties($properties));
		}
	}

	private function registerPlantMappings(CommonProperties $commonProperties) : void{
		$this->mapModel(Model::create(Blocks::BEETROOTS(), Ids::BEETROOT)->properties([$commonProperties->cropAgeMax7]));
		$this->mapModel(Model::create(Blocks::CARROTS(), Ids::CARROTS)->properties([$commonProperties->cropAgeMax7]));
		$this->mapModel(Model::create(Blocks::POTATOES(), Ids::POTATOES)->properties([$commonProperties->cropAgeMax7]));
		$this->mapModel(Model::create(Blocks::WHEAT(), Ids::WHEAT)->properties([$commonProperties->cropAgeMax7]));

		$this->mapModel(Model::create(Blocks::MELON_STEM(), Ids::MELON_STEM)->properties($commonProperties->stemProperties));
		$this->mapModel(Model::create(Blocks::PUMPKIN_STEM(), Ids::PUMPKIN_STEM)->properties($commonProperties->stemProperties));

		foreach([
			[Blocks::DOUBLE_TALLGRASS(), Ids::TALL_GRASS],
			[Blocks::LARGE_FERN(), Ids::LARGE_FERN],
			[Blocks::LILAC(), Ids::LILAC],
			[Blocks::PEONY(), Ids::PEONY],
			[Blocks::ROSE_BUSH(), Ids::ROSE_BUSH],
			[Blocks::SUNFLOWER(), Ids::SUNFLOWER],
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties([$commonProperties->doublePlantHalf]));
		}

		foreach([
			[Blocks::BROWN_MUSHROOM_BLOCK(), Ids::BROWN_MUSHROOM_BLOCK],
			[Blocks::RED_MUSHROOM_BLOCK(), Ids::RED_MUSHROOM_BLOCK]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties([
				new ValueFromIntProperty(StateNames::HUGE_MUSHROOM_BITS, ValueMappings::getInstance()->mushroomBlockType, fn(RedMushroomBlock $b) => $b->getMushroomBlockType(), fn(RedMushroomBlock $b, MushroomBlockType $v) => $b->setMushroomBlockType($v)),
			]));
		}

		$this->mapModel(Model::create(Blocks::GLOW_LICHEN(), Ids::GLOW_LICHEN)->properties([$commonProperties->multiFacingFlags]));
		$this->mapModel(Model::create(Blocks::RESIN_CLUMP(), Ids::RESIN_CLUMP)->properties([$commonProperties->multiFacingFlags]));

		$this->mapModel(Model::create(Blocks::VINES(), Ids::VINE)->properties([
			new OptionSetFromIntProperty(
				StateNames::VINE_DIRECTION_BITS,
				IntFromRawStateMap::int([
					Facing::NORTH => BlockLegacyMetadata::VINE_FLAG_NORTH,
					Facing::SOUTH => BlockLegacyMetadata::VINE_FLAG_SOUTH,
					Facing::WEST => BlockLegacyMetadata::VINE_FLAG_WEST,
					Facing::EAST => BlockLegacyMetadata::VINE_FLAG_EAST,
				]),
				fn(Vine $b) => $b->getFaces(),
				fn(Vine $b, array $v) => $b->setFaces($v)
			)
		]));

		$this->mapModel(Model::create(Blocks::SWEET_BERRY_BUSH(), Ids::SWEET_BERRY_BUSH)->properties([
			//TODO: berry bush only wants 0-3, but it can be bigger in MCPE due to misuse of GROWTH state which goes up to 7
			new IntProperty(StateNames::GROWTH, 0, 7, fn(SweetBerryBush $b) => $b->getAge(), fn(SweetBerryBush $b, int $v) => $b->setAge(min($v, SweetBerryBush::STAGE_MATURE)))
		]));
		$this->mapModel(Model::create(Blocks::TORCHFLOWER_CROP(), Ids::TORCHFLOWER_CROP)->properties([
			//TODO: this property can have values 0-7, but only 0-1 are valid
			new IntProperty(StateNames::GROWTH, 0, 7, fn(TorchflowerCrop $b) => $b->isReady() ? 1 : 0, fn(TorchflowerCrop $b, int $v) => $b->setReady($v !== 0))
		]));
	}

	private function registerCoralMappings(CommonProperties $commonProperties) : void{
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CORAL())->idComponents([...$commonProperties->coralIdPrefixes, "_coral"]));
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CORAL_BLOCK())->idComponents([...$commonProperties->coralIdPrefixes, "_coral_block"]));
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CORAL_FAN())
			->idComponents([...$commonProperties->coralIdPrefixes, "_coral_fan"])
			->properties([
				new ValueFromIntProperty(StateNames::CORAL_FAN_DIRECTION, ValueMappings::getInstance()->coralAxis, fn(FloorCoralFan $b) => $b->getAxis(), fn(FloorCoralFan $b, int $v) => $b->setAxis($v))
			])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::WALL_CORAL_FAN())
			->idComponents([...$commonProperties->coralIdPrefixes, "_coral_wall_fan"])
			->properties([
				new ValueFromIntProperty(StateNames::CORAL_DIRECTION, ValueMappings::getInstance()->horizontalFacingCoral, fn(HorizontalFacing $b) => $b->getFacing(), fn(HorizontalFacing $b, int $v) => $b->setFacing($v)),
			])
		);
	}

	private function registerCopperMappings(CommonProperties $commonProperties) : void{
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::COPPER_BULB())
			->idComponents([...$commonProperties->copperIdPrefixes, "copper_bulb"])
			->properties([
				$commonProperties->lit,
				new BoolProperty(StateNames::POWERED_BIT, fn(PoweredByRedstone $b) => $b->isPowered(), fn(PoweredByRedstone $b, bool $v) => $b->setPowered($v)),
			])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::COPPER())
			->idComponents([
				...$commonProperties->copperIdPrefixes,
				"copper",
				//HACK: the non-waxed, non-oxidised variant has a _block suffix, but none of the others do
				new BoolFromStringProperty("bruhhhh", "", "_block", fn(Copper $b) => !$b->isWaxed() && $b->getOxidation() === CopperOxidation::NONE, fn() => null)
			])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CHISELED_COPPER())->idComponents([...$commonProperties->copperIdPrefixes, "chiseled_copper"]));
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::COPPER_GRATE())->idComponents([...$commonProperties->copperIdPrefixes, "copper_grate"]));
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CUT_COPPER())->idComponents([...$commonProperties->copperIdPrefixes, "cut_copper"]));
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CUT_COPPER_STAIRS())
			->idComponents([...$commonProperties->copperIdPrefixes, "cut_copper_stairs"])
			->properties($commonProperties->stairProperties)
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::COPPER_TRAPDOOR())
			->idComponents([...$commonProperties->copperIdPrefixes, "copper_trapdoor"])
			->properties($commonProperties->trapdoorProperties)
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::COPPER_DOOR())
			->idComponents([...$commonProperties->copperIdPrefixes, "copper_door"])
			->properties($commonProperties->doorProperties)
		);

		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CUT_COPPER_SLAB())
			->idComponents([
				...$commonProperties->copperIdPrefixes,
				$commonProperties->slabIdInfix,
				"cut_copper_slab"
			])
			->properties([$commonProperties->slabPositionProperty])
		);
	}

	private function registerFlattenedEnumMappings(CommonProperties $commonProperties) : void{
		//A
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::ANVIL())
			->idComponents([
				new ValueFromStringProperty("id", IntFromRawStateMap::string([
					0 => Ids::ANVIL,
					1 => Ids::CHIPPED_ANVIL,
					2 => Ids::DAMAGED_ANVIL,
				]), fn(Anvil $b) => $b->getDamage(), fn(Anvil $b, int $v) => $b->setDamage($v))
			])
			->properties([$commonProperties->horizontalFacingCardinal])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::AMETHYST_CLUSTER())
			->idComponents([
				new ValueFromStringProperty("id", IntFromRawStateMap::string([
					AmethystCluster::STAGE_SMALL_BUD => Ids::SMALL_AMETHYST_BUD,
					AmethystCluster::STAGE_MEDIUM_BUD => Ids::MEDIUM_AMETHYST_BUD,
					AmethystCluster::STAGE_LARGE_BUD => Ids::LARGE_AMETHYST_BUD,
					AmethystCluster::STAGE_CLUSTER => Ids::AMETHYST_CLUSTER
				]), fn(AmethystCluster $b) => $b->getStage(), fn(AmethystCluster $b, int $v) => $b->setStage($v))
			])
			->properties([$commonProperties->blockFace])
		);

		//C
		//This one is a special offender :<
		//I have no idea why this only has 3 IDs - there are 4 in Java and 4 visually distinct states in Bedrock
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::CAVE_VINES())
			->idComponents([
				"minecraft:cave_vines",
				new ValueFromStringProperty(
					"variant",
					EnumFromRawStateMap::string(FlattenedCaveVinesVariant::class, fn(FlattenedCaveVinesVariant $case) => $case->value),
					fn(CaveVines $b) => $b->hasBerries() ?
						($b->isHead() ?
							FlattenedCaveVinesVariant::HEAD_WITH_BERRIES :
							FlattenedCaveVinesVariant::BODY_WITH_BERRIES) :
						FlattenedCaveVinesVariant::NO_BERRIES,
					fn(CaveVines $b, FlattenedCaveVinesVariant $v) => match($v){
						FlattenedCaveVinesVariant::HEAD_WITH_BERRIES => $b->setBerries(true)->setHead(true),
						FlattenedCaveVinesVariant::BODY_WITH_BERRIES => $b->setBerries(true)->setHead(false),
						FlattenedCaveVinesVariant::NO_BERRIES => $b->setBerries(false)->setHead(false), //assume this isn't a head, since we don't have enough information
					}
				)
			])
			->properties([
				new IntProperty(StateNames::GROWING_PLANT_AGE, 0, 25, fn(CaveVines $b) => $b->getAge(), fn(CaveVines $b, int $v) => $b->setAge($v)),
			])
		);

		//D
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::DIRT())
			->idComponents([
				new ValueFromStringProperty("id", EnumFromRawStateMap::string(DirtType::class, fn(DirtType $case) => match ($case) {
					DirtType::NORMAL => Ids::DIRT,
					DirtType::COARSE => Ids::COARSE_DIRT,
					DirtType::ROOTED => Ids::DIRT_WITH_ROOTS,
				}), fn(Dirt $b) => $b->getDirtType(), fn(Dirt $b, DirtType $v) => $b->setDirtType($v))
			])
		);

		//F
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::FROGLIGHT())
			->idComponents([
				new ValueFromStringProperty("id", ValueMappings::getInstance()->froglightType, fn(Froglight $b) => $b->getFroglightType(), fn(Froglight $b, FroglightType $v) => $b->setFroglightType($v)),
			])
			->properties([$commonProperties->pillarAxis])
		);

		//L
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::LIGHT())
			->idComponents([
				"minecraft:light_block_",
				//this is a bit shit but it's easier than adapting IntProperty to support flattening :D
				new ValueFromStringProperty(
					"light_level",
					IntFromRawStateMap::string(array_map(strval(...), range(0, 15))),
					fn(Light $b) => $b->getLightLevel(),
					fn(Light $b, int $v) => $b->setLightLevel($v)
				)
			])
		);

		//M
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::MOB_HEAD())
			->idComponents([
				new ValueFromStringProperty("id", ValueMappings::getInstance()->mobHeadType, fn(MobHead $b) => $b->getMobHeadType(), fn(MobHead $b, MobHeadType $v) => $b->setMobHeadType($v)),
			])
			->properties([
				new ValueFromIntProperty(StateNames::FACING_DIRECTION, ValueMappings::getInstance()->facingExceptDown, fn(MobHead $b) => $b->getFacing(), fn(MobHead $b, int $v) => $b->setFacing($v))
			])
		);

		foreach([
			[Blocks::LAVA(), "lava"],
			[Blocks::WATER(), "water"]
		] as [$block, $idSuffix]){
			$this->mapFlattenedId(FlattenedIdModel::create($block)
				->idComponents([...$commonProperties->liquidIdPrefixes, $idSuffix])
				->properties([$commonProperties->liquidData])
			);
		}
	}

	private function registerFlattenedBoolMappings(CommonProperties $commonProperties) : void{
		foreach([
			[Blocks::BLAST_FURNACE(), "blast_furnace"],
			[Blocks::FURNACE(), "furnace"],
			[Blocks::SMOKER(), "smoker"]
		] as [$block, $idSuffix]){
			$this->mapFlattenedId(FlattenedIdModel::create($block)
				->idComponents([...$commonProperties->furnaceIdPrefixes, $idSuffix])
				->properties([$commonProperties->horizontalFacingCardinal])
			);
		}

		foreach([
			[Blocks::REDSTONE_LAMP(), "redstone_lamp"],
			[Blocks::REDSTONE_ORE(), "redstone_ore"],
			[Blocks::DEEPSLATE_REDSTONE_ORE(), "deepslate_redstone_ore"]
		] as [$block, $idSuffix]){
			$this->mapFlattenedId(FlattenedIdModel::create($block)->idComponents(["minecraft:", $commonProperties->litIdInfix, $idSuffix]));
		}

		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::DAYLIGHT_SENSOR())
			->idComponents([
				"minecraft:daylight_detector",
				new BoolFromStringProperty("inverted", "", "_inverted", fn(DaylightSensor $b) => $b->isInverted(), fn(DaylightSensor $b, bool $v) => $b->setInverted($v))
			])
			->properties([$commonProperties->analogRedstoneSignal])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::REDSTONE_REPEATER())
			->idComponents([
				"minecraft:",
				new BoolFromStringProperty("powered", "un", "", fn(RedstoneRepeater $b) => $b->isPowered(), fn(RedstoneRepeater $b, bool $v) => $b->setPowered($v)),
				"powered_repeater"
			])
			->properties([
				$commonProperties->horizontalFacingCardinal,
				new IntProperty(StateNames::REPEATER_DELAY, 0, 3, fn(RedstoneRepeater $b) => $b->getDelay(), fn(RedstoneRepeater $b, int $v) => $b->setDelay($v), offset: 1),
			])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::REDSTONE_COMPARATOR())
			->idComponents([
				"minecraft:",
				//this property also appears in the state, so we ignore it in the ID
				//this is baked here purely to keep minecraft happy
				new BoolFromStringProperty("dummy_powered", "un", "", fn(RedstoneComparator $b) => $b->isPowered(), fn() => null),
				"powered_comparator"
			])
			->properties([
				$commonProperties->horizontalFacingCardinal,
				new BoolProperty(StateNames::OUTPUT_LIT_BIT, fn(RedstoneComparator $b) => $b->isPowered(), fn(RedstoneComparator $b, bool $v) => $b->setPowered($v)),
				new BoolProperty(StateNames::OUTPUT_SUBTRACT_BIT, fn(RedstoneComparator $b) => $b->isSubtractMode(), fn(RedstoneComparator $b, bool $v) => $b->setSubtractMode($v)),
			])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::REDSTONE_TORCH())
			->idComponents([
				"minecraft:",
				new BoolFromStringProperty("lit", "unlit_", "", fn(RedstoneTorch $b) => $b->isLit(), fn(RedstoneTorch $b, bool $v) => $b->setLit($v)),
				"redstone_torch"
			])
			->properties([$commonProperties->torchFacing])
		);
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::SPONGE())->idComponents([
			"minecraft:",
			new BoolFromStringProperty("wet", "", "wet_", fn(Sponge $b) => $b->isWet(), fn(Sponge $b, bool $v) => $b->setWet($v)),
			"sponge"
		]));
		$this->mapFlattenedId(FlattenedIdModel::create(Blocks::TNT())
			->idComponents([
				"minecraft:",
				new BoolFromStringProperty("underwater", "", "underwater_", fn(TNT $b) => $b->worksUnderwater(), fn(TNT $b, bool $v) => $b->setWorksUnderwater($v)),
				"tnt"
			])
			->properties([
				new BoolProperty(StateNames::EXPLODE_BIT, fn(TNT $b) => $b->isUnstable(), fn(TNT $b, bool $v) => $b->setUnstable($v)),
			])
		);
	}

	private function registerStoneLikeSlabMappings() : void{
		$this->mapSlab(Blocks::ANDESITE_SLAB(), "andesite");
		$this->mapSlab(Blocks::BLACKSTONE_SLAB(), "blackstone");
		$this->mapSlab(Blocks::BRICK_SLAB(), "brick");
		$this->mapSlab(Blocks::COBBLED_DEEPSLATE_SLAB(), "cobbled_deepslate");
		$this->mapSlab(Blocks::COBBLESTONE_SLAB(), "cobblestone");
		$this->mapSlab(Blocks::CUT_RED_SANDSTONE_SLAB(), "cut_red_sandstone");
		$this->mapSlab(Blocks::CUT_SANDSTONE_SLAB(), "cut_sandstone");
		$this->mapSlab(Blocks::DARK_PRISMARINE_SLAB(), "dark_prismarine");
		$this->mapSlab(Blocks::DEEPSLATE_BRICK_SLAB(), "deepslate_brick");
		$this->mapSlab(Blocks::DEEPSLATE_TILE_SLAB(), "deepslate_tile");
		$this->mapSlab(Blocks::DIORITE_SLAB(), "diorite");
		$this->mapSlab(Blocks::END_STONE_BRICK_SLAB(), "end_stone_brick");
		$this->mapSlab(Blocks::FAKE_WOODEN_SLAB(), "petrified_oak");
		$this->mapSlab(Blocks::GRANITE_SLAB(), "granite");
		$this->mapSlab(Blocks::MOSSY_COBBLESTONE_SLAB(), "mossy_cobblestone");
		$this->mapSlab(Blocks::MOSSY_STONE_BRICK_SLAB(), "mossy_stone_brick");
		$this->mapSlab(Blocks::MUD_BRICK_SLAB(), "mud_brick");
		$this->mapSlab(Blocks::NETHER_BRICK_SLAB(), "nether_brick");
		$this->mapSlab(Blocks::POLISHED_ANDESITE_SLAB(), "polished_andesite");
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_BRICK_SLAB(), "polished_blackstone_brick");
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_SLAB(), "polished_blackstone");
		$this->mapSlab(Blocks::POLISHED_DEEPSLATE_SLAB(), "polished_deepslate");
		$this->mapSlab(Blocks::POLISHED_DIORITE_SLAB(), "polished_diorite");
		$this->mapSlab(Blocks::POLISHED_GRANITE_SLAB(), "polished_granite");
		$this->mapSlab(Blocks::POLISHED_TUFF_SLAB(), "polished_tuff");
		$this->mapSlab(Blocks::PRISMARINE_BRICKS_SLAB(), "prismarine_brick");
		$this->mapSlab(Blocks::PRISMARINE_SLAB(), "prismarine");
		$this->mapSlab(Blocks::PURPUR_SLAB(), "purpur");
		$this->mapSlab(Blocks::QUARTZ_SLAB(), "quartz");
		$this->mapSlab(Blocks::RED_NETHER_BRICK_SLAB(), "red_nether_brick");
		$this->mapSlab(Blocks::RED_SANDSTONE_SLAB(), "red_sandstone");
		$this->mapSlab(Blocks::RESIN_BRICK_SLAB(), "resin_brick");
		$this->mapSlab(Blocks::SANDSTONE_SLAB(), "sandstone");
		$this->mapSlab(Blocks::SMOOTH_QUARTZ_SLAB(), "smooth_quartz");
		$this->mapSlab(Blocks::SMOOTH_RED_SANDSTONE_SLAB(), "smooth_red_sandstone");
		$this->mapSlab(Blocks::SMOOTH_SANDSTONE_SLAB(), "smooth_sandstone");
		$this->mapSlab(Blocks::SMOOTH_STONE_SLAB(), "smooth_stone");
		$this->mapSlab(Blocks::STONE_BRICK_SLAB(), "stone_brick");
		$this->mapSlab(Blocks::STONE_SLAB(), "normal_stone");
		$this->mapSlab(Blocks::TUFF_BRICK_SLAB(), "tuff_brick");
		$this->mapSlab(Blocks::TUFF_SLAB(), "tuff");
	}

	private function registerStoneLikeStairMappings() : void{
		$this->mapStairs(Blocks::ANDESITE_STAIRS(), Ids::ANDESITE_STAIRS);
		$this->mapStairs(Blocks::BLACKSTONE_STAIRS(), Ids::BLACKSTONE_STAIRS);
		$this->mapStairs(Blocks::BRICK_STAIRS(), Ids::BRICK_STAIRS);
		$this->mapStairs(Blocks::COBBLED_DEEPSLATE_STAIRS(), Ids::COBBLED_DEEPSLATE_STAIRS);
		$this->mapStairs(Blocks::COBBLESTONE_STAIRS(), Ids::STONE_STAIRS);
		$this->mapStairs(Blocks::DARK_PRISMARINE_STAIRS(), Ids::DARK_PRISMARINE_STAIRS);
		$this->mapStairs(Blocks::DEEPSLATE_BRICK_STAIRS(), Ids::DEEPSLATE_BRICK_STAIRS);
		$this->mapStairs(Blocks::DEEPSLATE_TILE_STAIRS(), Ids::DEEPSLATE_TILE_STAIRS);
		$this->mapStairs(Blocks::DIORITE_STAIRS(), Ids::DIORITE_STAIRS);
		$this->mapStairs(Blocks::END_STONE_BRICK_STAIRS(), Ids::END_BRICK_STAIRS);
		$this->mapStairs(Blocks::GRANITE_STAIRS(), Ids::GRANITE_STAIRS);
		$this->mapStairs(Blocks::MOSSY_COBBLESTONE_STAIRS(), Ids::MOSSY_COBBLESTONE_STAIRS);
		$this->mapStairs(Blocks::MOSSY_STONE_BRICK_STAIRS(), Ids::MOSSY_STONE_BRICK_STAIRS);
		$this->mapStairs(Blocks::MUD_BRICK_STAIRS(), Ids::MUD_BRICK_STAIRS);
		$this->mapStairs(Blocks::NETHER_BRICK_STAIRS(), Ids::NETHER_BRICK_STAIRS);
		$this->mapStairs(Blocks::POLISHED_ANDESITE_STAIRS(), Ids::POLISHED_ANDESITE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_BRICK_STAIRS(), Ids::POLISHED_BLACKSTONE_BRICK_STAIRS);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_STAIRS(), Ids::POLISHED_BLACKSTONE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_DEEPSLATE_STAIRS(), Ids::POLISHED_DEEPSLATE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_DIORITE_STAIRS(), Ids::POLISHED_DIORITE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_GRANITE_STAIRS(), Ids::POLISHED_GRANITE_STAIRS);
		$this->mapStairs(Blocks::POLISHED_TUFF_STAIRS(), Ids::POLISHED_TUFF_STAIRS);
		$this->mapStairs(Blocks::PRISMARINE_BRICKS_STAIRS(), Ids::PRISMARINE_BRICKS_STAIRS);
		$this->mapStairs(Blocks::PRISMARINE_STAIRS(), Ids::PRISMARINE_STAIRS);
		$this->mapStairs(Blocks::PURPUR_STAIRS(), Ids::PURPUR_STAIRS);
		$this->mapStairs(Blocks::QUARTZ_STAIRS(), Ids::QUARTZ_STAIRS);
		$this->mapStairs(Blocks::RED_NETHER_BRICK_STAIRS(), Ids::RED_NETHER_BRICK_STAIRS);
		$this->mapStairs(Blocks::RED_SANDSTONE_STAIRS(), Ids::RED_SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::RESIN_BRICK_STAIRS(), Ids::RESIN_BRICK_STAIRS);
		$this->mapStairs(Blocks::SANDSTONE_STAIRS(), Ids::SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::SMOOTH_QUARTZ_STAIRS(), Ids::SMOOTH_QUARTZ_STAIRS);
		$this->mapStairs(Blocks::SMOOTH_RED_SANDSTONE_STAIRS(), Ids::SMOOTH_RED_SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::SMOOTH_SANDSTONE_STAIRS(), Ids::SMOOTH_SANDSTONE_STAIRS);
		$this->mapStairs(Blocks::STONE_BRICK_STAIRS(), Ids::STONE_BRICK_STAIRS);
		$this->mapStairs(Blocks::STONE_STAIRS(), Ids::NORMAL_STONE_STAIRS);
		$this->mapStairs(Blocks::TUFF_BRICK_STAIRS(), Ids::TUFF_BRICK_STAIRS);
		$this->mapStairs(Blocks::TUFF_STAIRS(), Ids::TUFF_STAIRS);
	}

	private function registerStoneLikeWallMappings(CommonProperties $commonProperties) : void{
		foreach([
			Ids::ANDESITE_WALL => Blocks::ANDESITE_WALL(),
			Ids::BLACKSTONE_WALL => Blocks::BLACKSTONE_WALL(),
			Ids::BRICK_WALL => Blocks::BRICK_WALL(),
			Ids::COBBLED_DEEPSLATE_WALL => Blocks::COBBLED_DEEPSLATE_WALL(),
			Ids::COBBLESTONE_WALL => Blocks::COBBLESTONE_WALL(),
			Ids::DEEPSLATE_BRICK_WALL => Blocks::DEEPSLATE_BRICK_WALL(),
			Ids::DEEPSLATE_TILE_WALL => Blocks::DEEPSLATE_TILE_WALL(),
			Ids::DIORITE_WALL => Blocks::DIORITE_WALL(),
			Ids::END_STONE_BRICK_WALL => Blocks::END_STONE_BRICK_WALL(),
			Ids::GRANITE_WALL => Blocks::GRANITE_WALL(),
			Ids::MOSSY_COBBLESTONE_WALL => Blocks::MOSSY_COBBLESTONE_WALL(),
			Ids::MOSSY_STONE_BRICK_WALL => Blocks::MOSSY_STONE_BRICK_WALL(),
			Ids::MUD_BRICK_WALL => Blocks::MUD_BRICK_WALL(),
			Ids::NETHER_BRICK_WALL => Blocks::NETHER_BRICK_WALL(),
			Ids::POLISHED_BLACKSTONE_BRICK_WALL => Blocks::POLISHED_BLACKSTONE_BRICK_WALL(),
			Ids::POLISHED_BLACKSTONE_WALL => Blocks::POLISHED_BLACKSTONE_WALL(),
			Ids::POLISHED_DEEPSLATE_WALL => Blocks::POLISHED_DEEPSLATE_WALL(),
			Ids::POLISHED_TUFF_WALL => Blocks::POLISHED_TUFF_WALL(),
			Ids::PRISMARINE_WALL => Blocks::PRISMARINE_WALL(),
			Ids::RED_NETHER_BRICK_WALL => Blocks::RED_NETHER_BRICK_WALL(),
			Ids::RED_SANDSTONE_WALL => Blocks::RED_SANDSTONE_WALL(),
			Ids::RESIN_BRICK_WALL => Blocks::RESIN_BRICK_WALL(),
			Ids::SANDSTONE_WALL => Blocks::SANDSTONE_WALL(),
			Ids::STONE_BRICK_WALL => Blocks::STONE_BRICK_WALL(),
			Ids::TUFF_BRICK_WALL => Blocks::TUFF_BRICK_WALL(),
			Ids::TUFF_WALL => Blocks::TUFF_WALL()
		] as $id => $block){
			$this->mapModel(Model::create($block, $id)->properties($commonProperties->wallProperties));
		}
	}

	private function registerWoodMappings(CommonProperties $commonProperties) : void{
		//buttons
		foreach([
			[Blocks::ACACIA_BUTTON(), Ids::ACACIA_BUTTON],
			[Blocks::BIRCH_BUTTON(), Ids::BIRCH_BUTTON],
			[Blocks::CHERRY_BUTTON(), Ids::CHERRY_BUTTON],
			[Blocks::CRIMSON_BUTTON(), Ids::CRIMSON_BUTTON],
			[Blocks::DARK_OAK_BUTTON(), Ids::DARK_OAK_BUTTON],
			[Blocks::JUNGLE_BUTTON(), Ids::JUNGLE_BUTTON],
			[Blocks::MANGROVE_BUTTON(), Ids::MANGROVE_BUTTON],
			[Blocks::OAK_BUTTON(), Ids::WOODEN_BUTTON],
			[Blocks::PALE_OAK_BUTTON(), Ids::PALE_OAK_BUTTON],
			[Blocks::SPRUCE_BUTTON(), Ids::SPRUCE_BUTTON],
			[Blocks::WARPED_BUTTON(), Ids::WARPED_BUTTON]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties($commonProperties->buttonProperties));
		}

		//doors
		foreach([
			[Blocks::ACACIA_DOOR(), Ids::ACACIA_DOOR],
			[Blocks::BIRCH_DOOR(), Ids::BIRCH_DOOR],
			[Blocks::CHERRY_DOOR(), Ids::CHERRY_DOOR],
			[Blocks::CRIMSON_DOOR(), Ids::CRIMSON_DOOR],
			[Blocks::DARK_OAK_DOOR(), Ids::DARK_OAK_DOOR],
			[Blocks::JUNGLE_DOOR(), Ids::JUNGLE_DOOR],
			[Blocks::MANGROVE_DOOR(), Ids::MANGROVE_DOOR],
			[Blocks::OAK_DOOR(), Ids::WOODEN_DOOR],
			[Blocks::PALE_OAK_DOOR(), Ids::PALE_OAK_DOOR],
			[Blocks::SPRUCE_DOOR(), Ids::SPRUCE_DOOR],
			[Blocks::WARPED_DOOR(), Ids::WARPED_DOOR]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties($commonProperties->doorProperties));
		}

		//fences
		foreach([
			[Blocks::ACACIA_FENCE(), Ids::ACACIA_FENCE],
			[Blocks::BIRCH_FENCE(), Ids::BIRCH_FENCE],
			[Blocks::CHERRY_FENCE(), Ids::CHERRY_FENCE],
			[Blocks::DARK_OAK_FENCE(), Ids::DARK_OAK_FENCE],
			[Blocks::JUNGLE_FENCE(), Ids::JUNGLE_FENCE],
			[Blocks::MANGROVE_FENCE(), Ids::MANGROVE_FENCE],
			[Blocks::OAK_FENCE(), Ids::OAK_FENCE],
			[Blocks::PALE_OAK_FENCE(), Ids::PALE_OAK_FENCE],
			[Blocks::SPRUCE_FENCE(), Ids::SPRUCE_FENCE],
			[Blocks::CRIMSON_FENCE(), Ids::CRIMSON_FENCE],
			[Blocks::WARPED_FENCE(), Ids::WARPED_FENCE]
		] as [$block, $id]){
			$this->mapSimple($block, $id);
		}

		foreach([
			[Blocks::ACACIA_FENCE_GATE(), Ids::ACACIA_FENCE_GATE],
			[Blocks::BIRCH_FENCE_GATE(), Ids::BIRCH_FENCE_GATE],
			[Blocks::CHERRY_FENCE_GATE(), Ids::CHERRY_FENCE_GATE],
			[Blocks::DARK_OAK_FENCE_GATE(), Ids::DARK_OAK_FENCE_GATE],
			[Blocks::JUNGLE_FENCE_GATE(), Ids::JUNGLE_FENCE_GATE],
			[Blocks::MANGROVE_FENCE_GATE(), Ids::MANGROVE_FENCE_GATE],
			[Blocks::OAK_FENCE_GATE(), Ids::FENCE_GATE],
			[Blocks::PALE_OAK_FENCE_GATE(), Ids::PALE_OAK_FENCE_GATE],
			[Blocks::SPRUCE_FENCE_GATE(), Ids::SPRUCE_FENCE_GATE],
			[Blocks::CRIMSON_FENCE_GATE(), Ids::CRIMSON_FENCE_GATE],
			[Blocks::WARPED_FENCE_GATE(), Ids::WARPED_FENCE_GATE]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties($commonProperties->fenceGateProperties));
		}

		foreach([
			[Blocks::ACACIA_SIGN(), Ids::ACACIA_STANDING_SIGN],
			[Blocks::BIRCH_SIGN(), Ids::BIRCH_STANDING_SIGN],
			[Blocks::CHERRY_SIGN(), Ids::CHERRY_STANDING_SIGN],
			[Blocks::DARK_OAK_SIGN(), Ids::DARKOAK_STANDING_SIGN],
			[Blocks::JUNGLE_SIGN(), Ids::JUNGLE_STANDING_SIGN],
			[Blocks::MANGROVE_SIGN(), Ids::MANGROVE_STANDING_SIGN],
			[Blocks::OAK_SIGN(), Ids::STANDING_SIGN],
			[Blocks::PALE_OAK_SIGN(), Ids::PALE_OAK_STANDING_SIGN],
			[Blocks::SPRUCE_SIGN(), Ids::SPRUCE_STANDING_SIGN],
			[Blocks::CRIMSON_SIGN(), Ids::CRIMSON_STANDING_SIGN],
			[Blocks::WARPED_SIGN(), Ids::WARPED_STANDING_SIGN]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties([$commonProperties->floorSignLikeRotation]));
		}

		//logs
		foreach([
			[Blocks::ACACIA_LOG(), "acacia_log"],
			[Blocks::BIRCH_LOG(), "birch_log"],
			[Blocks::CHERRY_LOG(), "cherry_log"],
			[Blocks::DARK_OAK_LOG(), "dark_oak_log"],
			[Blocks::JUNGLE_LOG(), "jungle_log"],
			[Blocks::MANGROVE_LOG(), "mangrove_log"],
			[Blocks::OAK_LOG(), "oak_log"],
			[Blocks::PALE_OAK_LOG(), "pale_oak_log"],
			[Blocks::SPRUCE_LOG(), "spruce_log"],
			[Blocks::CRIMSON_STEM(), "crimson_stem"],
			[Blocks::WARPED_STEM(), "warped_stem"],

			//all-sided logs
			[Blocks::ACACIA_WOOD(), "acacia_wood"],
			[Blocks::BIRCH_WOOD(), "birch_wood"],
			[Blocks::CHERRY_WOOD(), "cherry_wood"],
			[Blocks::DARK_OAK_WOOD(), "dark_oak_wood"],
			[Blocks::JUNGLE_WOOD(), "jungle_wood"],
			[Blocks::MANGROVE_WOOD(), "mangrove_wood"],
			[Blocks::OAK_WOOD(), "oak_wood"],
			[Blocks::PALE_OAK_WOOD(), "pale_oak_wood"],
			[Blocks::SPRUCE_WOOD(), "spruce_wood"],
			[Blocks::CRIMSON_HYPHAE(), "crimson_hyphae"],
			[Blocks::WARPED_HYPHAE(), "warped_hyphae"]
		] as [$block, $idSuffix]){
			$this->mapFlattenedId(FlattenedIdModel::create($block)
				->idComponents([...$commonProperties->woodIdPrefixes, $idSuffix])
				->properties([$commonProperties->pillarAxis])
			);
		}

		//planks
		foreach([
			[Blocks::ACACIA_PLANKS(), Ids::ACACIA_PLANKS],
			[Blocks::BIRCH_PLANKS(), Ids::BIRCH_PLANKS],
			[Blocks::CHERRY_PLANKS(), Ids::CHERRY_PLANKS],
			[Blocks::DARK_OAK_PLANKS(), Ids::DARK_OAK_PLANKS],
			[Blocks::JUNGLE_PLANKS(), Ids::JUNGLE_PLANKS],
			[Blocks::MANGROVE_PLANKS(), Ids::MANGROVE_PLANKS],
			[Blocks::OAK_PLANKS(), Ids::OAK_PLANKS],
			[Blocks::PALE_OAK_PLANKS(), Ids::PALE_OAK_PLANKS],
			[Blocks::SPRUCE_PLANKS(), Ids::SPRUCE_PLANKS],
			[Blocks::CRIMSON_PLANKS(), Ids::CRIMSON_PLANKS],
			[Blocks::WARPED_PLANKS(), Ids::WARPED_PLANKS]
		] as [$block, $id]){
			$this->mapSimple($block, $id);
		}

		//pressure plates
		foreach([
			[Blocks::ACACIA_PRESSURE_PLATE(), Ids::ACACIA_PRESSURE_PLATE],
			[Blocks::BIRCH_PRESSURE_PLATE(), Ids::BIRCH_PRESSURE_PLATE],
			[Blocks::CHERRY_PRESSURE_PLATE(), Ids::CHERRY_PRESSURE_PLATE],
			[Blocks::DARK_OAK_PRESSURE_PLATE(), Ids::DARK_OAK_PRESSURE_PLATE],
			[Blocks::JUNGLE_PRESSURE_PLATE(), Ids::JUNGLE_PRESSURE_PLATE],
			[Blocks::MANGROVE_PRESSURE_PLATE(), Ids::MANGROVE_PRESSURE_PLATE],
			[Blocks::OAK_PRESSURE_PLATE(), Ids::WOODEN_PRESSURE_PLATE],
			[Blocks::PALE_OAK_PRESSURE_PLATE(), Ids::PALE_OAK_PRESSURE_PLATE],
			[Blocks::SPRUCE_PRESSURE_PLATE(), Ids::SPRUCE_PRESSURE_PLATE],
			[Blocks::CRIMSON_PRESSURE_PLATE(), Ids::CRIMSON_PRESSURE_PLATE],
			[Blocks::WARPED_PRESSURE_PLATE(), Ids::WARPED_PRESSURE_PLATE]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties($commonProperties->simplePressurePlateProperties));
		}

		//slabs
		foreach([
			[Blocks::ACACIA_SLAB(), "acacia"],
			[Blocks::BIRCH_SLAB(), "birch"],
			[Blocks::CHERRY_SLAB(), "cherry"],
			[Blocks::DARK_OAK_SLAB(), "dark_oak"],
			[Blocks::JUNGLE_SLAB(), "jungle"],
			[Blocks::MANGROVE_SLAB(), "mangrove"],
			[Blocks::OAK_SLAB(), "oak"],
			[Blocks::PALE_OAK_SLAB(), "pale_oak"],
			[Blocks::SPRUCE_SLAB(), "spruce"],
			[Blocks::CRIMSON_SLAB(), "crimson"],
			[Blocks::WARPED_SLAB(), "warped"]
		] as [$block, $type]){
			$this->mapSlab($block, $type);
		}

		//stairs
		foreach([
			[Blocks::ACACIA_STAIRS(), Ids::ACACIA_STAIRS],
			[Blocks::BIRCH_STAIRS(), Ids::BIRCH_STAIRS],
			[Blocks::CHERRY_STAIRS(), Ids::CHERRY_STAIRS],
			[Blocks::DARK_OAK_STAIRS(), Ids::DARK_OAK_STAIRS],
			[Blocks::JUNGLE_STAIRS(), Ids::JUNGLE_STAIRS],
			[Blocks::MANGROVE_STAIRS(), Ids::MANGROVE_STAIRS],
			[Blocks::OAK_STAIRS(), Ids::OAK_STAIRS],
			[Blocks::PALE_OAK_STAIRS(), Ids::PALE_OAK_STAIRS],
			[Blocks::SPRUCE_STAIRS(), Ids::SPRUCE_STAIRS],
			[Blocks::CRIMSON_STAIRS(), Ids::CRIMSON_STAIRS],
			[Blocks::WARPED_STAIRS(), Ids::WARPED_STAIRS]
		] as [$block, $id]){
			$this->mapStairs($block, $id);
		}

		//trapdoors
		foreach([
			[Blocks::ACACIA_TRAPDOOR(), Ids::ACACIA_TRAPDOOR],
			[Blocks::BIRCH_TRAPDOOR(), Ids::BIRCH_TRAPDOOR],
			[Blocks::CHERRY_TRAPDOOR(), Ids::CHERRY_TRAPDOOR],
			[Blocks::DARK_OAK_TRAPDOOR(), Ids::DARK_OAK_TRAPDOOR],
			[Blocks::JUNGLE_TRAPDOOR(), Ids::JUNGLE_TRAPDOOR],
			[Blocks::MANGROVE_TRAPDOOR(), Ids::MANGROVE_TRAPDOOR],
			[Blocks::OAK_TRAPDOOR(), Ids::TRAPDOOR],
			[Blocks::PALE_OAK_TRAPDOOR(), Ids::PALE_OAK_TRAPDOOR],
			[Blocks::SPRUCE_TRAPDOOR(), Ids::SPRUCE_TRAPDOOR],
			[Blocks::CRIMSON_TRAPDOOR(), Ids::CRIMSON_TRAPDOOR],
			[Blocks::WARPED_TRAPDOOR(), Ids::WARPED_TRAPDOOR]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties($commonProperties->trapdoorProperties));
		}

		//wall signs
		foreach([
			[Blocks::ACACIA_WALL_SIGN(), Ids::ACACIA_WALL_SIGN],
			[Blocks::BIRCH_WALL_SIGN(), Ids::BIRCH_WALL_SIGN],
			[Blocks::CHERRY_WALL_SIGN(), Ids::CHERRY_WALL_SIGN],
			[Blocks::DARK_OAK_WALL_SIGN(), Ids::DARKOAK_WALL_SIGN],
			[Blocks::JUNGLE_WALL_SIGN(), Ids::JUNGLE_WALL_SIGN],
			[Blocks::MANGROVE_WALL_SIGN(), Ids::MANGROVE_WALL_SIGN],
			[Blocks::OAK_WALL_SIGN(), Ids::WALL_SIGN],
			[Blocks::PALE_OAK_WALL_SIGN(), Ids::PALE_OAK_WALL_SIGN],
			[Blocks::SPRUCE_WALL_SIGN(), Ids::SPRUCE_WALL_SIGN],
			[Blocks::CRIMSON_WALL_SIGN(), Ids::CRIMSON_WALL_SIGN],
			[Blocks::WARPED_WALL_SIGN(), Ids::WARPED_WALL_SIGN]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties([$commonProperties->horizontalFacingClassic]));
		}
	}

	private function registerTorchMappings(CommonProperties $commonProperties) : void{
		foreach([
			[Blocks::BLUE_TORCH(), Ids::COLORED_TORCH_BLUE],
			[Blocks::GREEN_TORCH(), Ids::COLORED_TORCH_GREEN],
			[Blocks::PURPLE_TORCH(), Ids::COLORED_TORCH_PURPLE],
			[Blocks::RED_TORCH(), Ids::COLORED_TORCH_RED],
			[Blocks::SOUL_TORCH(), Ids::SOUL_TORCH],
			[Blocks::TORCH(), Ids::TORCH],
			[Blocks::UNDERWATER_TORCH(), Ids::UNDERWATER_TORCH]
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties([$commonProperties->torchFacing]));
		}
	}

	private function registerChemistryMappings(CommonProperties $commonProperties) : void{
		foreach([
			[Blocks::COMPOUND_CREATOR(), Ids::COMPOUND_CREATOR],
			[Blocks::ELEMENT_CONSTRUCTOR(), Ids::ELEMENT_CONSTRUCTOR],
			[Blocks::LAB_TABLE(), Ids::LAB_TABLE],
			[Blocks::MATERIAL_REDUCER(), Ids::MATERIAL_REDUCER],
		] as [$block, $id]){
			$this->mapModel(Model::create($block, $id)->properties([$commonProperties->horizontalFacingSWNEInverted]));
		}
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param Model<TBlock> $model
	 */
	private function mapModel(Model $model) : void{
		$id = $model->getId();
		$block = $model->getBlock();
		$propertyDescriptors = $model->getProperties();

		$this->deserializer?->map($id, static function(Reader $in) use ($block, $propertyDescriptors) : Block{
			$newBlock = clone $block;
			foreach($propertyDescriptors as $descriptor){
				$descriptor->deserialize($newBlock, $in);
			}
			return $newBlock;
		});
		$this->serializer?->map($block, static function(Block $block) use ($id, $propertyDescriptors) : Writer{
			$writer = new Writer($id);
			foreach($propertyDescriptors as $descriptor){
				$descriptor->serialize($block, $writer);
			}
			return $writer;
		});
	}

	private function register1to1CustomMappings(CommonProperties $commonProperties) : void{
		//TODO: some of these have repeated accessor refs, we might be able to deduplicate them
		//A
		$this->mapModel(Model::create(Blocks::ACTIVATOR_RAIL(), Ids::ACTIVATOR_RAIL)->properties([
			new BoolProperty(StateNames::RAIL_DATA_BIT, fn(ActivatorRail $b) => $b->isPowered(), fn(ActivatorRail $b, bool $v) => $b->setPowered($v)),
			new IntProperty(StateNames::RAIL_DIRECTION, 0, 5, fn(ActivatorRail $b) => $b->getShape(), fn(ActivatorRail $b, int $v) => $b->setShape($v))
		]));

		//B
		$this->mapModel(Model::create(Blocks::BAMBOO(), Ids::BAMBOO)->properties([
			new ValueFromStringProperty(StateNames::BAMBOO_LEAF_SIZE, ValueMappings::getInstance()->bambooLeafSize, fn(Bamboo $b) => $b->getLeafSize(), fn(Bamboo $b, int $v) => $b->setLeafSize($v)),
			new BoolProperty(StateNames::AGE_BIT, fn(Bamboo $b) => $b->isReady(), fn(Bamboo $b, bool $v) => $b->setReady($v)),
			new BoolFromStringProperty(StateNames::BAMBOO_STALK_THICKNESS, StringValues::BAMBOO_STALK_THICKNESS_THIN, StringValues::BAMBOO_STALK_THICKNESS_THICK, fn(Bamboo $b) => $b->isThick(), fn(Bamboo $b, bool $v) => $b->setThick($v))
		]));
		$this->mapModel(Model::create(Blocks::BAMBOO_SAPLING(), Ids::BAMBOO_SAPLING)->properties([
			new BoolProperty(StateNames::AGE_BIT, fn(BambooSapling $b) => $b->isReady(), fn(BambooSapling $b, bool $v) => $b->setReady($v))
		]));
		$this->mapModel(Model::create(Blocks::BANNER(), Ids::STANDING_BANNER)->properties([$commonProperties->floorSignLikeRotation]));
		$this->mapModel(Model::create(Blocks::BARREL(), Ids::BARREL)->properties([
			$commonProperties->anyFacingClassic,
			new BoolProperty(StateNames::OPEN_BIT, fn(Barrel $b) => $b->isOpen(), fn(Barrel $b, bool $v) => $b->setOpen($v))
		]));
		$this->mapModel(Model::create(Blocks::BASALT(), Ids::BASALT)->properties([$commonProperties->pillarAxis]));
		$this->mapModel(Model::create(Blocks::BED(), Ids::BED)->properties([
			new BoolProperty(StateNames::HEAD_PIECE_BIT, fn(Bed $b) => $b->isHeadPart(), fn(Bed $b, bool $v) => $b->setHead($v)),
			new BoolProperty(StateNames::OCCUPIED_BIT, fn(Bed $b) => $b->isOccupied(), fn(Bed $b, bool $v) => $b->setOccupied($v)),
			$commonProperties->horizontalFacingSWNE
		]));
		$this->mapModel(Model::create(Blocks::BEDROCK(), Ids::BEDROCK)->properties([
			new BoolProperty(StateNames::INFINIBURN_BIT, fn(Bedrock $b) => $b->burnsForever(), fn(Bedrock $b, bool $v) => $b->setBurnsForever($v))
		]));
		$this->mapModel(Model::create(Blocks::BELL(), Ids::BELL)->properties([
			BoolProperty::unused(StateNames::TOGGLE_BIT, false),
			new ValueFromStringProperty(StateNames::ATTACHMENT, ValueMappings::getInstance()->bellAttachmentType, fn(Bell $b) => $b->getAttachmentType(), fn(Bell $b, BellAttachmentType $v) => $b->setAttachmentType($v)),
			$commonProperties->horizontalFacingSWNE
		]));
		$this->mapModel(Model::create(Blocks::BONE_BLOCK(), Ids::BONE_BLOCK)->properties([
			IntProperty::unused(StateNames::DEPRECATED, 0),
			$commonProperties->pillarAxis
		]));

		$this->mapModel(Model::create(Blocks::BREWING_STAND(), Ids::BREWING_STAND)->properties(array_map(fn(BrewingStandSlot $slot) => new BoolProperty(match ($slot) {
			BrewingStandSlot::EAST => StateNames::BREWING_STAND_SLOT_A_BIT,
			BrewingStandSlot::SOUTHWEST => StateNames::BREWING_STAND_SLOT_B_BIT,
			BrewingStandSlot::NORTHWEST => StateNames::BREWING_STAND_SLOT_C_BIT
		}, fn(BrewingStand $b) => $b->hasSlot($slot), fn(BrewingStand $b, bool $v) => $b->setSlot($slot, $v)), BrewingStandSlot::cases())));

		//C
		$this->mapModel(Model::create(Blocks::CACTUS(), Ids::CACTUS)->properties([
			new IntProperty(StateNames::AGE, 0, 15, fn(Cactus $b) => $b->getAge(), fn(Cactus $b, int $v) => $b->setAge($v))
		]));
		$this->mapModel(Model::create(Blocks::CAKE(), Ids::CAKE)->properties([
			new IntProperty(StateNames::BITE_COUNTER, 0, 6, fn(Cake $b) => $b->getBites(), fn(Cake $b, int $v) => $b->setBites($v))
		]));
		$this->mapModel(Model::create(Blocks::CAMPFIRE(), Ids::CAMPFIRE)->properties($commonProperties->campfireProperties));
		$this->mapModel(Model::create(Blocks::CARVED_PUMPKIN(), Ids::CARVED_PUMPKIN)->properties([
			$commonProperties->horizontalFacingCardinal
		]));
		$this->mapModel(Model::create(Blocks::CHAIN(), Ids::CHAIN)->properties([$commonProperties->pillarAxis]));
		$this->mapModel(Model::create(Blocks::CHISELED_BOOKSHELF(), Ids::CHISELED_BOOKSHELF)->properties([
			$commonProperties->horizontalFacingSWNE,
			new OptionSetFromIntProperty(
				StateNames::BOOKS_STORED,
				EnumFromRawStateMap::int(ChiseledBookshelfSlot::class, fn(ChiseledBookshelfSlot $case) => match($case){
					//these are (currently) the same as the internal values, but it's best not to rely on those in case Mojang mess with the flags
					ChiseledBookshelfSlot::TOP_LEFT => 1 << 0,
					ChiseledBookshelfSlot::TOP_MIDDLE => 1 << 1,
					ChiseledBookshelfSlot::TOP_RIGHT => 1 << 2,
					ChiseledBookshelfSlot::BOTTOM_LEFT => 1 << 3,
					ChiseledBookshelfSlot::BOTTOM_MIDDLE => 1 << 4,
					ChiseledBookshelfSlot::BOTTOM_RIGHT => 1 << 5
				}),
				fn(ChiseledBookshelf $b) => $b->getSlots(),
				fn(ChiseledBookshelf $b, array $v) => $b->setSlots($v)
			)
		]));
		$this->mapModel(Model::create(Blocks::CHISELED_QUARTZ(), Ids::CHISELED_QUARTZ_BLOCK)->properties([$commonProperties->pillarAxis]));
		$this->mapModel(Model::create(Blocks::CHEST(), Ids::CHEST)->properties([$commonProperties->horizontalFacingCardinal]));
		$this->mapModel(Model::create(Blocks::CHORUS_FLOWER(), Ids::CHORUS_FLOWER)->properties([
			new IntProperty(StateNames::AGE, ChorusFlower::MIN_AGE, ChorusFlower::MAX_AGE, fn(ChorusFlower $b) => $b->getAge(), fn(ChorusFlower $b, int $v) => $b->setAge($v))
		]));
		$this->mapModel(Model::create(Blocks::COCOA_POD(), Ids::COCOA)->properties([
			new IntProperty(StateNames::AGE, 0, 2, fn(CocoaBlock $b) => $b->getAge(), fn(CocoaBlock $b, int $v) => $b->setAge($v)),
			$commonProperties->horizontalFacingSWNEInverted
		]));

		//D
		$this->mapModel(Model::create(Blocks::DEEPSLATE(), Ids::DEEPSLATE)->properties([$commonProperties->pillarAxis]));
		$this->mapModel(Model::create(Blocks::DETECTOR_RAIL(), Ids::DETECTOR_RAIL)->properties([
			new BoolProperty(StateNames::RAIL_DATA_BIT, fn(DetectorRail $b) => $b->isActivated(), fn(DetectorRail $b, bool $v) => $b->setActivated($v)),
			new IntProperty(StateNames::RAIL_DIRECTION, 0, 5, fn(StraightOnlyRail $b) => $b->getShape(), fn(StraightOnlyRail $b, int $v) => $b->setShape($v)) //TODO: shared with ActivatorRail
		]));

		//E
		$this->mapModel(Model::create(Blocks::ENDER_CHEST(), Ids::ENDER_CHEST)->properties([$commonProperties->horizontalFacingCardinal]));
		$this->mapModel(Model::create(Blocks::END_PORTAL_FRAME(), Ids::END_PORTAL_FRAME)->properties([
			new BoolProperty(StateNames::END_PORTAL_EYE_BIT, fn(EndPortalFrame $b) => $b->hasEye(), fn(EndPortalFrame $b, bool $v) => $b->setEye($v)),
			$commonProperties->horizontalFacingCardinal
		]));
		$this->mapModel(Model::create(Blocks::END_ROD(), Ids::END_ROD)->properties([
			new ValueFromIntProperty(StateNames::FACING_DIRECTION, ValueMappings::getInstance()->facingEndRod, fn(EndRod $b) => $b->getFacing(), fn(EndRod $b, int $v) => $b->setFacing($v)),
		]));

		//F
		$this->mapModel(Model::create(Blocks::FARMLAND(), Ids::FARMLAND)->properties([
			new IntProperty(StateNames::MOISTURIZED_AMOUNT, 0, 7, fn(Farmland $b) => $b->getWetness(), fn(Farmland $b, int $v) => $b->setWetness($v))
		]));
		$this->mapModel(Model::create(Blocks::FIRE(), Ids::FIRE)->properties([
			new IntProperty(StateNames::AGE, 0, 15, fn(Fire $b) => $b->getAge(), fn(Fire $b, int $v) => $b->setAge($v))
		]));
		$this->mapModel(Model::create(Blocks::FLOWER_POT(), Ids::FLOWER_POT)->properties([
			BoolProperty::unused(StateNames::UPDATE_BIT, false)
		]));
		$this->mapModel(Model::create(Blocks::FROSTED_ICE(), Ids::FROSTED_ICE)->properties([
			new IntProperty(StateNames::AGE, 0, 3, fn(FrostedIce $b) => $b->getAge(), fn(FrostedIce $b, int $v) => $b->setAge($v))
		]));

		//G
		$this->mapModel(Model::create(Blocks::GLOWING_ITEM_FRAME(), Ids::GLOW_FRAME)->properties($commonProperties->itemFrameProperties));

		//H
		$this->mapModel(Model::create(Blocks::HAY_BALE(), Ids::HAY_BLOCK)->properties([
			IntProperty::unused(StateNames::DEPRECATED, 0),
			$commonProperties->pillarAxis
		]));
		$this->mapModel(Model::create(Blocks::HOPPER(), Ids::HOPPER)->properties([
			//kinda weird this doesn't use powered_bit?
			new BoolProperty(StateNames::TOGGLE_BIT, fn(PoweredByRedstone $b) => $b->isPowered(), fn(PoweredByRedstone $b, bool $v) => $b->setPowered($v)),
			new ValueFromIntProperty(StateNames::FACING_DIRECTION, ValueMappings::getInstance()->facingExceptUp, fn(Hopper $b) => $b->getFacing(), fn(Hopper $b, int $v) => $b->setFacing($v)),
		]));

		//I
		$this->mapModel(Model::create(Blocks::IRON_DOOR(), Ids::IRON_DOOR)->properties($commonProperties->doorProperties));
		$this->mapModel(Model::create(Blocks::IRON_TRAPDOOR(), Ids::IRON_TRAPDOOR)->properties($commonProperties->trapdoorProperties));
		$this->mapModel(Model::create(Blocks::ITEM_FRAME(), Ids::FRAME)->properties($commonProperties->itemFrameProperties));

		//L
		$this->mapModel(Model::create(Blocks::LADDER(), Ids::LADDER)->properties([$commonProperties->horizontalFacingClassic]));
		$this->mapModel(Model::create(Blocks::LANTERN(), Ids::LANTERN)->properties([
			new BoolProperty(StateNames::HANGING, fn(Lantern $b) => $b->isHanging(), fn(Lantern $b, bool $v) => $b->setHanging($v))
		]));
		$this->mapModel(Model::create(Blocks::LECTERN(), Ids::LECTERN)->properties([
			new BoolProperty(StateNames::POWERED_BIT, fn(Lectern $b) => $b->isProducingSignal(), fn(Lectern $b, bool $v) => $b->setProducingSignal($v)),
			$commonProperties->horizontalFacingCardinal,
		]));
		$this->mapModel(Model::create(Blocks::LEVER(), Ids::LEVER)->properties([
			new ValueFromStringProperty(StateNames::LEVER_DIRECTION, ValueMappings::getInstance()->leverFacing, fn(Lever $b) => $b->getFacing(), fn(Lever $b, LeverFacing $v) => $b->setFacing($v)),
			new BoolProperty(StateNames::OPEN_BIT, fn(Lever $b) => $b->isActivated(), fn(Lever $b, bool $v) => $b->setActivated($v)),
		]));
		$this->mapModel(Model::create(Blocks::LIGHTNING_ROD(), Ids::LIGHTNING_ROD)->properties([$commonProperties->anyFacingClassic]));
		$this->mapModel(Model::create(Blocks::LIT_PUMPKIN(), Ids::LIT_PUMPKIN)->properties([$commonProperties->horizontalFacingCardinal]));
		$this->mapModel(Model::create(Blocks::LOOM(), Ids::LOOM)->properties([$commonProperties->horizontalFacingSWNE]));

		//M
		$this->mapModel(Model::create(Blocks::MUDDY_MANGROVE_ROOTS(), Ids::MUDDY_MANGROVE_ROOTS)->properties([$commonProperties->pillarAxis]));
		$this->mapModel(Model::create(Blocks::NETHER_WART(), Ids::NETHER_WART)->properties([
			new IntProperty(StateNames::AGE, 0, 3, fn(NetherWartPlant $b) => $b->getAge(), fn(NetherWartPlant $b, int $v) => $b->setAge($v))
		]));
		$this->mapModel(Model::create(Blocks::NETHER_PORTAL(), Ids::PORTAL)->properties([
			new ValueFromStringProperty(StateNames::PORTAL_AXIS, ValueMappings::getInstance()->portalAxis, fn(NetherPortal $b) => $b->getAxis(), fn(NetherPortal $b, int $v) => $b->setAxis($v))
		]));

		//P
		$this->mapModel(Model::create(Blocks::PINK_PETALS(), Ids::PINK_PETALS)->properties([
			//Pink petals only uses 0-3, but GROWTH state can go up to 7
			new IntProperty(StateNames::GROWTH, 0, 7, fn(PinkPetals $b) => $b->getCount(), fn(PinkPetals $b, int $v) => $b->setCount(min($v, PinkPetals::MAX_COUNT)), offset: 1),
			$commonProperties->horizontalFacingCardinal
		]));
		$this->mapModel(Model::create(Blocks::POWERED_RAIL(), Ids::GOLDEN_RAIL)->properties([
			new BoolProperty(StateNames::RAIL_DATA_BIT, fn(PoweredRail $b) => $b->isPowered(), fn(PoweredRail $b, bool $v) => $b->setPowered($v)), //TODO: shared with ActivatorRail
			new IntProperty(StateNames::RAIL_DIRECTION, 0, 5, fn(StraightOnlyRail $b) => $b->getShape(), fn(StraightOnlyRail $b, int $v) => $b->setShape($v)) //TODO: shared with ActivatorRail
		]));
		$this->mapModel(Model::create(Blocks::PITCHER_PLANT(), Ids::PITCHER_PLANT)->properties([
			new BoolProperty(StateNames::UPPER_BLOCK_BIT, fn(DoublePlant $b) => $b->isTop(), fn(DoublePlant $b, bool $v) => $b->setTop($v)), //TODO: don't we have helpers for this?
		]));
		$this->mapModel(Model::create(Blocks::POLISHED_BASALT(), Ids::POLISHED_BASALT)->properties([$commonProperties->pillarAxis]));
		$this->mapModel(Model::create(Blocks::POLISHED_BLACKSTONE_BUTTON(), Ids::POLISHED_BLACKSTONE_BUTTON)->properties($commonProperties->buttonProperties));
		$this->mapModel(Model::create(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE)->properties($commonProperties->simplePressurePlateProperties));
		$this->mapModel(Model::create(Blocks::PUMPKIN(), Ids::PUMPKIN)->properties([
			//not used, has no visible effect
			$commonProperties->dummyCardinalDirection
		]));
		$this->mapModel(Model::create(Blocks::PURPUR(), Ids::PURPUR_BLOCK)->properties([
			$commonProperties->dummyPillarAxis
		]));
		$this->mapModel(Model::create(Blocks::PURPUR_PILLAR(), Ids::PURPUR_PILLAR)->properties([$commonProperties->pillarAxis]));

		//Q
		$this->mapModel(Model::create(Blocks::QUARTZ(), Ids::QUARTZ_BLOCK)->properties([
			$commonProperties->dummyPillarAxis
		]));
		$this->mapModel(Model::create(Blocks::QUARTZ_PILLAR(), Ids::QUARTZ_PILLAR)->properties([$commonProperties->pillarAxis]));

		//R
		$this->mapModel(Model::create(Blocks::RAIL(), Ids::RAIL)->properties([
			new IntProperty(StateNames::RAIL_DIRECTION, 0, 9, fn(Rail $b) => $b->getShape(), fn(Rail $b, int $v) => $b->setShape($v))
		]));
		$this->mapModel(Model::create(Blocks::REDSTONE_WIRE(), Ids::REDSTONE_WIRE)->properties([$commonProperties->analogRedstoneSignal]));
		$this->mapModel(Model::create(Blocks::RESPAWN_ANCHOR(), Ids::RESPAWN_ANCHOR)->properties([
			new IntProperty(StateNames::RESPAWN_ANCHOR_CHARGE, 0, 4, fn(RespawnAnchor $b) => $b->getCharges(), fn(RespawnAnchor $b, int $v) => $b->setCharges($v))
		]));

		//S
		$this->mapModel(Model::create(Blocks::SEA_PICKLE(), Ids::SEA_PICKLE)->properties([
			new IntProperty(StateNames::CLUSTER_COUNT, 0, 3, fn(SeaPickle $b) => $b->getCount(), fn(SeaPickle $b, int $v) => $b->setCount($v), offset: 1),
			new BoolProperty(StateNames::DEAD_BIT, fn(SeaPickle $b) => $b->isUnderwater(), fn(SeaPickle $b, bool $v) => $b->setUnderwater($v), inverted: true)
		]));
		$this->mapModel(Model::create(Blocks::SMALL_DRIPLEAF(), Ids::SMALL_DRIPLEAF_BLOCK)->properties([
			new BoolProperty(StateNames::UPPER_BLOCK_BIT, fn(SmallDripleaf $b) => $b->isTop(), fn(SmallDripleaf $b, bool $v) => $b->setTop($v)),
			$commonProperties->horizontalFacingCardinal
		]));
		$this->mapModel(Model::create(Blocks::SMOOTH_QUARTZ(), Ids::SMOOTH_QUARTZ)->properties([
			$commonProperties->dummyPillarAxis
		]));
		$this->mapModel(Model::create(Blocks::SNOW_LAYER(), Ids::SNOW_LAYER)->properties([
			new DummyProperty(StateNames::COVERED_BIT, false),
			new IntProperty(StateNames::HEIGHT, 0, 7, fn(SnowLayer $b) => $b->getLayers(), fn(SnowLayer $b, int $v) => $b->setLayers($v), offset: 1)
		]));
		$this->mapModel(Model::create(Blocks::SOUL_CAMPFIRE(), Ids::SOUL_CAMPFIRE)->properties($commonProperties->campfireProperties));
		$this->mapModel(Model::create(Blocks::SOUL_FIRE(), Ids::SOUL_FIRE)->properties([
			new DummyProperty(StateNames::AGE, 0) //this is useless for soul fire, since it doesn't have the logic associated
		]));
		$this->mapModel(Model::create(Blocks::SOUL_LANTERN(), Ids::SOUL_LANTERN)->properties([
			new BoolProperty(StateNames::HANGING, fn(Lantern $b) => $b->isHanging(), fn(Lantern $b, bool $v) => $b->setHanging($v)) //TODO: repeated
		]));
		$this->mapModel(Model::create(Blocks::STONE_BUTTON(), Ids::STONE_BUTTON)->properties($commonProperties->buttonProperties));
		$this->mapModel(Model::create(Blocks::STONE_PRESSURE_PLATE(), Ids::STONE_PRESSURE_PLATE)->properties($commonProperties->simplePressurePlateProperties));
		$this->mapModel(Model::create(Blocks::STONECUTTER(), Ids::STONECUTTER_BLOCK)->properties([
			$commonProperties->horizontalFacingCardinal
		]));
		$this->mapModel(Model::create(Blocks::SUGARCANE(), Ids::REEDS)->properties([
			new IntProperty(StateNames::AGE, 0, 15, fn(Sugarcane $b) => $b->getAge(), fn(Sugarcane $b, int $v) => $b->setAge($v))
		]));

		//T
		$this->mapModel(Model::create(Blocks::TRAPPED_CHEST(), Ids::TRAPPED_CHEST)->properties([
			$commonProperties->horizontalFacingCardinal
		]));
		$this->mapModel(Model::create(Blocks::TRIPWIRE(), Ids::TRIP_WIRE)->properties([
			new BoolProperty(StateNames::ATTACHED_BIT, fn(Tripwire $b) => $b->isConnected(), fn(Tripwire $b, bool $v) => $b->setConnected($v)),
			new BoolProperty(StateNames::DISARMED_BIT, fn(Tripwire $b) => $b->isDisarmed(), fn(Tripwire $b, bool $v) => $b->setDisarmed($v)),
			new BoolProperty(StateNames::SUSPENDED_BIT, fn(Tripwire $b) => $b->isSuspended(), fn(Tripwire $b, bool $v) => $b->setSuspended($v)),
			new BoolProperty(StateNames::POWERED_BIT, fn(Tripwire $b) => $b->isTriggered(), fn(Tripwire $b, bool $v) => $b->setTriggered($v)),
		]));
		$this->mapModel(Model::create(Blocks::TRIPWIRE_HOOK(), Ids::TRIPWIRE_HOOK)->properties([
			new BoolProperty(StateNames::ATTACHED_BIT, fn(TripwireHook $b) => $b->isConnected(), fn(TripwireHook $b, bool $v) => $b->setConnected($v)),
			new BoolProperty(StateNames::POWERED_BIT, fn(TripwireHook $b) => $b->isPowered(), fn(TripwireHook $b, bool $v) => $b->setPowered($v)),
			$commonProperties->horizontalFacingSWNE
		]));

		$this->mapModel(Model::create(Blocks::TWISTING_VINES(), Ids::TWISTING_VINES)->properties([
			new IntProperty(StateNames::TWISTING_VINES_AGE, 0, 25, fn(NetherVines $b) => $b->getAge(), fn(NetherVines $b, int $v) => $b->setAge($v))
		]));

		//W
		$this->mapModel(Model::create(Blocks::WALL_BANNER(), Ids::WALL_BANNER)->properties([$commonProperties->horizontalFacingClassic]));
		$this->mapModel(Model::create(Blocks::WEEPING_VINES(), Ids::WEEPING_VINES)->properties([
			new IntProperty(StateNames::WEEPING_VINES_AGE, 0, 25, fn(NetherVines $b) => $b->getAge(), fn(NetherVines $b, int $v) => $b->setAge($v))
		]));
		$this->mapModel(Model::create(Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY(), Ids::HEAVY_WEIGHTED_PRESSURE_PLATE)->properties([$commonProperties->analogRedstoneSignal]));
		$this->mapModel(Model::create(Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), Ids::LIGHT_WEIGHTED_PRESSURE_PLATE)->properties([$commonProperties->analogRedstoneSignal]));
	}

	/**
	 * All mappings that still use the split form of serializer/deserializer registration
	 * This is typically only used by blocks with one ID but multiple PM types (split by property)
	 * These currently can't be registered in a unified way, and due to their small number it may not be worth the
	 * effort to implement a unified way to deal with them
	 */
	private function registerSplitMappings() : void{
		//big dripleaf - split into head / stem variants, as stems don't have tilt or leaf state
		if($this->serializer !== null){
			$this->serializer->map(Blocks::BIG_DRIPLEAF_HEAD(), function(BigDripleafHead $block) : Writer{
				return Writer::create(Ids::BIG_DRIPLEAF)
					->writeCardinalHorizontalFacing($block->getFacing())
					->writeUnitEnum(StateNames::BIG_DRIPLEAF_TILT, ValueMappings::getInstance()->dripleafState, $block->getLeafState())
					->writeBool(StateNames::BIG_DRIPLEAF_HEAD, true);
			});
			$this->serializer->map(Blocks::BIG_DRIPLEAF_STEM(), function(BigDripleafStem $block) : Writer{
				return Writer::create(Ids::BIG_DRIPLEAF)
					->writeCardinalHorizontalFacing($block->getFacing())
					->writeString(StateNames::BIG_DRIPLEAF_TILT, StringValues::BIG_DRIPLEAF_TILT_NONE)
					->writeBool(StateNames::BIG_DRIPLEAF_HEAD, false);
			});
		}
		$this->deserializer?->map(Ids::BIG_DRIPLEAF, function(Reader $in) : Block{
			if($in->readBool(StateNames::BIG_DRIPLEAF_HEAD)){
				return Blocks::BIG_DRIPLEAF_HEAD()
					->setFacing($in->readCardinalHorizontalFacing())
					->setLeafState($in->readUnitEnum(StateNames::BIG_DRIPLEAF_TILT, ValueMappings::getInstance()->dripleafState));
			}else{
				$in->ignored(StateNames::BIG_DRIPLEAF_TILT);
				return Blocks::BIG_DRIPLEAF_STEM()->setFacing($in->readCardinalHorizontalFacing());
			}
		});

		//cauldrons - split into liquid variants, as each have different behaviour
		if($this->serializer !== null){
			$this->serializer->map(Blocks::CAULDRON(), Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, 0));
			$this->serializer->map(Blocks::LAVA_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_LAVA, $b->getFillLevel()));
			//potion cauldrons store their real information in the block actor data
			$this->serializer->map(Blocks::POTION_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
			$this->serializer->map(Blocks::WATER_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
		}
		$this->deserializer?->map(Ids::CAULDRON, function(Reader $in) : Block{
			$level = $in->readBoundedInt(StateNames::FILL_LEVEL, 0, 6);
			if($level === 0){
				$in->ignored(StateNames::CAULDRON_LIQUID);
				return Blocks::CAULDRON();
			}

			return (match ($liquid = $in->readString(StateNames::CAULDRON_LIQUID)) {
				StringValues::CAULDRON_LIQUID_WATER => Blocks::WATER_CAULDRON(),
				StringValues::CAULDRON_LIQUID_LAVA => Blocks::LAVA_CAULDRON(),
				StringValues::CAULDRON_LIQUID_POWDER_SNOW => throw new UnsupportedBlockStateException("Powder snow is not supported yet"),
				default => throw $in->badValueException(StateNames::CAULDRON_LIQUID, $liquid)
			})->setFillLevel($level);
		});

		//mushroom stems, split for consistency with all-sided logs vs normal logs
		if($this->serializer !== null){
			$this->serializer->map(Blocks::ALL_SIDED_MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM));
			$this->serializer->map(Blocks::MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_STEM));
		}
		$this->deserializer?->map(Ids::MUSHROOM_STEM, fn(Reader $in) => match ($in->readBoundedInt(StateNames::HUGE_MUSHROOM_BITS, 0, 15)) {
			BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM => Blocks::ALL_SIDED_MUSHROOM_STEM(),
			BlockLegacyMetadata::MUSHROOM_BLOCK_STEM => Blocks::MUSHROOM_STEM(),
			default => throw new BlockStateDeserializeException("This state does not exist"),
		});

		//pitcher crop, split into single and double variants as double has different properties and behaviour
		//this will probably be the most annoying to unify
		if($this->serializer !== null){
			$this->serializer->map(Blocks::PITCHER_CROP(), function(PitcherCrop $block) : Writer{
				return Writer::create(Ids::PITCHER_CROP)
					->writeInt(StateNames::GROWTH, $block->getAge())
					->writeBool(StateNames::UPPER_BLOCK_BIT, false);
			});
			$this->serializer->map(Blocks::DOUBLE_PITCHER_CROP(), function(DoublePitcherCrop $block) : Writer{
				return Writer::create(Ids::PITCHER_CROP)
					->writeInt(StateNames::GROWTH, $block->getAge() + 1 + PitcherCrop::MAX_AGE)
					->writeBool(StateNames::UPPER_BLOCK_BIT, $block->isTop());
			});
		}
		$this->deserializer?->map(Ids::PITCHER_CROP, function(Reader $in) : Block{
			$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
			$top = $in->readBool(StateNames::UPPER_BLOCK_BIT);
			if($growth <= PitcherCrop::MAX_AGE){
				//top pitcher crop with age 0-2 is an invalid state
				//only the bottom half should exist in this case
				return $top ? Blocks::AIR() : Blocks::PITCHER_CROP()->setAge($growth);
			}
			return Blocks::DOUBLE_PITCHER_CROP()
				->setAge(min($growth - PitcherCrop::MAX_AGE - 1, DoublePitcherCrop::MAX_AGE))
				->setTop($top);
		});
	}
}
