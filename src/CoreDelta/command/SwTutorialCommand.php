<?php

declare(strict_types=1);

namespace CoreDelta\command;

use CoreDelta\CoreDelta;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class SwTutorialCommand extends Command {

    private CoreDelta $plugin;

    public function __construct(CoreDelta $plugin) {
        parent::__construct("swtutorial", "Ver tutorial", "/swtutorial <tipo>");
        $this->setPermission("coredelta.command.tutorial");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $sender->sendMessage(TextFormat::YELLOW . "Tutorial: ¡Próximamente!");
    }
}