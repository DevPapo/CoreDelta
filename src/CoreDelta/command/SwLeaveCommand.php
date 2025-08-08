<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SwLeaveCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swleave", "Salir de la arena", "/swleave");
        $this->setPermission("coredelta.command.leave");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego.");
            return;
        }
        foreach ($this->plugin->getGameManager()->getAllGames() as $game) {
            foreach ($game->getPlayers() as $gamePlayer) {
                if ($gamePlayer->getPlayer()->getName() === $sender->getName()) {
                    $game->removePlayer($sender);
                    $sender->sendMessage(TextFormat::GREEN . "¡Saliste de la partida!");
                    return;
                }
            }
        }
        $sender->sendMessage(TextFormat::RED . "¡No estás en ninguna partida!");
    }
}