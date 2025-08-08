<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SwTopCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swtop", "Ver top jugadores", "/swtop");
        $this->setPermission("coredelta.command.top");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $topPlayers = $this->plugin->getDatabaseManager()->getTopPlayers(10);
        $sender->sendMessage(TextFormat::YELLOW . "=== Top 10 Jugadores ===");
        foreach ($topPlayers as $i => $player) {
            $sender->sendMessage(TextFormat::GREEN . ($i + 1) . ". " . $player["username"] . " - " . $player["points"] . " puntos");
        }
    }
}