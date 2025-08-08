<?php

declare(strict_types=1);

namespace CoreDelta\game;

use CoreDelta\CoreDelta;
use CoreDelta\arena\Arena;
use CoreDelta\game\Game;

class GameManager {
    
    private CoreDelta $plugin;
    private array $games = [];
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
    }
    
    public function createGame(Arena $arena): ?Game {
        if ($this->gameExists($arena->getName())) {
            return null;
        }
        
        $game = new Game($this->plugin, $arena);
        $this->games[$arena->getName()] = $game;
        return $game;
    }
    
    public function getGame(string $arenaName): ?Game {
        return $this->games[$arenaName] ?? null;
    }
    
    public function removeGame(string $arenaName): bool {
        if (!$this->gameExists($arenaName)) {
            return false;
        }
        
        $game = $this->games[$arenaName];
        $game->stop();
        
        unset($this->games[$arenaName]);
        return true;
    }
    
    public function gameExists(string $arenaName): bool {
        return isset($this->games[$arenaName]);
    }
    
    public function getAllGames(): array {
        return $this->games;
    }
}
