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

namespace pocketmine\tools\generate_bedrock_data_from_packets;

use pocketmine\crafting\json\FurnaceRecipeData;
use pocketmine\crafting\json\ItemStackData;
use pocketmine\crafting\json\PotionContainerChangeRecipeData;
use pocketmine\crafting\json\PotionTypeRecipeData;
use pocketmine\crafting\json\RecipeIngredientData;
use pocketmine\crafting\json\ShapedRecipeData;
use pocketmine\crafting\json\ShapelessRecipeData;
use pocketmine\crafting\json\SmithingTransformRecipeData;
use pocketmine\crafting\json\SmithingTrimRecipeData;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\convert\BlockStateDictionary;
use pocketmine\network\mcpe\convert\BlockTranslator;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use pocketmine\network\mcpe\protocol\BiomeDefinitionListPacket;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackExtraData;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackExtraDataShield;
use pocketmine\network\mcpe\protocol\types\recipe\ComplexAliasItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\MolangItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\MultiRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\network\mcpe\protocol\types\recipe\ShapedRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\ShapelessRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\SmithingTransformRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\SmithingTrimRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\StringIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\TagItemDescriptor;
use pocketmine\network\PacketHandlingException;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Path;
use function array_map;
use function array_values;
use function asort;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function chr;
use function count;
use function dirname;
use function explode;
use function file;
use function file_put_contents;
use function fwrite;
use function get_class;
use function implode;
use function is_array;
use function is_object;
use function json_encode;
use function ksort;
use function mkdir;
use function ord;
use function strlen;
use const FILE_IGNORE_NEW_LINES;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const PHP_BINARY;
use const PHP_EOL;
use const SORT_NUMERIC;
use const SORT_STRING;
use const STDERR;

require dirname(__DIR__) . '/vendor/autoload.php';

class ParserPacketHandler extends PacketHandler{

	public ?ItemTypeDictionary $itemTypeDictionary = null;
	private BlockTranslator $blockTranslator;
	private BlockItemIdMap $blockItemIdMap;

	public function __construct(private string $bedrockDataPath){
		$this->blockTranslator = new BlockTranslator(
			BlockStateDictionary::loadFromString(
				Filesystem::fileGetContents(Path::join($this->bedrockDataPath, "canonical_block_states.nbt")),
				Filesystem::fileGetContents(Path::join($this->bedrockDataPath, "block_state_meta_map.json")),
			),
			GlobalBlockStateHandlers::getSerializer()
		);
		$this->blockItemIdMap = BlockItemIdMap::getInstance();
	}

