<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;
use onebone\economyapi\event\money\AddMoneyEvent;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\event\player\PlayerQuitEvent;
use Tungsten\SkillAnimate\Events\ChakraGenerateEvent;
use Tungsten\SkillAnimate\Events\SkillExecuteEvent;
use Tungsten\SkillAnimate\Events\SkillOnHandEvent;
use Tungsten\SkillAnimate\SkillAnimate;

use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;

use Tungsten\SkillAnimate\SkillContainer\GaraProtection;
class MainEventListener implements Listener
{
    public $sa;
    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }

    public function InteractEvent(PlayerInteractEvent $ev): void
    {
        $player = $ev->getPlayer();
        if ($ev->getItem()->getId() == 500) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa,$player,"GaraProtection"));
        }else if ($ev->getItem()->getId() == 501) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa,$player,"GroundBadabum"));
        }else if ($ev->getItem()->getId() == 502) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa,$player,"SoulHand"));
        }else if ($ev->getItem()->getId() == 503) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa,$player,"WispsSpawner"));
        }else if ($ev->getItem()->getId() == 504) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa,$player,"StickyFluid"));
        }

    }
    public function onJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();
        $this->sa->getServer()->getPluginManager()->callEvent(new ChakraGenerateEvent($this->sa,$player));
    }
    public function onQuit(PlayerQuitEvent $ev){
        $player = $ev->getPlayer();
        $this->sa->database->saveConfig($player);
    }
    public function onHeld(PlayerItemHeldEvent $ev){
        $id = $ev->getItem()->getId();
        if(in_array($id,$this->sa->skillIdItem)){
            $player = $ev->getPlayer();
            if($id == 504){
                $this->sa->getServer()->getPluginManager()->callEvent(new SkillOnHandEvent($this->sa,$player,"StickyFluid"));
            }if($id == 505){
                //ChasingFluid

            }
        }

    }
}