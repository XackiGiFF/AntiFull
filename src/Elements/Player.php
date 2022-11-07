<?php
namespace Elements;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
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


class Player implements Listener {

    private $plugin;


    public function __construct(LimitedCreative $plugin) {
        $this->plugin = $plugin;
    }
    public function onBlockPlace(BlockPlaceEvent $event) {
        $world = $event->getPlayer()->getLevel()->getName();
        $block = $event->getBlock();
        $loc = $block->getX().",".$block->getY().",".$block->getZ();

        if( ($event->getPlayer()->getGamemode() == 1) &&
            ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
            if($this->plugin->isItemDisabled($event->getBlock()->getId())) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->plugin->msg("Ты не можешь поставить блок ".$event->getBlock()->getName().
                    " в креативе"));
            } else {
                $this->plugin->db->exec("INSERT INTO blocks (world, location) VALUES ('$world', '$loc');");
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
           $event->getPlayer()->close( TextFormat::GRAY . "[" . TextFormat::GOLD . "AntiFull" .
        TextFormat::GRAY . "] " . TextFormat::WHITE . 
"Игрок " .$event->getPlayer()->getName(). " был выкинут за использование ".$event->getItem()->getName(). " по координатам (" .$event->getBlock()->getX().",".$event->getBlock()->getY().",".$event->getBlock()->getZ().")","§7[§eAntiFull§7]§c§oТы был выкинут за\n §b§oиспользование запретных вещей!!");
        }
    }

    public function terract(PlayerInteractEvent $event) {
        $item = $event->getItem()->getId();
        if( ($event->getPlayer()->getGamemode() == 0) && ($this->plugin->isItemDisabled($item)) &&
            ($event->getPlayer()->hasPermission("limitedcreative.permission.creative"))) {
            $event->setCancelled(true);
            $event->getPlayer()->sendMessage($this->plugin->
            msg("Ты не можешь использовать " .$event->getItem()->getName().". Это запретная вещь!!!"));
          $event->getPlayer()->close( TextFormat::GRAY . "[" . TextFormat::GOLD . "AntiFull" .
        TextFormat::GRAY . "] " . TextFormat::WHITE . 
"Игрок " .$event->getPlayer()->getName(). " был выкинут за использование ".$event->getItem()->getName(). " по координатам (" .$event->getBlock()->getX().",".$event->getBlock()->getY().",".$event->getBlock()->getZ().")","§7[§eAntiFull§7]§c§oТы был выкинут за\n §b§oиспользование запретных вещей!!");
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