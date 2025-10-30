<?php

declare(strict_types=1);

namespace pocketmine\equipment;

use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use pocketmine\inventory\BaseInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\utils\EquipmentSlot;

class ArmorStandEntityEquipment extends BaseInventory
{

    /** @var Living */
    protected $holder;

    /** @var Item[] */
    protected array $slots = [];

    public function __construct(Living $entity)
    {
        $this->holder = $entity;
        parent::__construct();
    }

    public function getHolder(): Living
    {
        return $this->holder;
    }

    public function getSize(): int
    {
        return 2;
    }

    public function sendSlot(int $index, $target): void
    {
        if ($target instanceof Player) {
            $target = [$target];
        }

        $pk = new MobEquipmentPacket();
        $pk->actorRuntimeId = $this->holder->getId();
        $pk->inventorySlot = $pk->hotbarSlot = $index;
        $pk->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItem($index)));


        if ($target instanceof Player) {
            $target = [$target];
        }

        foreach ($target as $player) {
            $player->getNetworkSession()->sendDataPacket($pk);
        }
    }

    public function getItem(int $index): Item
    {
        return $this->slots[$index] ?? VanillaBlocks::AIR()->asItem();
    }

    public function getViewers(): array
    {
        return $this->holder->getViewers();
    }

    public function getItemInHand(): Item
    {
        return $this->getItem(EquipmentSlot::MAINHAND);
    }

    public function getOffhandItem(): Item
    {
        return $this->getItem(EquipmentSlot::OFFHAND);
    }

    public function setItemInHand(Item $item, bool $send = true): bool
    {
        return $this->setItem(EquipmentSlot::MAINHAND, $item, $send);
    }

    public function setOffhandItem(Item $item, bool $send = true): bool
    {
        return $this->setItem(EquipmentSlot::OFFHAND, $item, $send);
    }

    public function sendContents($target): void
    {
        $this->sendSlot(EquipmentSlot::MAINHAND, $target);
        $this->sendSlot(EquipmentSlot::OFFHAND, $target);
    }

    public static function legacyDeserialize(array $data): self
    {
        throw new \RuntimeException("ArmorStandEntityEquipment::legacyDeserialize is not supported because a Living holder is required to construct ArmorStandEntityEquipment.");
    }
    //     public static function legacyDeserialize(array $data): self
    // {
    //     return new self(
    //         id: $data['id'] ?? 0,
    //         meta: $data['meta'] ?? 0,
    //         count: $data['count'] ?? 0,
    //         blockRuntimeId: $data['blockRuntimeId'] ?? 0,
    //         rawExtraData: base64_decode($data['rawExtraData'] ?? "")
    //     );
    // }
    //public function getContents(): array {
    public function getContents(bool $includeEmpty = false): array
    {
        return $this->slots ?? [];
    }

    protected function internalSetItem(int $index, Item $item): void
    {
        $this->slots[$index] = $item;
    }

    protected function internalSetContents(array $items): void
    {
        $this->slots = $items;
    }
}
