<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SkywarsCommand extends Command {
    
    private CoreDelta $plugin;
    
    public function __construct(CoreDelta $plugin) {
        parent::__construct("skywars", "CoreDelta Skywars commands", "/skywars <subcommand>", ["sw"]);
        $this->setPermission("coredelta.command.skywars");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (empty($args)) {
            $this->sendHelp($sender);
            return true;
        }

        $subcommand = strtolower(array_shift($args));

        switch ($subcommand) {
            case "create":
                return $this->handleCreate($sender, $args);
            case "delete":
                return $this->handleDelete($sender, $args);
            case "join":
                return $this->handleJoin($sender, $args);
            case "leave":
                return $this->handleLeave($sender);
            case "stats":
                return $this->handleStats($sender, $args);
            case "top":
                return $this->handleTop($sender);
            case "list":
                return $this->handleList($sender);
            case "tutorial":
                return $this->handleTutorial($sender, $args);
            case "setup":
                return $this->handleSetup($sender, $args);
            default:
                $sender->sendMessage("§cUnknown subcommand. Use /skywars for help.");
                return true;
        }
    }
    
    private function sendHelp(CommandSender $sender): void {
        $sender->sendMessage(TextFormat::YELLOW . "=== CoreDelta Skywars Commands ===");
        $sender->sendMessage(TextFormat::GREEN . "/skywars create <name> - Create a new arena");
        $sender->sendMessage(TextFormat::GREEN . "/skywars delete <name> - Delete an arena");
        $sender->sendMessage(TextFormat::GREEN . "/skywars join <arena> - Join a game");
        $sender->sendMessage(TextFormat::GREEN . "/skywars leave - Leave current game");
        $sender->sendMessage(TextFormat::GREEN . "/skywars stats [player] - View player stats");
        $sender->sendMessage(TextFormat::GREEN . "/skywars top - View top players");
        $sender->sendMessage(TextFormat::GREEN . "/skywars list - List available arenas");
        $sender->sendMessage(TextFormat::GREEN . "/skywars tutorial <type> - Show tutorial");
        $sender->sendMessage(TextFormat::GREEN . "/skywars setup <arena> - Start arena setup wizard");
    }
    
    private function handleCreate(CommandSender $sender, array $args): bool {
        if (!$sender->hasPermission("coredelta.command.create")) {
            $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command!");
            return true;
        }
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /skywars create <name>");
            return true;
        }
        
        $name = $args[0];
        $success = $this->plugin->getArenaManager()->createArena($name, [
            "world" => $name,
            "min_players" => 2,
            "max_players" => 8,
            "spawn_points" => [],
            "center_chest" => null,
            "enabled" => false
        ]);
        
        if ($success) {
            $sender->sendMessage(TextFormat::GREEN . "Arena '$name' created successfully!");
        } else {
            $sender->sendMessage(TextFormat::RED . "Arena '$name' already exists!");
        }
        
        return true;
    }
    
    private function handleDelete(CommandSender $sender, array $args): bool {
        if (!$sender->hasPermission("coredelta.command.delete")) {
            $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command!");
            return true;
        }
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /skywars delete <name>");
            return true;
        }
        
        $name = $args[0];
        $success = $this->plugin->getArenaManager()->deleteArena($name);
        
        if ($success) {
            $sender->sendMessage(TextFormat::GREEN . "Arena '$name' deleted successfully!");
        } else {
            $sender->sendMessage(TextFormat::RED . "Arena '$name' does not exist!");
        }
        
        return true;
    }
    
    private function handleJoin(CommandSender $sender, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game!");
            return true;
        }
        
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /skywars join <arena>");
            return true;
        }
        
        $arenaName = $args[0];
        $arena = $this->plugin->getArenaManager()->getArena($arenaName);
        
        if (!$arena) {
            $sender->sendMessage(TextFormat::RED . "Arena '$arenaName' does not exist!");
            return true;
        }
        
        $game = $this->plugin->getGameManager()->getGame($arenaName);
        if (!$game) {
            $game = $this->plugin->getGameManager()->createGame($arena);
        }
        
        $success = $game->addPlayer($sender);
        
        if ($success) {
            $sender->sendMessage(TextFormat::GREEN . "Joined game in arena '$arenaName'!");
        } else {
            $sender->sendMessage(TextFormat::RED . "Arena '$arenaName' is full!");
        }
        
        return true;
    }
    
    private function handleLeave(CommandSender $sender): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game!");
            return true;
        }
        
        foreach ($this->plugin->getGameManager()->getAllGames() as $game) {
            foreach ($game->getPlayers() as $gamePlayer) {
                if ($gamePlayer->getPlayer()->getName() === $sender->getName()) {
                    $game->removePlayer($sender);
                    $sender->sendMessage(TextFormat::GREEN . "You left the game!");
                    return true;
                }
            }
        }
        
        $sender->sendMessage(TextFormat::RED . "You're not in any game!");
        return true;
    }
    
    private function handleStats(CommandSender $sender, array $args): bool {
        $playerName = $sender instanceof Player ? $sender->getName() : ($args[0] ?? null);
        
        if (!$playerName) {
            $sender->sendMessage(TextFormat::RED . "Usage: /skywars stats [player]");
            return true;
        }
        
        $stats = $this->plugin->getDatabaseManager()->getPlayerStats($playerName);
        
        if (!$stats) {
            $sender->sendMessage(TextFormat::RED . "Player '$playerName' not found!");
            return true;
        }
        
        $sender->sendMessage(TextFormat::YELLOW . "=== Stats for $playerName ===");
        $sender->sendMessage(TextFormat::GREEN . "Kills: " . $stats["kills"]);
        $sender->sendMessage(TextFormat::GREEN . "Deaths: " . $stats["deaths"]);
        $sender->sendMessage(TextFormat::GREEN . "Wins: " . $stats["wins"]);
        $sender->sendMessage(TextFormat::GREEN . "Games Played: " . $stats["games_played"]);
        $sender->sendMessage(TextFormat::GREEN . "Points: " . $stats["points"]);
        
        return true;
    }
    
    private function handleTop(CommandSender $sender): bool {
        $topPlayers = $this->plugin->getDatabaseManager()->getTopPlayers(10);
        
        $sender->sendMessage(TextFormat::YELLOW . "=== Top 10 Players ===");
        foreach ($topPlayers as $i => $player) {
            $sender->sendMessage(TextFormat::GREEN . ($i + 1) . ". " . $player["username"] . " - " . $player["points"] . " points");
        }
        
        return true;
    }
    
    private function handleList(CommandSender $sender): bool {
        $arenas = $this->plugin->getArenaManager()->getAvailableArenas();
        
        if (empty($arenas)) {
            $sender->sendMessage(TextFormat::RED . "No arenas available!");
            return true;
        }
        
        $sender->sendMessage(TextFormat::YELLOW . "=== Available Arenas ===");
        foreach ($arenas as $name => $arena) {
            $game = $this->plugin->getGameManager()->getGame($name);
            $players = $game ? count($game->getPlayers()) : 0;
            $sender->sendMessage(TextFormat::GREEN . $name . " - " . $players . "/" . $arena->getMaxPlayers() . " players");
        }
        
        return true;
    }
}
