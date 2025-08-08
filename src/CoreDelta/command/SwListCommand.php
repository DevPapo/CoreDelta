<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SwListCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swlist", "Listar arenas", "/swlist");
        $this->setPermission("coredelta.command.list");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $arenas = $this->plugin->getArenaManager()->getAvailableArenas();
        if (empty($arenas)) {
            $sender->sendMessage(TextFormat::RED . "¡No hay arenas disponibles!");
            return;
        }
        $sender->sendMessage(TextFormat::YELLOW . "=== Arenas Disponibles ===");
        foreach ($arenas as $name => $arena) {
            $game = $this->plugin->getGameManager()->getGame($name);
            $players = $game ? count($game->getPlayers()) : 0;
            $sender->sendMessage(TextFormat::GREEN . $name . " - " . $players . "/" . $arena->getMaxPlayers() . " jugadores");
        }
    }
}