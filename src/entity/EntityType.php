<?php

declare(strict_types=1);

namespace pocketmine\entity;

class EntityType {
    private string $saveName;
    private ?string $className;

    public function __construct(string $saveName, ?string $className = null) {
        $this->saveName = $saveName;
        $this->className = $className;
    }

    public function getSaveName(): string {
        return $this->saveName;
    }

    public function getClassName(): ?string {
        return $this->className;
    }

    public function __toString(): string {
        return $this->saveName;
    }
}
