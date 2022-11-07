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

        $disabledItems = $this->getConfig()->get("disabled-items");
        foreach($disabledItems as $disableItem){
            $this->disableItems[]=$disableItem;
        }

        $this->getServer()->getPluginManager()->registerEvents(new PlayerEvents($this), $this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function isItemDisabled($item) {
        return in_array($item, $this->disableItems, true);
    }

    public function msg($msg) {
        return TextFormat::GRAY . "[" . TextFormat::GOLD . "LimitedCreative" .
        TextFormat::GRAY . "] " . TextFormat::WHITE . $msg;
    }
}