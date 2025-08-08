<?php

declare(strict_types=1);

namespace CoreDelta\tutorial;

use CoreDelta\CoreDelta;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TutorialManager {
    
    private CoreDelta $plugin;
    private array $tutorials = [];
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
        $this->loadTutorials();
    }
    
    private function loadTutorials(): void {
        $this->tutorials = [
            "admin_setup" => [
                "title" => "Setup de Arena - Admin",
                "steps" => [
                    1 => "Paso 1: Usa /skywars create <nombre> para crear una nueva arena",
                    2 => "Paso 2: Ve al mundo de la arena y marca los spawn points",
                    3 => "Paso 3: Usa /skywars setup spawn <arena> para configurar spawn points",
                    4 => "Paso 4: Marca el cofre central con /skywars setup chest <arena>",
                    5 => "Paso 5: Activa la arena con /skywars enable <arena>"
                ]
            ],
            "player_guide" => [
                "title" => "Guía de Juego - Jugador",
                "steps" => [
                    1 => "Paso 1: Usa /skywars list para ver arenas disponibles",
                    2 => "Paso 2: Usa /skywars join <arena> para unirte a un juego",
                    3 => "Paso 3: Espera a que el juego inicie automáticamente",
                    4 => "Paso 4: Consigue items de los cofres y elimina a otros jugadores",
                    5 => "Paso 5: ¡Sé el último en pie para ganar!"
                ]
            ],
            "first_time" => [
                "title" => "Primera Vez en Skywars",
                "steps" => [
                    1 => "Bienvenido a Skywars! Tu objetivo es ser el último jugador vivo",
                    2 => "Cada jugador empieza en una isla separada",
                    3 => "Busca cofres para obtener items y armas",
                    4 => "Construye puentes para llegar a otras islas",
                    5 => "Elimina a otros jugadores y sé el último en pie"
                ]
            ]
        ];
    }
    
    public function showTutorial(Player $player, string $tutorialType): void {
        if (!isset($this->tutorials[$tutorialType])) {
            return;
        }
        
        $tutorial = $this->tutorials[$tutorialType];
        $player->sendMessage(TextFormat::GREEN . "=== " . $tutorial["title"] . " ===");
        
        foreach ($tutorial["steps"] as $step => $instruction) {
            $player->sendMessage(TextFormat::YELLOW . $instruction);
        }
    }
    
    public function showStep(Player $player, string $tutorialType, int $step): void {
        if (!isset($this->tutorials[$tutorialType]) || !isset($this->tutorials[$tutorialType]["steps"][$step])) {
            return;
        }
        
        $instruction = $this->tutorials[$tutorialType]["steps"][$step];
        $player->sendMessage(TextFormat::AQUA . "[TUTORIAL] " . $instruction);
    }
    
    public function getAvailableTutorials(): array {
        return array_keys($this->tutorials);
    }
}
