<?php

declare(strict_types=1);

namespace CoreDelta\arena;

use CoreDelta\CoreDelta;
use CoreDelta\game\Game;
use pocketmine\world\World;
use pocketmine\math\Vector3;

class Arena {
    
    private CoreDelta $plugin;
    private string $name;
    private array $data;
    private ?Game $game = null;
    
    public function __construct(CoreDelta $plugin, string $name, array $data) {
        $this->plugin = $plugin;
        $this->name = $name;
        $this->data = $data;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function getWorld(): string {
        return $this->data["world"] ?? "";
    }
    
    public function getMinPlayers(): int {
        return $this->data["min_players"] ?? 2;
    }
    
    public function getMaxPlayers(): int {
        return $this->data["max_players"] ?? 8;
    }
    
    public function getSpawnPoints(): array {
        return $this->data["spawn_points"] ?? [];
    }
    
    public function getCenterChest(): ?Vector3 {
        $chest = $this->data["center_chest"] ?? null;
        if ($chest) {
            return new Vector3($chest["x"], $chest["y"], $chest["z"]);
        }
        return null;
    }
    
    public function isEnabled(): bool {
        return $this->data["enabled"] ?? true;
    }
    
    public function getGame(): ?Game {
        return $this->game;
    }
    
    public function setGame(?Game $game): void {
        $this->game = $game;
    }
    
    public function getData(): array {
        return $this->data;
    }
}
