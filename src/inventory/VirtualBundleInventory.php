<?php

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\item\Bundle;
use pocketmine\Server;
use pocketmine\item\Item;

final class VirtualBundleInventory extends SimpleInventory{

    private Bundle $holder;

    public function __construct(Bundle $holder, int $size = 9){
        parent::__construct($size);
        $this->holder = $holder;
    }

    public function getHolder(): Bundle{
        return $this->holder;
    }

    public function setHolder(Bundle $bundle): void{
        $this->holder = $bundle;
    }

    public function setItem(int $index, \pocketmine\item\Item $item) : void{
        parent::setItem($index, $item);
        try{
            Server::getInstance()->getLogger()->info("VirtualBundleInventory::setItem index=$index, item=" . $item->getName() . ", class=" . get_class($item) . ", typeId=" . $item->getTypeId() . ", stateId=" . $item->getStateId());
        }catch(\Throwable $e){
            // ignore
        }
        try{
            $this->holder->saveNBT();
        }catch(\Throwable $e){
            try{
                Server::getInstance()->getLogger()->debug("VirtualBundleInventory::setItem: saveNBT failed: " . $e->getMessage());
            }catch(\Throwable $e){
                // ignore
            }
        }
    }

    public function clear(int $index) : void{
        parent::clear($index);
        try{
            Server::getInstance()->getLogger()->info("VirtualBundleInventory::clear index=$index");
        }catch(\Throwable $e){
            // ignore
        }
        try{
            $this->holder->saveNBT();
        }catch(\Throwable $e){
            try{
                Server::getInstance()->getLogger()->debug("VirtualBundleInventory::clear: saveNBT failed: " . $e->getMessage());
            }catch(\Throwable $e){
                // ignore
            }
        }
    }

    /**
     * Provide packets to open the client UI for this virtual inventory.
     * InventoryManager special-cases VirtualTradeInventory; we'll rely on InventoryManager checking this class.
     *
     * @param int $id network window id
     * @return array
     */
    public function createInventoryOpenPackets(int $id) : array{
        // Use a generic container open packet (entityInv) to show a container UI.
        // Pass the bundle's bundle_id in the actorUniqueId field so Bedrock clients can recognise this as a dynamic container.
        // Using the real bundle_id is required so the client generates ItemStackRequest container mappings targeting
        // the dynamic container (instead of mapping slots to the player's cursor/inventory). If bundle_id is missing,
        // fall back to 0.
        $dynamicId = $this->holder->getBundleId() ?? 0;
        try{
            Server::getInstance()->getLogger()->info("VirtualBundleInventory::createInventoryOpenPackets called for bundle_id=$dynamicId, networkWindowId=$id");
        }catch(\Throwable $e){
            // ignore if server not available in some contexts
        }
        return [ContainerOpenPacket::entityInv($id, WindowTypes::CONTAINER, $dynamicId)];
    }

    /**
     * Apply a slot change coming from an InventoryTransaction (SlotChangeAction).
     * This is where bundle-specific packing rules should be enforced.
     */
    public function applySlotChange(int $slot, Item $sourceItem, Item $targetItem, Player $player): void{
        try{
            Server::getInstance()->getLogger()->info("VirtualBundleInventory::applySlotChange called slot=$slot, source=" . $sourceItem->getName() . ", target=" . $targetItem->getName() . ", player=" . $player->getName());
        }catch(\Throwable $e){
            // ignore
        }

        if ($targetItem->isNull()) {
            $this->clear($slot);
            return;
        }

        // Reject items that are not allowed in bundles (e.g. block entity items such as shulker boxes)
        $namedTag = $targetItem->getNamedTag();
        $blockEntityTag = $namedTag->getCompoundTag(Item::TAG_BLOCK_ENTITY_TAG);
        if($blockEntityTag !== null){
            try{
                Server::getInstance()->getLogger()->info("VirtualBundleInventory: rejecting item with BlockEntityTag (likely shulker) by player=" . $player->getName());
            }catch(\Throwable $e){ }
            return; // do not accept
        }

        // Implement package-slot accounting:
        // - bundle has 64 package-units capacity
        // - each full inventory-stack of a given item type consumes perStackUsage = 64 / maxStackSize units
        // - filling existing partial stacks does not consume additional package-units

        $incoming = clone $targetItem;
        $incomingCount = $incoming->getCount();
        $maxStack = $incoming->getMaxStackSize();
        if($maxStack <= 0) $maxStack = 1;
        $perStackUsage = (int) (64 / $maxStack);
        if($perStackUsage <= 0) $perStackUsage = 1;

        $currentUsage = $this->computePackageUsageFromContents();
        $availableUnits = max(0, 64 - $currentUsage);

        // First, compute how much free space exists in partial stacks of the same item
        $freeInPartial = 0;
        $sameSlots = [];
        for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
            $slotItem = $this->getItem($i);
            if($slotItem->isNull()) continue;
            if($slotItem->canStackWith($incoming)){
                $free = $maxStack - $slotItem->getCount();
                if($free > 0){
                    $freeInPartial += $free;
                    $sameSlots[] = $i;
                }
            }
        }

