<?php
namespace basprohop;
use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\inventory\InventoryOpenEvent;


class PlayerEvents implements Listener {

    private $plugin;


    public function __construct(LimitedCreative $plugin) {
        $this->plugin = $plugin;
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $item = $event->getItem()->getId();
        if( ($event->getPlayer()->getGamemode() == 1) && ($this->plugin->isItemDisabled($item)) &&
            ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
            $event->setCancelled(true);
            $event->getPlayer()->sendMessage($this->plugin->
            msg("A(z) " .$event->getItem()->getName()." tilos kreatívban!"));
        }
    }
	
	public function onPlayerItemConsume(PlayerItemConsumeEvent $event) {
        $item = $event->getItem()->getId();
        if( ($event->getPlayer()->getGamemode() == 1) && ($this->plugin->isItemDisabled($item)) &&
            ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
            $event->setCancelled(true);
            $event->getPlayer()->sendMessage($this->plugin->
            msg("A(z) " .$event->getItem()->getName()." tilos kreatívban!"));
        }
    }

    public function onDrop(PlayerDropItemEvent $event){
	    if( ($event->getPlayer()->getGamemode() == 1) && ($event->getPlayer()->hasPermission("limitedcreative.permission.creative")))
            $event->setCancelled(true);
    }

	public function onInventoryOpenEvent(InventoryOpenEvent $event) {
		if( ($event->getPlayer()->getGamemode() == 1) && ($event->getPlayer()->hasPermission("limitedcreative.permission.creative")))
            $event->setCancelled(true);
	}

    public function onGameModeChange(PlayerGameModeChangeEvent $event) {
        $content = $event->getPlayer()->getInventory()->getContents();
        $event->getPlayer()->getInventory()->setContents($content);

        if( ($event->getNewGamemode() == 1)
            && ($event->getPlayer()->hasPermission("limitedcreative.permission.creative")));
            else if (($event->getNewGamemode() == 0)
            && ($event->getPlayer()->hasPermission("limitedcreative.permission.creative")));

        if( ($this->plugin->settings["reset-inventory"])
            && ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
            $event->getPlayer()->getInventory()->clearAll();
        }
    }

    public function onEntityDamage(EntityDamageEvent $event) {
        if ($event instanceof EntityDamageByEntityEvent) {

            $entity = $event->getEntity(); //Victim
            $player = $event->getDamager(); //Attacker

            if ( ($player instanceof Player) && ($entity instanceof Player)
                && ($player->hasPermission("limitedcreative.permission.creative"))) {
                if ( (($player->getGamemode() == 1) && ($entity->getGamemode() == 0))
                    && $this->plugin->settings["disable-pvp-damage"]) {

                    $player->sendMessage($this->plugin->msg("A PvP tilos kreatívban!"));
                    $event->setCancelled(true);
                }
            } else if ( ($player instanceof Player)
                && ($player->hasPermission("limitedcreative.permission.creative"))) {
                if( ($player->getGamemode() == 1) && ($this->plugin->settings["disable-entity-damage"]) ) {
                    $player->sendMessage($this->plugin->msg("Nem üthetsz kreatívban!"));
                    $event->setCancelled(true);
                }
            }
        }
    }

    public function onPickupItem(InventoryPickupItemEvent $event) {
        $player = $event->getInventory()->getHolder();
        if($player instanceof Player){
            if( ($player->getGamemode() == 1) && ($this->plugin->settings["disable-item-pickup"])
                && ($player->hasPermission("limitedcreative.permission.creative"))) {
                $player->sendPopup($this->plugin->msg("Nem vehetsz fel tárgyakat kreatívban!"));
                $event->setCancelled(true);
            }
        }
    }

}