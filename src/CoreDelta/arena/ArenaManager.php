<?php

declare(strict_types=1);

namespace CoreDelta\arena;

use CoreDelta\CoreDelta;
use CoreDelta\arena\Arena;
use pocketmine\utils\Config;

class ArenaManager {
    
    private CoreDelta $plugin;
    private array $arenas = [];
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
        $this->loadArenas();
    }
    
    private function loadArenas(): void {
        $arenasDir = $this->plugin->getDataFolder() . "arenas/";
        if (!is_dir($arenasDir)) {
            mkdir($arenasDir, 0777, true);
        }
        
        foreach (glob($arenasDir . "*.yml") as $file) {
            $config = new Config($file, Config::YAML);
            $name = basename($file, ".yml");
            $this->arenas[$name] = new Arena($this->plugin, $name, $config->getAll());
        }
    }
    
    public function createArena(string $name, array $data): bool {
        if ($this->arenaExists($name)) {
            return false;
        }
        
        $arenasDir = $this->plugin->getDataFolder() . "arenas/";
        $config = new Config($arenasDir . $name . ".yml", Config::YAML);
        $config->setAll($data);
        $config->save();
        
        $this->arenas[$name] = new Arena($this->plugin, $name, $data);
        return true;
    }
    
    public function deleteArena(string $name): bool {
        if (!$this->arenaExists($name)) {
            return false;
        }
        
        $file = $this->plugin->getDataFolder() . "arenas/" . $name . ".yml";
        if (file_exists($file)) {
            unlink($file);
        }
        
        unset($this->arenas[$name]);
        return true;
    }
    
    public function getArena(string $name): ?Arena {
        return $this->arenas[$name] ?? null;
    }
    
    public function getAllArenas(): array {
        return $this->arenas;
    }
    
    public function arenaExists(string $name): bool {
        return isset($this->arenas[$name]);
    }
    
    public function getAvailableArenas(): array {
        $available = [];
        foreach ($this->arenas as $name => $arena) {
            if ($arena->isEnabled()) {
                $available[$name] = $arena;
            }
        }
        return $available;
    }
}
