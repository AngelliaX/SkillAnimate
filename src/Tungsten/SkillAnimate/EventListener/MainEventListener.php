<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;
use onebone\economyapi\event\money\AddMoneyEvent;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;

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

    }
    public function onJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();
        if($player->namedtag->hasTag("Chakra",FloatTag::class)) return;
        $nbt1 = new FloatTag("Chakra",300);
        $nbt2 = new IntTag("maxChakra",300);
        $nbt3 = new FloatTag("ChakraHealPerSec",5);
        $player->namedtag->setTag($nbt1);
        $player->namedtag->setTag($nbt2);
        $player->namedtag->setTag($nbt3);
    }
}