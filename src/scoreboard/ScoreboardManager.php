<?php

declare(strict_types=1);

namespace CoreDelta\scoreboard;

use CoreDelta\CoreDelta;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ScoreboardManager {
    
    private CoreDelta $plugin;
    private array $scoreboards = [];
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
    }
    
    public function showScoreboard(Player $player, array $lines): void {
        $this->scoreboards[$player->getName()] = $lines;
        
        $title = $this->plugin->getConfig()->getNested("scoreboard.title", "§l§eSKYWARS");
        $player->sendMessage(TextFormat::YELLOW . "=== " . $title . " ===");
        
        foreach ($lines as $line) {
            $player->sendMessage($line);
        }
    }
    
    public function hideScoreboard(Player $player): void {
        unset($this->scoreboards[$player->getName()]);
    }
    
    public function updateScoreboard(Player $player, array $lines): void {
        if (isset($this->scoreboards[$player->getName()])) {
            $this->showScoreboard($player, $lines);
        }
    }
    
    public function getScoreboard(Player $player): ?array {
        return $this->scoreboards[$player->getName()] ?? null;
    }
}
