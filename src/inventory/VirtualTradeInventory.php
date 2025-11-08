<?php

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\entity\trade\TradeRecipe;
use pocketmine\entity\trade\TradeRecipeData;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use function count;

final class VirtualTradeInventory extends SimpleInventory{

    private const TAG_RECIPES = "Recipes";
    private const TAG_TIER_EXP_REQUIREMENTS = "TierExpRequirements";

    public function __construct(private string $name, private int $holderRuntimeId, private TradeRecipeData $recipeData){
        parent::__construct(2);
    }

    public function getRecipeData() : TradeRecipeData{
        return $this->recipeData;
    }

    public function createInventoryOpenPackets(int $id) : array{
        $recipeData = $this->recipeData;
        $recipes = $recipeData->getRecipes();

        $tierExpRequirements = [];
        foreach($recipeData->getTierExpRequirements() as $tier => $expRequirement){
            $tierExpRequirements[] = CompoundTag::create()->setInt((string) $tier, $expRequirement);
        }

        $recipesTag = new ListTag();
        for($i = 0; $i < count($recipes); $i++){
            $recipeNBT = $recipes[$i]->nbtSerialize();
            // net ID behaves like index of the recipe.
            $recipeNBT->setInt(TradeRecipe::TAG_NET_ID, $i + 1);
            $recipesTag->push($recipeNBT);
        }

        $nbt = CompoundTag::create()
            ->setTag(self::TAG_RECIPES, $recipesTag)
            ->setTag(self::TAG_TIER_EXP_REQUIREMENTS, new ListTag($tierExpRequirements));

        return [
            UpdateTradePacket::create(
                $id,
                WindowTypes::TRADING,
                0,
                $recipeData->getTier(),
                $this->holderRuntimeId,
                -1,
                $this->name,
                true,
                true,
                new CacheableNbt($nbt)
            )
        ];
    }
}
