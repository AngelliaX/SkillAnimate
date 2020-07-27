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
    /**
     * timer for $isSendTip;
     */
    private $timer = 0;
    private $isSendTip;

    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
        $this->pvpWorldName = $sa->pvpWorldName;
        $this->skillIdItem = $sa->skillIdItem;
    }


    public function onRun($tick)
    {
        $this->timer += $this->getHandler()->getPeriod();
        if ($this->timer >= 60) {
            $this->isSendTip = true;
            $this->timer = 0;
        }
        foreach ($this->list as $player) {
            if ($player instanceof Player) {
                $config = $this->sa->database->getConfig($player);
                $Chakra = $config->getNested("Chakra");
                $maxChakra = $config->getNested("maxChakra");
                #$player->sendTip("§bChakra: §a".round($Chakra,0)."§e/§a" . "$maxChakra");
                $addChak = $config->getNested("ChakraHealPerSec");
                if ($Chakra < $maxChakra) {
                    if ($Chakra + $addChak >= $maxChakra) {
                        $config->setNested("Chakra", $maxChakra);
                    } else {
                        $config->setNested("Chakra", $Chakra + $addChak);
                    }
                    $Chakra = $config->getNested("Chakra");
                    if ($this->isSendTip) {
                        $player->sendTip("§bMana: §f" . round($Chakra, 0) . "§f/§f" . "$maxChakra");
                    }
                    #$this->playMusic($player, "random.levelup");
                }
            }
        }
        if ($this->isSendTip) {
            $this->isSendTip = false;
        }
    }

    public function onHeld(PlayerItemHeldEvent $ev): void
    {
        $name = $ev->getPlayer()->getName();
        if (isset($this->isOnPvPWorld[$name])) return;
        if (in_array($ev->getItem()->getId(), $this->skillIdItem)) {
            $this->list[$name] = $ev->getPlayer();
        } else {
            if (array_key_exists($name, $this->list)) {
                unset($this->list[$name]);
            }
        }
    }

    public function onTap(PlayerInteractEvent $ev)
    {
        $name = $ev->getPlayer()->getName();
        if (isset($this->isOnPvPWorld[$name])) return;
        if (isset($this->list[$name])) return;
        if (in_array($ev->getItem()->getId(), $this->skillIdItem)) {
            $this->list[$name] = $ev->getPlayer();
        }
    }

    public function changeWorld(EntityLevelChangeEvent $ev): void
    {
        if (!$ev->getEntity() instanceof Player) return;
        $name = $ev->getEntity()->getName();
        if ($ev->getTarget()->getName() == $this->pvpWorldName) {
            if ($ev->getEntity() instanceof Player) {
                var_dump("oka");
                $this->isOnPvPWorld[$name] = "yes";
                $this->list[$name] = $ev->getEntity();
            }
        } else {
            if (isset($this->isOnPvPWorld[$name])) {
                unset($this->isOnPvPWorld[$name]);
                unset($this->list[$name]);
            }
        }
    }

    public function onQuit(PlayerQuitEvent $ev)
    {
        if (!isset($this->list[$ev->getPlayer()->getName()])) return;
        unset($this->list[$ev->getPlayer()->getName()]);
        unset($this->isOnPvPWorld[$ev->getPlayer()->getName()]);
    }

    private function playMusic(Player $player, string $soundName)
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
}