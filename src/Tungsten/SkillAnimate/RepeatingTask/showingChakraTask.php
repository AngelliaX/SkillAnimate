<?php

namespace Tungsten\SkillAnimate\RepeatingTask;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\SkillAnimate;

class showingChakraTask extends Task implements Listener
{

    public $pvpWorldName;
    public $skillIdItem = [];
    /** @var SkillAnimate */
    private $sa;
    /**
     * @var array
     * return list of player is being showed chakra
     */
    private $list = [];
    /**
     * @var array
     * return list of player that is in pvp world
     */
    private $isOnPvPWorld = [];

    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
        $this->pvpWorldName = $sa->pvpWorldName;
        $this->skillIdItem = $sa->skillIdItem;
    }


    public function onRun($tick)
    {
        foreach ($this->list as $player) {
            if ($player instanceof Player) {
                $Chakra = $player->namedtag->getFloat("Chakra");
                $maxChakra = $player->namedtag->getInt("maxChakra");
                #$player->sendTip("§bChakra: §a".round($Chakra,0)."§e/§a" . "$maxChakra");
                $addChak = $player->namedtag->getFloat("ChakraHealPerSec");
                if ($Chakra < $maxChakra) {
                    if($Chakra + $addChak >= $maxChakra){
                        $player->namedtag->setFloat("Chakra", $maxChakra);
                    }else{
                        $player->namedtag->setFloat("Chakra", $Chakra + $addChak);
                    }
                    $Chakra = $player->namedtag->getFloat("Chakra");
                    $player->sendTip("§bChakra: §a".round($Chakra,0)."§e/§a" . "$maxChakra");
                    $this->playMusic($player, "random.levelup");
                }
            }
        }
    }

    public function playMusic(Player $player, string $soundName)
    {
        $sound = new PlaySoundPacket();
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 0.0025;
        $sound->pitch = 1;
        $sound->soundName = $soundName;
        SkillAnimate::$instance->getServer()->broadcastPacket([$player], $sound);
    }

    public function onHeld(PlayerItemHeldEvent $ev): void
    {
        $name = $ev->getPlayer()->getName();
        if (isset($this->isOnPvPWorld[$name])) return;
        if (in_array($ev->getItem()->getId(), $this->skillIdItem)) {
            $this->list[$ev->getPlayer()->getName()] = $ev->getPlayer();
        } else {
            unset($this->list[$ev->getPlayer()->getName()]);
        }
    }
    public function onTap(PlayerInteractEvent $ev){
        $name = $ev->getPlayer()->getName();
        if (isset($this->isOnPvPWorld[$name])) return;
        if(isset($this->list[$name])) return;
        if (in_array($ev->getItem()->getId(), $this->skillIdItem)) {
            $this->list[$ev->getPlayer()->getName()] = $ev->getPlayer();
        }
    }
    public function changeWorld(EntityLevelChangeEvent $ev): void
    {
        if (!$ev->getEntity() instanceof Player) return;
        $name = $ev->getEntity()->getName();
        if ($ev->getTarget()->getName() == $this->pvpWorldName) {
            $this->isOnPvPWorld[$name] = "yes";
            //TODO check bug neu object nay ko dc thay doi bthg
            $this->list[$name] = $ev->getEntity();
        } else {
            if (isset($this->isOnPvPWorld[$name])) {
                unset($this->isOnPvPWorld[$name]);
                unset($this->list[$name]);
            }
        }
    }

    public
    function onQuit(PlayerQuitEvent $ev)
    {
        if (!isset($this->list[$ev->getPlayer()->getName()])) return;
        unset($this->list[$ev->getPlayer()->getName()]);
        unset($this->isOnPvPWorld[$ev->getPlayer()->getName()]);
    }
}