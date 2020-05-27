<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;
use onebone\economyapi\event\money\AddMoneyEvent;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\event\player\PlayerQuitEvent;
use Tungsten\SkillAnimate\Events\ChakraGenerateEvent;
use Tungsten\SkillAnimate\Events\SkillExecuteEvent;
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
        }
        if ($ev->getItem()->getId() == 501) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa,$player,"GroundBadabum"));
        }
        if ($ev->getItem()->getId() == 502) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa,$player,"SoulHand"));
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

}