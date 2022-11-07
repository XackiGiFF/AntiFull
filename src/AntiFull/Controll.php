<?php
namespace AntiFull;
use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\permission\ServerOperator;

class Controll implements Listener {

    private $plugin;


    public function __construct(AntiFullNew $plugin) {
        $this->plugin = $plugin;
    }

    public function AntiFullNew(PlayerInteractEvent $event) {
        $item = $event->getItem()->getId();
        if( ($this->plugin->isItemDisabled($item)) &&
            ($event->getPlayer()->hasPermission("antifullnew.protect"))) {
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
               $this->plugin->db->exec("INSERT INTO disable (name, item, world, location, ip) VALUES ('$name', '$i', '$world', '$loc', '$ip');");
       } elseif ( ($this->plugin->isItemDisabled($item))){
       $world = $event->getPlayer()->getLevel()->getName();
        $block = $event->getBlock();
        $loc = $block->getX().",".$block->getY().",".$block->getZ();
        $name = $event->getPlayer()->getName();
        $i = $event->getItem()->getName();
        $ip = $event->getPlayer()->getAddress();
               $this->plugin->db->exec("INSERT INTO enable (name, item, world, location, ip) VALUES ('$name', '$i', '$world', '$loc', '$ip');");
      }
    }
}