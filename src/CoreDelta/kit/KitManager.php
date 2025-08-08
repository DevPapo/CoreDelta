<?php

declare(strict_types=1);

namespace CoreDelta\kit;

use CoreDelta\CoreDelta;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class KitManager {
    
    private CoreDelta $plugin;
    private array $kits = [];
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
        $this->loadKits();
    }
    
    private function loadKits(): void {
        $this->kits = [
            "default" => [
                "name" => "Default",
                "items" => [
                    VanillaItems::IRON_SWORD(),
                    VanillaItems::BOW(),
                    VanillaItems::ARROW()->setCount(16),
                    VanillaItems::GOLDEN_APPLE()->setCount(2),
                    VanillaItems::WOODEN_PICKAXE()
                ]
            ],
            "archer" => [
                "name" => "Archer",
                "items" => [
                    VanillaItems::STONE_SWORD(),
                    VanillaItems::BOW(),
                    VanillaItems::ARROW()->setCount(32),
                    VanillaItems::GOLDEN_APPLE()
                ]
            ],
            "warrior" => [
                "name" => "Warrior",
                "items" => [
                    VanillaItems::IRON_SWORD(),
                    VanillaItems::IRON_CHESTPLATE(),
                    VanillaItems::IRON_LEGGINGS(),
                    VanillaItems::GOLDEN_APPLE()->setCount(3)
                ]
            ]
        ];
    }
    
    public function getKit(string $kitName): ?array {
        return $this->kits[$kitName] ?? null;
    }
    
    public function giveKit(Player $player, string $kitName): bool {
        $kit = $this->getKit($kitName);
        if (!$kit) return false;
        
        $player->getInventory()->clearAll();
        
        foreach ($kit["items"] as $item) {
            $player->getInventory()->addItem($item);
        }
        
        return true;
    }
    
    public function getAllKits(): array {
        return $this->kits;
    }
}
