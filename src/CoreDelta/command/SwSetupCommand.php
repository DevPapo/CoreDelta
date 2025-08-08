<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SwSetupCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swsetup", "Setup de arena", "/swsetup <arena>");
        $this->setPermission("coredelta.command.setup");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $sender->sendMessage(TextFormat::YELLOW . "Setup de arena: ¡Próximamente!");
    }
}