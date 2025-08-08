<?php

declare(strict_types=1);

namespace CoreDelta\setup;

use CoreDelta\CoreDelta;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class SetupWizard {
    
    private CoreDelta $plugin;
    private array $setupSessions = [];
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
    }
    
    public function startSetup(Player $player, string $arenaName): void {
        $this->setupSessions[$player->getName()] = [
            "arena" => $arenaName,
            "step" => 1,
            "data" => [
                "spawn_points" => [],
                "center_chest" => null
            ]
        ];
        
        $this->sendSetupStep($player);
    }
    
    public function handleSetupCommand(Player $player, array $args): void {
        $session = $this->setupSessions[$player->getName()] ?? null;
        if (!$session) {
            $player->sendMessage(TextFormat::RED . "No estás en modo setup. Usa /skywars setup <arena> para empezar");
            return;
        }
        
        $step = $session["step"];
        $arena = $session["arena"];
        
        switch ($step) {
            case 1: // Configurar spawn points
                $this->addSpawnPoint($player, $args);
                break;
            case 2: // Configurar cofre central
                $this->setCenterChest($player, $args);
                break;
            case 3: // Finalizar
                $this->finishSetup($player);
                break;
        }
    }
    
    private function sendSetupStep(Player $player): void {
        $session = $this->setupSessions[$player->getName()];
        $step = $session["step"];
        
        switch ($step) {
            case 1:
                $player->sendMessage(TextFormat::GREEN . "=== Setup de Arena - Paso 1 ===");
                $player->sendMessage(TextFormat::YELLOW . "Ve a cada posición de spawn y usa:");
                $player->sendMessage(TextFormat::AQUA . "/skywars setup spawn add");
                $player->sendMessage(TextFormat::YELLOW . "Spawns configurados: " . count($session["data"]["spawn_points"]));
                $player->sendMessage(TextFormat::YELLOW . "Cuando termines, usa: /skywars setup next");
                break;
            case 2:
                $player->sendMessage(TextFormat::GREEN . "=== Setup de Arena - Paso 2 ===");
                $player->sendMessage(TextFormat::YELLOW . "Ve al cofre central y usa:");
                $player->sendMessage(TextFormat::AQUA . "/skywars setup chest");
                break;
            case 3:
                $player->sendMessage(TextFormat::GREEN . "=== Setup de Arena - Paso 3 ===");
                $player->sendMessage(TextFormat::YELLOW . "Revisión final:");
                $player->sendMessage(TextFormat::YELLOW . "Spawns: " . count($session["data"]["spawn_points"]));
                $player->sendMessage(TextFormat::YELLOW . "Cofre central: " . ($session["data"]["center_chest"] ? "Configurado" : "No configurado"));
                $player->sendMessage(TextFormat::AQUA . "Usa /skywars setup finish para completar");
                break;
        }
    }
    
    private function addSpawnPoint(Player $player, array $args): void {
        if (!isset($args[0]) || $args[0] !== "add") {
            return;
        }
        
        $pos = $player->getPosition();
        $this->setupSessions[$player->getName()]["data"]["spawn_points"][] = [
            "x" => $pos->getX(),
            "y" => $pos->getY(),
            "z" => $pos->getZ()
        ];
        
        $player->sendMessage(TextFormat::GREEN . "Spawn point agregado en tu posición actual");
    }
    
    private function setCenterChest(Player $player, array $args): void {
        $pos = $player->getPosition();
        $this->setupSessions[$player->getName()]["data"]["center_chest"] = [
            "x" => $pos->getX(),
            "y" => $pos->getY(),
            "z" => $pos->getZ()
        ];
        
        $player->sendMessage(TextFormat::GREEN . "Cofre central configurado en tu posición actual");
        $this->setupSessions[$player->getName()]["step"] = 3;
        $this->sendSetupStep($player);
    }
    
    private function finishSetup(Player $player): void {
        $session = $this->setupSessions[$player->getName()];
        $arena = $session["arena"];
        $data = $session["data"];
        
        if (count($data["spawn_points"]) < 2) {
            $player->sendMessage(TextFormat::RED . "Necesitas al menos 2 spawn points");
            return;
        }
        
        if (!$data["center_chest"]) {
            $player->sendMessage(TextFormat::RED . "Debes configurar el cofre central");
            return;
        }
        
        $success = $this->plugin->getArenaManager()->createArena($arena, [
            "world" => $player->getWorld()->getFolderName(),
            "min_players" => 2,
            "max_players" => count($data["spawn_points"]),
            "spawn_points" => $data["spawn_points"],
            "center_chest" => $data["center_chest"],
            "enabled" => true
        ]);
        
        if ($success) {
            $player->sendMessage(TextFormat::GREEN . "¡Arena '$arena' configurada exitosamente!");
            $player->sendMessage(TextFormat::YELLOW . "Usa /skywars join $arena para probarla");
        } else {
            $player->sendMessage(TextFormat::RED . "Error al crear la arena");
        }
        
        unset($this->setupSessions[$player->getName()]);
    }
    
    public function cancelSetup(Player $player): void {
        unset($this->setupSessions[$player->getName()]);
        $player->sendMessage(TextFormat::YELLOW . "Setup cancelado");
    }
}
