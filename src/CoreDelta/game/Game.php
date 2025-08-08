<?php

declare(strict_types=1);

namespace CoreDelta\game;

use CoreDelta\CoreDelta;
use CoreDelta\arena\Arena;
use CoreDelta\player\GamePlayer;
use CoreDelta\scoreboard\ScoreboardManager;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\world\Position;

class Game {
    
    private CoreDelta $plugin;
    private Arena $arena;
    private array $players = [];
    private int $phase = 0; // 0: waiting, 1: countdown, 2: playing, 3: ending
    private int $countdown;
    private int $gameTime;
    private int $gracePeriod;
    private ?ClosureTask $gameTask = null;
    
    public function __construct(CoreDelta $plugin, Arena $arena) {
        $this->plugin = $plugin;
        $this->arena = $arena;
        $this->countdown = $plugin->getConfig()->getNested("game.countdown-time", 30);
        $this->gameTime = $plugin->getConfig()->getNested("game.game-time", 600);
        $this->gracePeriod = $plugin->getConfig()->getNested("game.grace-period", 60);
    }
    
    public function addPlayer(Player $player): bool {
        if (count($this->players) >= $this->arena->getMaxPlayers()) {
            return false;
        }
        
        $this->players[$player->getName()] = new GamePlayer($player, $this);
        
        // Teleport to lobby
        $spawn = $this->arena->getSpawnPoints()[0] ?? ["x" => 0, "y" => 64, "z" => 0];
        $player->teleport(new Position($spawn["x"], $spawn["y"], $spawn["z"], $this->plugin->getServer()->getWorldManager()->getWorldByName($this->arena->getWorld())));
        
        $this->broadcastMessage(str_replace("{player}", $player->getName(), $this->plugin->getConfig()->getNested("messages.join", "{prefix} {player} joined the game!")));
        
        if ($this->phase === 0 && count($this->players) >= $this->arena->getMinPlayers()) {
            $this->start();
        }
        
        return true;
    }
    
    public function removePlayer(Player $player): void {
        if (isset($this->players[$player->getName()])) {
            $gamePlayer = $this->players[$player->getName()];
            $gamePlayer->reset();
            unset($this->players[$player->getName()]);
            
            $this->broadcastMessage(str_replace("{player}", $player->getName(), $this->plugin->getConfig()->getNested("messages.leave", "{prefix} {player} left the game!")));
            
            if (count($this->players) < $this->arena->getMinPlayers() && $this->phase === 1) {
                $this->cancel();
            }
        }
    }
    
    public function start(): void {
        if ($this->phase !== 0) return;
        
        $this->phase = 1;
        $this->broadcastMessage(TextFormat::GREEN . "Game starting in " . $this->countdown . " seconds!");
        
        $this->gameTask = new ClosureTask(function() {
            $this->tick();
        });
        
        $this->plugin->getScheduler()->scheduleRepeatingTask($this->gameTask, 20);
    }
    
    private function tick(): void {
        switch ($this->phase) {
            case 1: // Countdown
                $this->countdown--;
                
                if ($this->countdown <= 0) {
                    $this->phase = 2;
                    $this->distributePlayers();
                    $this->broadcastMessage(TextFormat::GREEN . "Game started! Good luck!");
                } elseif ($this->countdown <= 5) {
                    $this->broadcastMessage(TextFormat::YELLOW . "Starting in " . $this->countdown . " seconds!");
                }
                break;
                
            case 2: // Playing
                $this->gameTime--;
                
                if ($this->gameTime <= 0 || count($this->getAlivePlayers()) <= 1) {
                    $this->end();
                } elseif ($this->gameTime % 60 === 0) {
                    $this->broadcastMessage(TextFormat::YELLOW . "Game ends in " . ($this->gameTime / 60) . " minutes!");
                }
                break;
                
            case 3: // Ending
                $this->gameTime--;
                
                if ($this->gameTime <= 0) {
                    $this->stop();
                }
                break;
        }
    }
    
    public function end(): void {
        $alive = $this->getAlivePlayers();
        
        if (count($alive) === 1) {
            $winner = array_shift($alive);
            $this->broadcastMessage(str_replace("{player}", $winner->getPlayer()->getName(), $this->plugin->getConfig()->getNested("messages.win", "{prefix} {player} won the game!")));
            
            // Update stats
            $this->plugin->getDatabaseManager()->updatePlayerStats($winner->getPlayer()->getName(), [
                "wins" => 1,
                "kills" => $winner->getKills(),
                "points" => 50
            ]);
            
            foreach ($this->players as $gamePlayer) {
                $this->plugin->getDatabaseManager()->updatePlayerStats($gamePlayer->getPlayer()->getName(), [
                    "games" => 1,
                    "kills" => $gamePlayer->getKills(),
                    "points" => $gamePlayer->getKills() * 10
                ]);
            }
        }
        
        $this->phase = 3;
        $this->gameTime = 10;
    }
    
    public function stop(): void {
        if ($this->gameTask !== null) {
            $this->gameTask->cancel();
            $this->gameTask = null;
        }
        
        foreach ($this->players as $gamePlayer) {
            $gamePlayer->reset();
            $gamePlayer->getPlayer()->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        }
        
        $this->players = [];
        $this->plugin->getGameManager()->removeGame($this->arena->getName());
    }
    
    public function cancel(): void {
        $this->broadcastMessage(TextFormat::RED . "Game cancelled due to insufficient players!");
        $this->stop();
    }
    
    public function distributePlayers(): void {
        $spawnPoints = $this->arena->getSpawnPoints();
        $i = 0;
        
        foreach ($this->players as $gamePlayer) {
            $player = $gamePlayer->getPlayer();
            if ($i < count($spawnPoints)) {
                $spawn = $spawnPoints[$i];
                $player->teleport(new Position($spawn["x"], $spawn["y"], $spawn["z"], $this->plugin->getServer()->getWorldManager()->getWorldByName($this->arena->getWorld())));
                $i++;
            }
        }
    }
    
    public function broadcastMessage(string $message): void {
        $prefix = $this->plugin->getConfig()->getNested("messages.prefix", "§l§e[§6SkyWars§e]§r");
        $message = str_replace("{prefix}", $prefix, $message);
        
        foreach ($this->players as $gamePlayer) {
            $player = $gamePlayer->getPlayer();
            $player->sendMessage(str_replace("{player}", $player->getName(), $message));
        }
    }
    
    public function getArena(): Arena {
        return $this->arena;
    }
    
    public function getPlayers(): array {
        return $this->players;
    }
    
    public function getAlivePlayers(): array {
        return array_filter($this->players, fn($p) => $p->isAlive());
    }
    
    public function getPhase(): int {
        return $this->phase;
    }
}
