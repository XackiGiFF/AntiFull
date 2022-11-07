<?php
namespace AntiFull;
use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\permission\ServerOperator;

class Controll implements Listener {

    private $plugin;


    public function __construct(AntiFullNew $plugin) {
        $this->plugin = $plugin;
    }
    public function onBlockPlace(BlockPlaceEvent $event) {
        $world = $event->getPlayer()->getLevel()->getName();
        $block = $event->getBlock();
        $loc = $block->getX().",".$block->getY().",".$block->getZ();
        $name = $event->getPlayer()->getName();
        $i = $event->getItem()->getName();
        if( ($event->getPlayer()->getGamemode() == 1) &&
            ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
            if($this->plugin->isItemDisabled($event->getBlock()->getId())) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->plugin->msg("Ты не можешь поставить блок ".$event->getBlock()->getName().
                    " в креативе"));
            } 
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $item = $event->getItem()->getId();
        if( ($event->getPlayer()->getGamemode() == 1) && ($this->plugin->isItemDisabled($item)) &&
            ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
            $event->setCancelled(true);
            $event->getPlayer()->sendMessage($this->plugin->
            msg("Ты не можешь использовать " .$event->getItem()->getName()." в креативе!"));
			Server::getInstance()->broadcastMessage("§l§d[§aAntiFullNew§d] §bСистема защиты обнаружила атаку!\n" . "§l§d[§aAntiFullNew§d]§b Игрок:§1 " . $event->getPlayer()->getName() . "\n" . "§l§d[§aAntiFullNew§d]§cIP: " . $event->getPlayer()->getAddress() . "\n" . "§l§d[§aAntiFullNew§d]§e Вещь:§6 " . $event->getItem()->getName());
        $world = $event->getPlayer()->getLevel()->getName();
        $block = $event->getBlock();
        $loc = $block->getX().",".$block->getY().",".$block->getZ();
        $name = $event->getPlayer()->getName();
        $i = $event->getItem()->getName();
        $ip = $event->getPlayer()->getAddress();
               $this->plugin->db->exec("INSERT INTO blocks (name, item, world, location, ip) VALUES ('$name', '$i', '$world', '$loc', '$ip');");
       }
    }

    public function onBlockUpdate(BlockUpdateEvent $event) {
        if($this->plugin->settings["disable-block-updates"]) {
            $block = $event->getBlock();
            $world = $block->getLevel()->getName();
            $loc = $block->getX() . "," . $block->getY() . "," . $block->getZ();

            $count = $this->plugin->db->querySingle
            ("SELECT COUNT(*) as count FROM blocks WHERE ( (world = '$world') AND (location = '$loc'));");
            if ($count > 0) {
                $event->setCancelled(true);
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        if($event->getPlayer()->getGamemode() == 0) {
            $block = $event->getBlock();
            $world = $block->getLevel()->getName();
            $loc = $block->getX().",".$block->getY().",".$block->getZ();
            $count = $this->plugin->db->querySingle
            ("SELECT COUNT(*) as count FROM blocks WHERE (world = '$world' AND location = '$loc');");
            if ($count > 0) {
                if($this->plugin->settings["disable-item-drop"]) {
                    $event->setDrops(array(Item::get(0)));
                }
                $this->plugin->db->exec("DELETE FROM blocks WHERE (world = '$world' AND location = '$loc');");
            }
        }
    }

    public function onExplode(EntityExplodeEvent $event) {
        if($this->plugin->settings["disable-explosion-damage"]) {
            foreach ($event->getBlockList() as $block) {
                $world = $block->getLevel()->getName();
                $loc = $block->getX() . "," . $block->getY() . "," . $block->getZ();
                $count = $this->plugin->db->querySingle
                ("SELECT COUNT(*) as count FROM blocks WHERE (world = '$world' AND location = '$loc');");
                if ($count > 0) {
                    $event->setCancelled(true);
                }
            }
        }
    }


    public function onGameModeChange(PlayerGameModeChangeEvent $event) {
        $content = $event->getPlayer()->getInventory()->getContents();
        $event->getPlayer()->getInventory()->setContents($content);

        if( ($event->getNewGamemode() == 1)
            && ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
        } else if (($event->getNewGamemode() == 0)
            && ($event->getPlayer()->hasPermission("limitedcreative.permission.creative")) ){
        }

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

                    $player->sendMessage($this->plugin->msg("Ты не можешь драться в креативе!"));
                    $event->setCancelled(true);
                }
            } else if ( ($player instanceof Player)
                && ($player->hasPermission("limitedcreative.permission.creative"))) {
                if( ($player->getGamemode() == 1) && ($this->plugin->settings["disable-entity-damage"]) ) {
                    $player->sendMessage($this->plugin->msg("Ты не можешь аттаковать в креативе!"));
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
                $player->sendPopup($this->plugin->msg("Ты не можешь поднять предмет в креативе!"));
                $event->setCancelled(true);
            }
        }
    }

}