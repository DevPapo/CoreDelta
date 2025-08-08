<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SwStatsCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swstats", "Ver estadísticas", "/swstats [jugador]");
        $this->setPermission("coredelta.command.stats");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $playerName = $sender instanceof Player ? $sender->getName() : ($args[0] ?? null);
        if (!$playerName) {
            $sender->sendMessage(TextFormat::RED . "Uso: /swstats [jugador]");
            return;
        }
        $stats = $this->plugin->getDatabaseManager()->getPlayerStats($playerName);
        if (!$stats) {
            $sender->sendMessage(TextFormat::RED . "¡Jugador '$playerName' no encontrado!");
            return;
        }
        $sender->sendMessage(TextFormat::YELLOW . "=== Stats de $playerName ===");
        $sender->sendMessage(TextFormat::GREEN . "Kills: " . $stats["kills"]);
        $sender->sendMessage(TextFormat::GREEN . "Deaths: " . $stats["deaths"]);
        $sender->sendMessage(TextFormat::GREEN . "Wins: " . $stats["wins"]);
        $sender->sendMessage(TextFormat::GREEN . "Games Played: " . $stats["games_played"]);
        $sender->sendMessage(TextFormat::GREEN . "Points: " . $stats["points"]);
    }
}