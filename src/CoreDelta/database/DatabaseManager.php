<?php

declare(strict_types=1);

namespace CoreDelta\database;

use CoreDelta\CoreDelta;
use mysqli;

class DatabaseManager {
    
    private CoreDelta $plugin;
    private mysqli $connection;
    private string $tablePrefix;
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
        $this->connect();
        $this->initTables();
    }
    
    private function connect(): void {
        $config = $this->plugin->getConfig()->get("mysql");
        if (!is_array($config)) {
            throw new \RuntimeException("No se encontró la configuración 'mysql' en config.yml o está mal formada.");
        }
        $this->tablePrefix = $config["table-prefix"];
        
        $this->connection = new mysqli(
            $config["host"],
            $config["username"],
            $config["password"],
            $config["database"],
            $config["port"]
        );
        
        if ($this->connection->connect_error) {
            $this->plugin->getLogger()->error("Failed to connect to MySQL: " . $this->connection->connect_error);
            return;
        }
        
        $this->plugin->getLogger()->info("Successfully connected to MySQL database!");
    }
    
    private function initTables(): void {
        $tables = [
            "CREATE TABLE IF NOT EXISTS {$this->tablePrefix}players (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(16) NOT NULL UNIQUE,
                kills INT DEFAULT 0,
                deaths INT DEFAULT 0,
                wins INT DEFAULT 0,
                games_played INT DEFAULT 0,
                points INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS {$this->tablePrefix}arenas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                world VARCHAR(50) NOT NULL,
                min_players INT DEFAULT 2,
                max_players INT DEFAULT 8,
                spawn_points TEXT,
                center_chest VARCHAR(100),
                status VARCHAR(20) DEFAULT 'waiting',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS {$this->tablePrefix}games (
                id INT AUTO_INCREMENT PRIMARY KEY,
                arena_id INT,
                start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                end_time TIMESTAMP NULL,
                winner VARCHAR(16) NULL,
                FOREIGN KEY (arena_id) REFERENCES {$this->tablePrefix}arenas(id)
            )"
        ];
        
        foreach ($tables as $table) {
            $this->connection->query($table);
        }
    }
    
    public function getConnection(): mysqli {
        return $this->connection;
    }
    
    public function getTablePrefix(): string {
        return $this->tablePrefix;
    }
    
    public function close(): void {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function registerPlayer(string $username): void {
        $stmt = $this->connection->prepare(
            "INSERT IGNORE INTO {$this->tablePrefix}players (username) VALUES (?)"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
    }
    
    public function updatePlayerStats(string $username, array $stats): void {
        $stmt = $this->connection->prepare(
            "UPDATE {$this->tablePrefix}players 
            SET kills = kills + ?, deaths = deaths + ?, wins = wins + ?, 
                games_played = games_played + ?, points = points + ?
            WHERE username = ?"
        );
        
        $stmt->bind_param(
            "iiiiis",
            $stats["kills"] ?? 0,
            $stats["deaths"] ?? 0,
            $stats["wins"] ?? 0,
            $stats["games"] ?? 0,
            $stats["points"] ?? 0,
            $username
        );
        
        $stmt->execute();
        $stmt->close();
    }
    
    public function getPlayerStats(string $username): ?array {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tablePrefix}players WHERE username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats ?: null;
    }
    
    public function createArena(array $data): int {
        $stmt = $this->connection->prepare(
            "INSERT INTO {$this->tablePrefix}arenas (name, world, min_players, max_players, spawn_points, center_chest) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssiiis",
            $data["name"],
            $data["world"],
            $data["min_players"],
            $data["max_players"],
            json_encode($data["spawn_points"]),
            $data["center_chest"]
        );
        $stmt->execute();
        $arenaId = $stmt->insert_id;
        $stmt->close();
        
        return $arenaId;
    }
    
    public function updateArena(int $arenaId, array $data): void {
        $stmt = $this->connection->prepare(
            "UPDATE {$this->tablePrefix}arenas 
            SET name = ?, world = ?, min_players = ?, max_players = ?, 
                spawn_points = ?, center_chest = ?, status = ?
            WHERE id = ?"
        );
        $stmt->bind_param(
            "ssiiisii",
            $data["name"],
            $data["world"],
            $data["min_players"],
            $data["max_players"],
            json_encode($data["spawn_points"]),
            $data["center_chest"],
            $data["status"],
            $arenaId
        );
        $stmt->execute();
        $stmt->close();
    }
    
    public function getArena(int $arenaId): ?array {
        $stmt = $this->connection->prepare(
            "SELECT * FROM {$this->tablePrefix}arenas WHERE id = ?"
        );
        $stmt->bind_param("i", $arenaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $arena = $result->fetch_assoc();
        $stmt->close();
        
        return $arena ?: null;
    }
    
    public function deleteArena(int $arenaId): void {
        $stmt = $this->connection->prepare(
            "DELETE FROM {$this->tablePrefix}arenas WHERE id = ?"
        );
        $stmt->bind_param("i", $arenaId);
        $stmt->execute();
        $stmt->close();
    }
    
    public function startGame(int $arenaId): void {
        $stmt = $this->connection->prepare(
            "INSERT INTO {$this->tablePrefix}games (arena_id) VALUES (?)"
        );
        $stmt->bind_param("i", $arenaId);
        $stmt->execute();
        $stmt->close();
    }
    
    public function endGame(int $gameId, string $winner): void {
        $stmt = $this->connection->prepare(
            "UPDATE {$this->tablePrefix}games 
            SET end_time = CURRENT_TIMESTAMP, winner = ?
            WHERE id = ?"
        );
        $stmt->bind_param("si", $winner, $gameId);
        $stmt->execute();
        $stmt->close();
    }
    
    public function getOngoingGames(): array {
        $result = $this->connection->query(
            "SELECT g.*, a.name as arena_name, a.min_players, a.max_players, a.spawn_points, a.center_chest
            FROM {$this->tablePrefix}games g
            JOIN {$this->tablePrefix}arenas a ON g.arena_id = a.id
            WHERE g.end_time IS NULL"
        );
        
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
        
        return $games;
    }
    
    public function getPlayerRankings(): array {
        $result = $this->connection->query(
            "SELECT username, kills, deaths, wins, games_played, points
            FROM {$this->tablePrefix}players
            ORDER BY points DESC, wins DESC, kills DESC"
        );
        
        $rankings = [];
        while ($row = $result->fetch_assoc()) {
            $rankings[] = $row;
        }
        
        return $rankings;
    }
}