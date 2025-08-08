<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SwCreateCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swcreate", "Crear una nueva arena", "/swcreate <nombre>");
        $this->setPermission("coredelta.command.create");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(TextFormat::RED . "No tienes permiso para usar este comando.");
            return;
        }
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Uso: /swcreate <nombre>");
            return;
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
            $sender->sendMessage(TextFormat::GREEN . "¡Arena '$name' creada exitosamente!");
        } else {
            $sender->sendMessage(TextFormat::RED . "¡La arena '$name' ya existe!");
        }
    }
}