        $toAdd = $incomingCount;
        // Fill partials first (does not increase package units)
        $fillFromPartial = min($freeInPartial, $toAdd);
        $toAdd -= $fillFromPartial;

        // Remaining items require new stacks. Each new stack uses perStackUsage units.
        $emptySlots = [];
        for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
            if($this->isSlotEmpty($i)) $emptySlots[] = $i;
        }

        $neededNewStacks = (int) ceil($toAdd / $maxStack);
        $maxNewStacksByUnits = (int) floor($availableUnits / $perStackUsage);
        $maxNewStacksBySlots = count($emptySlots);
        $allowedNewStacks = min($neededNewStacks, $maxNewStacksByUnits, $maxNewStacksBySlots);

        // Calculate how many items we can actually add
        $allowedItemsFromNewStacks = $allowedNewStacks * $maxStack;
        $actuallyAddable = $fillFromPartial + min($toAdd, $allowedItemsFromNewStacks);

        if($actuallyAddable <= 0){
            try{ Server::getInstance()->getLogger()->info("VirtualBundleInventory: no capacity to add items for player=" . $player->getName()); }catch(\Throwable $e){}
            return;
        }

        // Apply fills to existing partial stacks
        $remainingToPlace = $actuallyAddable;
        // Fill partials (LIFO: prefer last matching slots)
        usort($sameSlots, function($a, $b){ return $b <=> $a; });
        foreach($sameSlots as $i){
            if($remainingToPlace <= 0) break;
            $slotItem = $this->getItem($i);
            $free = $maxStack - $slotItem->getCount();
            $add = min($free, $remainingToPlace);
            if($add > 0){
                $slotItem->setCount($slotItem->getCount() + $add);
                $this->setItem($i, $slotItem);
                $remainingToPlace -= $add;
            }
        }

        // Create new stacks in empty slots
        foreach($emptySlots as $index){
            if($remainingToPlace <= 0) break;
            $amount = min($maxStack, $remainingToPlace);
            $new = clone $incoming;
            $new->setCount($amount);
            $this->setItem($index, $new);
            $remainingToPlace -= $amount;
        }

        // Persist bundle NBT after changes
        try{
            $this->holder->saveNBT();
        }catch(\Throwable $e){
            try{ Server::getInstance()->getLogger()->debug("VirtualBundleInventory::applySlotChange: saveNBT failed: " . $e->getMessage()); }catch(\Throwable $e){}
        }

        try{
            Server::getInstance()->getLogger()->info("VirtualBundleInventory: added $actuallyAddable items of " . $incoming->getName() . " into bundle for player=" . $player->getName());
        }catch(\Throwable $e){ }
    }

    private function computePackageUsageFromContents() : int{
        $usage = 0;
        foreach($this->getContents() as $item){
            if($item->isNull()) continue;
            $maxStack = $item->getMaxStackSize();
            if($maxStack <= 0) $maxStack = 1;
            $perStackUsage = (int) (64 / $maxStack);
            if($perStackUsage <= 0) $perStackUsage = 1;
            $stacks = (int) ceil($item->getCount() / $maxStack);
            $usage += $stacks * $perStackUsage;
        }
        return $usage;
    }
}
