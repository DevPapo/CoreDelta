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
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data;
    }
    
    public function getTopPlayers(int $limit = 10): array {
        $result = $this->connection->query(
            "SELECT * FROM {$this->tablePrefix}players 
            ORDER BY points DESC, wins DESC 
            LIMIT $limit"
        );
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
