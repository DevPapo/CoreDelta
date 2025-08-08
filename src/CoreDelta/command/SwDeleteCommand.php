<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SwDeleteCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swdelete", "Eliminar una arena", "/swdelete <nombre>");
        $this->setPermission("coredelta.command.delete");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Uso: /swdelete <nombre>");
            return;
        }
        $name = $args[0];
        $success = $this->plugin->getArenaManager()->deleteArena($name);
        if ($success) {
            $sender->sendMessage(TextFormat::GREEN . "¡Arena '$name' eliminada exitosamente!");
        } else {
            $sender->sendMessage(TextFormat::RED . "¡La arena '$name' no existe!");
        }
    }
}