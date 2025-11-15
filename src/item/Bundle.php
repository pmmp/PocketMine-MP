<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\VirtualBundleInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\item\ItemUseResult;
use pocketmine\Server;

class Bundle extends Item implements InventoryHolder{
    private const MAX_CAPACITY = 64;
    private Inventory $inventory;
    /** @var int next bundle id to assign when missing */
    private static int $nextBundleId = 1;

    public function __construct(ItemIdentifier $identifier, string $name = "Bundle"){
        parent::__construct($identifier, $name);
        // allow multiple stored item entries inside the bundle
        // UI slots: use a virtual bundle inventory so the server can open a container UI for the item
        $this->inventory = new VirtualBundleInventory($this, 9);

        // Ensure the item has a bundle_id in its NamedTag for Bedrock dynamic container mapping
        $tag = $this->getNamedTag();
        $id = $tag->getInt("bundle_id", 0);
        if ($id === 0) {
            $tag->setInt("bundle_id", self::$nextBundleId++);
            $this->setNamedTag($tag);
        }
    }

    public function getBundleId(): int{
        return $this->getNamedTag()->getInt("bundle_id", 0);
    }

    public function getInventory(): Inventory{
        return $this->inventory;
    }

    public function saveNBT(): void{
        $tag = new CompoundTag();
        $this->serializeCompoundTag($tag);
        $this->setNamedTag($tag);
    }

    public function addItem(Item $item): bool{
        $total = 0;
        foreach ($this->inventory->getContents() as $content) {
            if ($content->isNull()) continue;
            $total += $content->getCount();
        }
        $total += $item->getCount();

        if ($total > self::MAX_CAPACITY) {
            return false;
        }

        $leftover = $this->inventory->addItem($item);
        return count($leftover) === 0;
    }

    private function getItemSize(Item $item): int{
        // Deprecated helper: capacity is tracked based on counts now.
        return $item->getCount();
    }

    protected function serializeCompoundTag(CompoundTag $tag): void{
        parent::serializeCompoundTag($tag);

        $itemsTag = new ListTag();
        foreach ($this->inventory->getContents() as $item) {
            $itemsTag->push($item->nbtSerialize());
        }
        if($itemsTag->count() > 0){
            $tag->setTag("BundleItems", $itemsTag);
            // also write Bedrock storage component tag so Bedrock clients/serializers can read bundle contents
            $tag->setTag("storage_item_component_content", clone $itemsTag);
        }else{
            $tag->removeTag("BundleItems");
            $tag->removeTag("storage_item_component_content");
        }
    }

    protected function deserializeCompoundTag(CompoundTag $tag): void{
        parent::deserializeCompoundTag($tag);

        $items = [];
        $list = $tag->getListTag("BundleItems");
        if($list === null){
            $list = $tag->getListTag("storage_item_component_content");
        }
        if($list !== null){
            foreach($list as $itemTag){
                $items[] = Item::nbtDeserialize($itemTag);
            }
        }

        $this->inventory->setContents($items);
    }

    public function getMaxStackSize(): int{
        return 1;
    }

    public function onClickAir(Player $player, \pocketmine\math\Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
        try{
            Server::getInstance()->getLogger()->info("Bundle::onClickAir: opening virtual bundle inventory for player=" . $player->getName() . ", bundle_id=" . $this->getBundleId());
        }catch(\Throwable $e){
            // ignore
        }

        $player->setCurrentWindow($this->inventory);
        return ItemUseResult::SUCCESS;
    }

    public function __clone(){
        parent::__clone();
        $original = $this->inventory;
        $this->inventory = new VirtualBundleInventory($this, $original->getSize());
        // Preserve existing bundle contents when cloning the item instance
        $this->inventory->setContents($original->getContents(true));
    }

   
}