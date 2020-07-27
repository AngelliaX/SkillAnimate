<?php

namespace Tungsten\SkillAnimate\Database;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use pocketmine\utils\Config;
use Tungsten\SkillAnimate\SkillAnimate;

class YamlDatabase{
    private $sa;

    /** Config $config */
    /** players' config */
    private $config;
	public function __construct(SkillAnimate $sa){
		$this->sa = $sa;
	}
	public function addConfig(Player $player){
	    $name = $player->getName();
	    if(!file_exists($this->sa->getDataFolder()."player/"."$name.yml")){
	        @mkdir($this->sa->getDataFolder()."player/");
	        $config = new Config($this->sa->getDataFolder()."player/"."$name.yml");
	        $config->setNested("Chakra",300);
	        $config->setNested("maxChakra",300);
	        $config->setNested("ChakraHealPerSec",0.05);
	        $this->config[$name] = $config;
	        return;
        }
	    $this->config[$name] = new Config($this->sa->getDataFolder()."player/"."$name.yml");
    }
    public function getConfig(Player $player) : Config{
	    return $this->config[$player->getName()];
    }
    public function saveConfig(Player $player){
	    $name = $player->getName();
	    if(!isset($this->config[$name])) return;
	    $this->config[$name]->save();
	    unset($this->config[$name]);
    }
    public function saveAll(){
        if($this->config == []) return;
	    foreach($this->config as $config){
            $config->save();
        }
    }
}
