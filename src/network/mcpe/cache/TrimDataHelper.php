<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\cache;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\ItemTagToIdMap;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\TrimDataPacket;
use pocketmine\network\mcpe\protocol\types\TrimMaterial;
use pocketmine\network\mcpe\protocol\types\TrimPattern;
use pocketmine\utils\SingletonTrait;
use function str_ends_with;
use function strlen;
use function strpos;
use function substr;
use function var_dump;

final class TrimDataHelper{
	use SingletonTrait;

	public const TAG_PATTERNS = "minecraft:trim_templates";

	public const ITEM_ID_TO_MATERIAL_MAPPINGS = [
		ItemTypeIds::AMETHYST_SHARD => "amethyst",
		ItemTypeIds::COPPER_INGOT => "copper",
		ItemTypeIds::DIAMOND => "diamond",
		ItemTypeIds::EMERALD => "emerald",
		ItemTypeIds::GOLD_INGOT => "gold",
		ItemTypeIds::IRON_INGOT => "iron",
		ItemTypeIds::LAPIS_LAZULI => "lapis",
		ItemTypeIds::NETHERITE_INGOT => "netherite",
		ItemTypeIds::NETHER_QUARTZ => "quartz",
		ItemTypeIds::REDSTONE_DUST => "redstone"
	];

	/**
	 * @param string[] $itemIdToPatternMappings
	 * @phpstan-param array<int, string> $itemIdToPatternMappings
	 */
	public function __construct(private TrimDataPacket $packet, private array $itemIdToPatternMappings){}

	private static function make() : TrimDataHelper{
		$itemIdToPatternIdMappings = [];
		return new TrimDataHelper(TrimDataPacket::create(TrimDataHelper::loadPatterns($itemIdToPatternIdMappings), TrimDataHelper::loadMaterials()), $itemIdToPatternIdMappings);
	}

	public function getPacket() : TrimDataPacket{
		return $this->packet;
	}

	public function itemIdToPatternId(int $itemId) : string{
		var_dump($this->itemIdToPatternMappings);
		return $this->itemIdToPatternMappings[$itemId];
	}

	/**
	 * @param
	 *
	 * @return TrimPattern[]
	 * @phpstan-return list<TrimPattern>
	 */
	private static function loadPatterns(array &$itemIdToPatternIdMappings) : array{
		$patterns = [];
		foreach(ItemTagToIdMap::getInstance()->getIdsForTag(self::TAG_PATTERNS) as $stringId){
			$patterns[] = $pattern = new TrimPattern($stringId, self::getPatternName($stringId));
			$itemIdToPatternIdMappings[StringToItemParser::getInstance()->parse($stringId)->getTypeId()] = $pattern->getPatternId();
		}
		return $patterns;
	}

	public static function getPatternName(string $stringId) : string{
		if (!str_ends_with($stringId, "_armor_trim_smithing_template")){
			throw new \InvalidArgumentException("Pattern names can only be constructed from trim item ids"); //todo
		}
		$prefixLength = strlen("minecraft:");
		return substr($stringId, $prefixLength, (strpos($stringId, "_") - $prefixLength));
	}

	/**
	 * @return TrimMaterial[]
	 * @phpstan-return list<TrimMaterial>
	 */
	private static function loadMaterials() : array{
		$materials = [];
		$materials[] = new TrimMaterial("amethyst", "§u", ItemTypeNames::AMETHYST_SHARD);
		$materials[] = new TrimMaterial("copper", "§n", ItemTypeNames::COPPER_INGOT);
		$materials[] = new TrimMaterial("diamond", "§s", ItemTypeNames::DIAMOND);
		$materials[] = new TrimMaterial("emerald", "§q", ItemTypeNames::EMERALD);
		$materials[] = new TrimMaterial("gold", "§p", ItemTypeNames::GOLD_INGOT);
		$materials[] = new TrimMaterial("iron", "§i", ItemTypeNames::IRON_INGOT);
		$materials[] = new TrimMaterial("lapis", "§t", ItemTypeNames::LAPIS_LAZULI);
		$materials[] = new TrimMaterial("netherite", "§j", ItemTypeNames::NETHERITE_INGOT);
		$materials[] = new TrimMaterial("quartz", "§h", ItemTypeNames::QUARTZ);
		$materials[] = new TrimMaterial("redstone", "§m", ItemTypeNames::REDSTONE);
		return $materials;
	}
}