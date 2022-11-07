<?php

namespace AntiFull;
use SQLite3;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class AntiFullNew extends PluginBase implements Listener {

    public $db;
    private $disableItems = array();
    public $settings;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->settings = $this->getConfig()->getAll();
        $disabledItems = $this->getConfig()->get("disabled-items");
        foreach($disabledItems as $disableItem){
            $this->disableItems[]=$disableItem;
        }

        $this->db = new SQLite3($this->getDataFolder() . "AntiFullNewBaza.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS enable (name varchar(60), item varchar(60), world varchar(60), location varchar (10000), ip varchar(10000));");


        $this->db->exec("CREATE TABLE IF NOT EXISTS disable (name varchar(60), item varchar(60), world varchar(60), location varchar (10000), ip varchar(10000));");

        $this->getServer()->getPluginManager()->registerEvents(new Controll($this), $this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function isItemDisabled($item) {
        return in_array($item, $this->disableItems, true);
    }

    public function msg($msg) {
        return TextFormat::GRAY . "[" . TextFormat::GOLD . "AntiFullNew" .
        TextFormat::GRAY . "] " . TextFormat::WHITE . $msg;
    }
}