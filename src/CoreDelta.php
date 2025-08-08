<?php

declare(strict_types=1);

namespace CoreDelta;

use CoreDelta\arena\ArenaManager;
use CoreDelta\database\DatabaseManager;
use CoreDelta\game\GameManager;
use CoreDelta\kit\KitManager;
use CoreDelta\listener\EventListener;
use CoreDelta\scoreboard\ScoreboardManager;
use CoreDelta\command\SkywarsCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class CoreDelta extends PluginBase {
    
    private static self $instance;
    
    private DatabaseManager $databaseManager;
    private ArenaManager $arenaManager;
    private GameManager $gameManager;
    private KitManager $kitManager;
    private ScoreboardManager $scoreboardManager;
    
    public static function getInstance(): self {
        return self::$instance;
    }
    
    public function onLoad(): void {
        self::$instance = $this;
    }
    
    public function onEnable(): void {
        $this->saveDefaultConfig();
        
        // Initialize managers
        $this->databaseManager = new DatabaseManager($this);
        $this->arenaManager = new ArenaManager($this);
        $this->gameManager = new GameManager($this);
        $this->kitManager = new KitManager($this);
        $this->scoreboardManager = new ScoreboardManager($this);
        
        // Register listeners
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        
        // Register commands
        $this->getServer()->getCommandMap()->register("CoreDelta", new SkywarsCommand($this));
        
        $this->getLogger()->info("CoreDelta Skywars enabled successfully!");
    }
    
    public function onDisable(): void {
        $this->databaseManager->close();
        $this->getLogger()->info("CoreDelta Skywars disabled!");
    }
    
    public function getDatabaseManager(): DatabaseManager {
        return $this->databaseManager;
    }
    
    public function getArenaManager(): ArenaManager {
        return $this->arenaManager;
    }
    
    public function getGameManager(): GameManager {
        return $this->gameManager;
    }
    
    public function getKitManager(): KitManager {
        return $this->kitManager;
    }
    
    public function getScoreboardManager(): ScoreboardManager {
        return $this->scoreboardManager;
    }
}