	private static function blockStatePropertiesToString(BlockStateData $blockStateData) : string{
		$statePropertiesTag = CompoundTag::create();
		foreach(Utils::stringifyKeys($blockStateData->getStates()) as $name => $value){
			$statePropertiesTag->setTag($name, $value);
		}
		return base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($statePropertiesTag)));
	}

	private function itemStackToJson(ItemStack $itemStack) : ItemStackData{
		if($itemStack->getId() === 0){
			throw new InvalidArgumentException("Cannot serialize a null itemstack");
		}
		if($this->itemTypeDictionary === null){
			throw new PacketHandlingException("Can't process item yet; haven't received item type dictionary");
		}
		$itemStringId = $this->itemTypeDictionary->fromIntId($itemStack->getId());
		$data = new ItemStackData($itemStringId);

		if($itemStack->getCount() !== 1){
			$data->count = $itemStack->getCount();
		}

		$meta = $itemStack->getMeta();
		if($meta === 32767){
			$meta = 0; //kick wildcard magic bullshit
		}
		if($this->blockItemIdMap->lookupBlockId($itemStringId) !== null){
			if($meta !== 0){
				throw new PacketHandlingException("Unexpected non-zero blockitem meta");
			}
			$blockState = $this->blockTranslator->getBlockStateDictionary()->generateDataFromStateId($itemStack->getBlockRuntimeId()) ?? null;
			if($blockState === null){
				throw new PacketHandlingException("Unmapped blockstate ID " . $itemStack->getBlockRuntimeId());
			}

			$stateProperties = $blockState->getStates();
			if(count($stateProperties) > 0){
				$data->block_states = self::blockStatePropertiesToString($blockState);
			}
		}elseif($itemStack->getBlockRuntimeId() !== ItemTranslator::NO_BLOCK_RUNTIME_ID){
			throw new PacketHandlingException("Non-blockitems should have a zero block runtime ID (" . $itemStack->getBlockRuntimeId() . " on " . $itemStringId . ")");
		}elseif($meta !== 0){
			$data->meta = $meta;
		}

		$rawExtraData = $itemStack->getRawExtraData();
		if($rawExtraData !== ""){
			$decoder = PacketSerializer::decoder($rawExtraData, 0);
			$extraData = $itemStringId === ItemTypeNames::SHIELD ? ItemStackExtraDataShield::read($decoder) : ItemStackExtraData::read($decoder);
			$nbt = $extraData->getNbt();
			if($nbt !== null && count($nbt) > 0){
				$data->nbt = base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($nbt)));
			}

			if(count($extraData->getCanPlaceOn()) > 0){
				$data->can_place_on = $extraData->getCanPlaceOn();
			}
			if(count($extraData->getCanDestroy()) > 0){
				$data->can_destroy = $extraData->getCanDestroy();
			}
		}

		return $data;
	}

	/**
	 * @return mixed[]
	 */
	private static function objectToOrderedArray(object $object) : array{
		$result = (array) ($object instanceof \JsonSerializable ? $object->jsonSerialize() : $object);
		ksort($result, SORT_STRING);

		foreach(Utils::promoteKeys($result) as $property => $value){
			if(is_object($value)){
				$result[$property] = self::objectToOrderedArray($value);
			}elseif(is_array($value)){
				$array = [];
				foreach(Utils::promoteKeys($value) as $k => $v){
					if(is_object($v)){
						$array[$k] = self::objectToOrderedArray($v);
					}else{
						$array[$k] = $v;
					}
				}

				$result[$property] = $array;
			}
		}

		return $result;
	}

	private static function sort(mixed $object) : mixed{
		if(is_object($object)){
			return self::objectToOrderedArray($object);
		}
		if(is_array($object)){
			$result = [];
			foreach(Utils::promoteKeys($object) as $k => $v){
				$result[$k] = self::sort($v);
			}
			return $result;
		}

		return $object;
	}

	public function handleStartGame(StartGamePacket $packet) : bool{
		$this->itemTypeDictionary = new ItemTypeDictionary($packet->itemTable);

		echo "updating legacy item ID mapping table\n";
		$table = [];
		foreach($packet->itemTable as $entry){
			$table[$entry->getStringId()] = [
				"runtime_id" => $entry->getNumericId(),
				"component_based" => $entry->isComponentBased()
			];
		}
		ksort($table, SORT_STRING);
		file_put_contents($this->bedrockDataPath . '/required_item_list.json', json_encode($table, JSON_PRETTY_PRINT) . "\n");

		foreach(Utils::promoteKeys($packet->levelSettings->experiments->getExperiments()) as $name => $experiment){
			echo "Experiment \"$name\" is " . ($experiment ? "" : "not ") . "active\n";
		}
		return true;
	}

	public function handleCreativeContent(CreativeContentPacket $packet) : bool{
		echo "updating creative inventory data\n";
		$items = array_map(function(CreativeContentEntry $entry) : array{
			return self::objectToOrderedArray($this->itemStackToJson($entry->getItem()));
		}, $packet->getEntries());
		file_put_contents($this->bedrockDataPath . '/creativeitems.json', json_encode($items, JSON_PRETTY_PRINT) . "\n");
		return true;
	}

	private function recipeIngredientToJson(RecipeIngredient $itemStack) : RecipeIngredientData{
		if($this->itemTypeDictionary === null){
			throw new PacketHandlingException("Can't process item yet; haven't received item type dictionary");
		}

		$descriptor = $itemStack->getDescriptor();
		if($descriptor === null){
			throw new PacketHandlingException("Can't json-serialize a null recipe ingredient");
		}
		$data = new RecipeIngredientData();

		if($descriptor instanceof IntIdMetaItemDescriptor || $descriptor instanceof StringIdMetaItemDescriptor){
			if($descriptor instanceof IntIdMetaItemDescriptor){
				$data->name = $this->itemTypeDictionary->fromIntId($descriptor->getId());
			}else{
				$data->name = $descriptor->getId();
			}
			$meta = $descriptor->getMeta();
			if($meta !== 32767){
				$blockStateId = $this->blockTranslator->getBlockStateDictionary()->lookupStateIdFromIdMeta($data->name, $meta);
				if($this->blockItemIdMap->lookupBlockId($data->name) !== null && $blockStateId !== null){
					$blockState = $this->blockTranslator->getBlockStateDictionary()->generateDataFromStateId($blockStateId);
					if($blockState !== null && count($blockState->getStates()) > 0){
						$data->block_states = self::blockStatePropertiesToString($blockState);
					}
				}elseif($meta !== 0){
					$data->meta = $meta;
				}
			}else{
				$data->meta = $meta;
			}
		}elseif($descriptor instanceof TagItemDescriptor){
			$data->tag = $descriptor->getTag();
		}elseif($descriptor instanceof MolangItemDescriptor){
			$data->molang_expression = $descriptor->getMolangExpression();
			$data->molang_version = $descriptor->getMolangVersion();
		}elseif($descriptor instanceof ComplexAliasItemDescriptor){
			$data->name = $descriptor->getAlias();
		}else{
			throw new \UnexpectedValueException("Unknown item descriptor type " . get_class($descriptor));
		}
		if($itemStack->getCount() !== 1){
			$data->count = $itemStack->getCount();
		}

		return $data;
	}

	private function shapedRecipeToJson(ShapedRecipe $entry) : ShapedRecipeData{
		$keys = [];

		$shape = [];
		$char = ord("A");

		$outputsByKey = [];
		foreach(Utils::promoteKeys($entry->getInput()) as $x => $row){
			foreach(Utils::promoteKeys($row) as $y => $ingredient){
				if($ingredient->getDescriptor() === null){
					$shape[$x][$y] = " ";
				}else{
					$jsonIngredient = $this->recipeIngredientToJson($ingredient);
					$hash = json_encode($jsonIngredient, JSON_THROW_ON_ERROR);
					if(isset($keys[$hash])){
						$shape[$x][$y] = $keys[$hash];
					}else{
						$key = chr($char);
						$keys[$hash] = $shape[$x][$y] = $key;
						$outputsByKey[$key] = $jsonIngredient;
						$char++;
					}
				}
			}
		}
		$unlockingIngredients = $entry->getUnlockingRequirement()->getUnlockingIngredients();
		return new ShapedRecipeData(
			array_map(fn(array $array) => implode('', array_values($array)), array_values($shape)),
			$outputsByKey,
			array_map(fn(ItemStack $output) => $this->itemStackToJson($output), $entry->getOutput()),
			$entry->getBlockName(),
			$entry->getPriority(),
			$unlockingIngredients !== null ? array_map(fn(RecipeIngredient $input) => $this->recipeIngredientToJson($input), $unlockingIngredients) : []
		);
	}

	private function shapelessRecipeToJson(ShapelessRecipe $recipe) : ShapelessRecipeData{
		$unlockingIngredients = $recipe->getUnlockingRequirement()->getUnlockingIngredients();
		return new ShapelessRecipeData(
			array_map(fn(RecipeIngredient $input) => $this->recipeIngredientToJson($input), $recipe->getInputs()),
			array_map(fn(ItemStack $output) => $this->itemStackToJson($output), $recipe->getOutputs()),
			$recipe->getBlockName(),
			$recipe->getPriority(),
			$unlockingIngredients !== null ? array_map(fn(RecipeIngredient $input) => $this->recipeIngredientToJson($input), $unlockingIngredients) : []
		);
	}

	private function furnaceRecipeToJson(FurnaceRecipe $recipe) : FurnaceRecipeData{
		return new FurnaceRecipeData(
			$this->recipeIngredientToJson(new RecipeIngredient(new IntIdMetaItemDescriptor($recipe->getInputId(), $recipe->getInputMeta() ?? 32767), 1)),
			$this->itemStackToJson($recipe->getResult()),
			$recipe->getBlockName()
		);
	}

	private function smithingRecipeToJson(SmithingTransformRecipe $recipe) : SmithingTransformRecipeData{
		return new SmithingTransformRecipeData(
			$this->recipeIngredientToJson($recipe->getTemplate()),
			$this->recipeIngredientToJson($recipe->getInput()),
			$this->recipeIngredientToJson($recipe->getAddition()),
			$this->itemStackToJson($recipe->getOutput()),
			$recipe->getBlockName()
		);
	}

	private function smithingTrimRecipeToJson(SmithingTrimRecipe $recipe) : SmithingTrimRecipeData{
		return new SmithingTrimRecipeData(
			$this->recipeIngredientToJson($recipe->getTemplate()),
			$this->recipeIngredientToJson($recipe->getInput()),
			$this->recipeIngredientToJson($recipe->getAddition()),
			$recipe->getBlockName()
		);
	}

	public function handleCraftingData(CraftingDataPacket $packet) : bool{
		echo "updating crafting data\n";

		$recipesPath = Path::join($this->bedrockDataPath, "recipes");
		Filesystem::recursiveUnlink($recipesPath);
		@mkdir($recipesPath);

		$recipes = [];
		foreach($packet->recipesWithTypeIds as $entry){
			static $typeMap = [
				CraftingDataPacket::ENTRY_SHAPELESS => "shapeless_crafting",
				CraftingDataPacket::ENTRY_SHAPED => "shaped_crafting",
				CraftingDataPacket::ENTRY_FURNACE => "smelting",
				CraftingDataPacket::ENTRY_FURNACE_DATA => "smelting",
				CraftingDataPacket::ENTRY_MULTI => "special_hardcoded",
				CraftingDataPacket::ENTRY_USER_DATA_SHAPELESS => "shapeless_shulker_box",
				CraftingDataPacket::ENTRY_SHAPELESS_CHEMISTRY => "shapeless_chemistry",
				CraftingDataPacket::ENTRY_SHAPED_CHEMISTRY => "shaped_chemistry",
				CraftingDataPacket::ENTRY_SMITHING_TRANSFORM => "smithing",
				CraftingDataPacket::ENTRY_SMITHING_TRIM => "smithing_trim",
			];
			if(!isset($typeMap[$entry->getTypeId()])){
				throw new \UnexpectedValueException("Unknown recipe type ID " . $entry->getTypeId());
			}
			$mappedType = $typeMap[$entry->getTypeId()];

			if($entry instanceof ShapedRecipe){
				//all known recipes are currently symmetric and I don't feel like attaching a `symmetric` field to
				//every shaped recipe for this - split it into a separate category instead
				if(!$entry->isSymmetric()){
					$recipes[$mappedType . "_asymmetric"][] = $this->shapedRecipeToJson($entry);
				}else{
					$recipes[$mappedType][] = $this->shapedRecipeToJson($entry);
				}
			}elseif($entry instanceof ShapelessRecipe){
				$recipes[$mappedType][] = $this->shapelessRecipeToJson($entry);
			}elseif($entry instanceof MultiRecipe){
				$recipes[$mappedType][] = $entry->getRecipeId()->toString();
			}elseif($entry instanceof FurnaceRecipe){
				$recipes[$mappedType][] = $this->furnaceRecipeToJson($entry);
			}elseif($entry instanceof SmithingTransformRecipe){
				$recipes[$mappedType][] = $this->smithingRecipeToJson($entry);
			}elseif($entry instanceof SmithingTrimRecipe){
				$recipes[$mappedType][] = $this->smithingTrimRecipeToJson($entry);
			}else{
				throw new AssumptionFailedError("Unknown recipe type " . get_class($entry));
			}
		}

		foreach($packet->potionTypeRecipes as $recipe){
			$recipes["potion_type"][] = new PotionTypeRecipeData(
				$this->recipeIngredientToJson(new RecipeIngredient(new IntIdMetaItemDescriptor($recipe->getInputItemId(), $recipe->getInputItemMeta()), 1)),
				$this->recipeIngredientToJson(new RecipeIngredient(new IntIdMetaItemDescriptor($recipe->getIngredientItemId(), $recipe->getIngredientItemMeta()), 1)),
				$this->itemStackToJson(new ItemStack($recipe->getOutputItemId(), $recipe->getOutputItemMeta(), 1, 0, "")),
			);
		}

		if($this->itemTypeDictionary === null){
			throw new AssumptionFailedError("We should have already crashed if this was null");
		}
		foreach($packet->potionContainerRecipes as $recipe){
			$recipes["potion_container_change"][] = new PotionContainerChangeRecipeData(
				$this->itemTypeDictionary->fromIntId($recipe->getInputItemId()),
				$this->recipeIngredientToJson(new RecipeIngredient(new IntIdMetaItemDescriptor($recipe->getIngredientItemId(), 0), 1)),
				$this->itemTypeDictionary->fromIntId($recipe->getOutputItemId()),
			);
		}

		//this sorts the data into a canonical order to make diffs between versions reliable
		//how the data is ordered doesn't matter as long as it's reproducible
		foreach($recipes as $_type => $entries){
			$_sortedRecipes = [];
			$_seen = [];
			foreach($entries as $entry){
				$entry = self::sort($entry);
				$_key = json_encode($entry);
				$duplicates = $_seen[$_key] ??= 0;
				$_seen[$_key]++;
				$suffix = chr(ord("a") + $duplicates);
				$_sortedRecipes[$_key . $suffix] = $entry;
			}
			ksort($_sortedRecipes, SORT_STRING);
			$recipes[$_type] = array_values($_sortedRecipes);
			foreach($_seen as $_key => $_seenCount){
				if($_seenCount > 1){
					fwrite(STDERR, "warning: $_type recipe $_key was seen $_seenCount times\n");
				}
			}
		}

		ksort($recipes, SORT_STRING);
		foreach($recipes as $type => $entries){
			echo "$type: " . count($entries) . "\n";
		}
		foreach($recipes as $type => $entries){
			file_put_contents(Path::join($recipesPath, $type . '.json'), json_encode($entries, JSON_PRETTY_PRINT) . "\n");
		}

		return true;
	}

	public function handleAvailableActorIdentifiers(AvailableActorIdentifiersPacket $packet) : bool{
		echo "storing actor identifiers" . PHP_EOL;

		$tag = $packet->identifiers->getRoot();
		if(!($tag instanceof CompoundTag)){
			throw new AssumptionFailedError();
		}
		$idList = $tag->getTag("idlist");
		if(!($idList instanceof ListTag) || $idList->getTagType() !== NBT::TAG_Compound){
			echo $tag . "\n";
			throw new \RuntimeException("expected TAG_List<TAG_Compound>(\"idlist\") tag inside root TAG_Compound");
		}
		if($tag->count() > 1){
			echo $tag . "\n";
			echo "!!! unexpected extra data found in available actor identifiers\n";
		}
		echo "updating legacy => string entity ID mapping table\n";
		$map = [];
		/**
		 * @var CompoundTag $thing
		 */
		foreach($idList as $thing){
			$map[$thing->getString("id")] = $thing->getInt("rid");
		}
		asort($map, SORT_NUMERIC);
		file_put_contents($this->bedrockDataPath . '/entity_id_map.json', json_encode($map, JSON_PRETTY_PRINT) . "\n");
		echo "storing entity identifiers\n";
		file_put_contents($this->bedrockDataPath . '/entity_identifiers.nbt', $packet->identifiers->getEncodedNbt());
		return true;
	}

	public function handleBiomeDefinitionList(BiomeDefinitionListPacket $packet) : bool{
		echo "storing biome definitions" . PHP_EOL;

		file_put_contents($this->bedrockDataPath . '/biome_definitions_full.nbt', $packet->definitions->getEncodedNbt());

		$nbt = $packet->definitions->getRoot();
		if(!$nbt instanceof CompoundTag){
			throw new AssumptionFailedError();
		}
		$strippedNbt = clone $nbt;
		foreach($strippedNbt as $compound){
			if($compound instanceof CompoundTag){
				foreach([
					"minecraft:capped_surface",
					"minecraft:consolidated_features",
					"minecraft:frozen_ocean_surface",
					"minecraft:legacy_world_generation_rules",
					"minecraft:mesa_surface",
					"minecraft:mountain_parameters",
					"minecraft:multinoise_generation_rules",
					"minecraft:overworld_generation_rules",
					"minecraft:surface_material_adjustments",
					"minecraft:surface_parameters",
					"minecraft:swamp_surface",
				] as $remove){
					$compound->removeTag($remove);
				}
			}
		}

		file_put_contents($this->bedrockDataPath . '/biome_definitions.nbt', (new CacheableNbt($strippedNbt))->getEncodedNbt());

		return true;
	}
}

