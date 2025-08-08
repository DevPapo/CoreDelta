<?php

declare(strict_types=1);

namespace CoreDelta\player;

use CoreDelta\game\Game;
use pocketmine\player\Player;

class GamePlayer {
    
    private Player $player;
    private Game $game;
    private bool $alive = true;
    private int $kills = 0;
    
    public function __construct(Player $player, Game $game) {
        $this->player = $player;
        $this->game = $game;
    }
    
    public function getPlayer(): Player {
        return $this->player;
    }
    
    public function isAlive(): bool {
        return $this->alive;
    }
    
    public function setAlive(bool $alive): void {
        $this->alive = $alive;
    }
    
    public function addKill(): void {
        $this->kills++;
    }
    
    public function getKills(): int {
        return $this->kills;
    }
    
    public function reset(): void {
        $this->alive = true;
        $this->kills = 0;
    }
}
