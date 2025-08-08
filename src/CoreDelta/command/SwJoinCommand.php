<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SwJoinCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swjoin", "Unirse a una arena", "/swjoin <arena>");
        $this->setPermission("coredelta.command.join");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego.");
            return;
        }
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Uso: /swjoin <arena>");
            return;
        }
        $arenaName = $args[0];
        $arena = $this->plugin->getArenaManager()->getArena($arenaName);
        if (!$arena) {
            $sender->sendMessage(TextFormat::RED . "¡La arena '$arenaName' no existe!");
            return;
        }
        $game = $this->plugin->getGameManager()->getGame($arenaName) ?? $this->plugin->getGameManager()->createGame($arena);
        $success = $game->addPlayer($sender);
        if ($success) {
            $sender->sendMessage(TextFormat::GREEN . "¡Te uniste a la arena '$arenaName'!");
        } else {
            $sender->sendMessage(TextFormat::RED . "¡La arena '$arenaName' está llena!");
        }
    }
}