/**
 * @param string[] $argv
 */
function main(array $argv) : int{
	if(count($argv) !== 3){
		fwrite(STDERR, 'Usage: ' . PHP_BINARY . ' ' . __FILE__ . ' <input file> <path to BedrockData>');
		return 1;
	}
	[, $inputFile, $bedrockDataPath] = $argv;

	$handler = new ParserPacketHandler($bedrockDataPath);

	$packets = file($inputFile, FILE_IGNORE_NEW_LINES);
	if($packets === false){
		fwrite(STDERR, 'File ' . $inputFile . ' not found or permission denied');
		return 1;
	}

	foreach($packets as $lineNum => $line){
		$parts = explode(':', $line);
		if(count($parts) !== 2){
			fwrite(STDERR, 'Wrong packet format at line ' . ($lineNum + 1) . ', expected read:base64 or write:base64');
			return 1;
		}
		$raw = base64_decode($parts[1], true);
		if($raw === false){
			fwrite(STDERR, 'Invalid base64\'d packet on line ' . ($lineNum + 1) . ' could not be parsed');
			return 1;
		}

		$pk = PacketPool::getInstance()->getPacket($raw);
		if($pk === null){
			fwrite(STDERR, "Unknown packet on line " . ($lineNum + 1) . ": " . $parts[1]);
			continue;
		}
		$serializer = PacketSerializer::decoder($raw, 0);

		$pk->decode($serializer);
		$pk->handle($handler);
		if(!$serializer->feof()){
			echo "Packet on line " . ($lineNum + 1) . ": didn't read all data from " . get_class($pk) . " (stopped at offset " . $serializer->getOffset() . " of " . strlen($serializer->getBuffer()) . " bytes): " . bin2hex($serializer->getRemaining()) . "\n";
		}
	}
	return 0;
}

exit(main($argv));
