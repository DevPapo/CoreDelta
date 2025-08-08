<?php

declare(strict_types=1);

namespace CoreDelta\kit;

use CoreDelta\CoreDelta;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
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
                    ["id" => ItemIds::IRON_SWORD, "count" => 1],
                    ["id" => ItemIds::BOW, "count" => 1],
                    ["id" => ItemIds::ARROW, "count" => 16],
                    ["id" => ItemIds::GOLDEN_APPLE, "count" => 2],
                    ["id" => ItemIds::WOODEN_PICKAXE, "count" => 1]
                ]
            ],
            "archer" => [
                "name" => "Archer",
                "items" => [
                    ["id" => ItemIds::STONE_SWORD, "count" => 1],
                    ["id" => ItemIds::BOW, "count" => 1],
                    ["id" => ItemIds::ARROW, "count" => 32],
                    ["id" => ItemIds::GOLDEN_APPLE, "count" => 1]
                ]
            ],
            "warrior" => [
                "name" => "Warrior",
                "items" => [
                    ["id" => ItemIds::IRON_SWORD, "count" => 1],
                    ["id" => ItemIds::IRON_CHESTPLATE, "count" => 1],
                    ["id" => ItemIds::IRON_LEGGINGS, "count" => 1],
                    ["id" => ItemIds::GOLDEN_APPLE, "count" => 3]
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
        
        foreach ($kit["items"] as $itemData) {
            $item = ItemFactory::getInstance()->get($itemData["id"], 0, $itemData["count"]);
            $player->getInventory()->addItem($item);
        }
        
        return true;
    }
    
    public function getAllKits(): array {
        return $this->kits;
    }
}
