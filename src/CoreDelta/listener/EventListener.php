<?php

declare(strict_types=1);

namespace CoreDelta\listener;

use CoreDelta\CoreDelta;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\player\Player;

class EventListener implements Listener {
    
    private CoreDelta $plugin;
    
    public function __construct(CoreDelta $plugin) {
        $this->plugin = $plugin;
    }
    
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $this->plugin->getDatabaseManager()->registerPlayer($player->getName());
    }
    
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        
        foreach ($this->plugin->getGameManager()->getAllGames() as $game) {
            foreach ($game->getPlayers() as $gamePlayer) {
                if ($gamePlayer->getPlayer()->getName() === $player->getName()) {
                    $game->removePlayer($player);
                    break 2;
                }
            }
        }
    }
    
    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        
        foreach ($this->plugin->getGameManager()->getAllGames() as $game) {
            foreach ($game->getPlayers() as $gamePlayer) {
                if ($gamePlayer->getPlayer()->getName() === $player->getName()) {
                    $gamePlayer->setAlive(false);
                    
                    $cause = $player->getLastDamageCause();
                    if ($cause instanceof EntityDamageByEntityEvent) {
                        $killer = $cause->getDamager();
                        if ($killer instanceof Player) {
                            foreach ($game->getPlayers() as $killerPlayer) {
                                if ($killerPlayer->getPlayer()->getName() === $killer->getName()) {
                                    $killerPlayer->addKill();
                                    $game->broadcastMessage(str_replace(
                                        ["{killer}", "{victim}"],
                                        [$killer->getName(), $player->getName()],
                                        $this->plugin->getConfig()->getNested("messages.kill", "{prefix} {killer} killed {victim}!")
                                    ));
                                    break;
                                }
                            }
                        }
                    }
                    
                    $game->broadcastMessage(str_replace("{player}", $player->getName(), $this->plugin->getConfig()->getNested("messages.death", "{prefix} {player} died!")));
                    
                    // Respawn player
                    $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    $player->setGamemode(3); // Spectator
                    
                    if (count($game->getAlivePlayers()) <= 1) {
                        $game->end();
                    }
                    
                    break;
                }
            }
        }
    }
    
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;
        
        foreach ($this->plugin->getGameManager()->getAllGames() as $game) {
            foreach ($game->getPlayers() as $gamePlayer) {
                if ($gamePlayer->getPlayer()->getName() === $entity->getName()) {
                    if ($game->getPhase() !== 2) {
                        $event->cancel();
                    }
                    break;
                }
            }
        }
    }
    
    public function onPlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        
        foreach ($this->plugin->getGameManager()->getAllGames() as $game) {
            foreach ($game->getPlayers() as $gamePlayer) {
                if ($gamePlayer->getPlayer()->getName() === $player->getName()) {
                    $recipients = [];
                    foreach ($game->getPlayers() as $p) {
                        $recipients[] = $p->getPlayer();
                    }
                    $event->setRecipients($recipients);
                    break;
                }
            }
        }
    }
}
