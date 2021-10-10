<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use Tungsten\SkillAnimate\Events\ChakraGenerateEvent;
use Tungsten\SkillAnimate\Events\SkillExecuteEvent;
use Tungsten\SkillAnimate\Events\SkillOnHandEvent;
use Tungsten\SkillAnimate\SkillAnimate;

class MainEventListener implements Listener
{
    public $sa;

    public $playerName = [];

    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }

    public function InteractEvent(PlayerInteractEvent $ev): void
    {
        $player = $ev->getPlayer();
        if ($ev->getItem()->getId() == 500) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa, $player, "GaraProtection"));
        } else if ($ev->getItem()->getId() == 501) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa, $player, "GroundBadabum"));
        } else if ($ev->getItem()->getId() == 502) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa, $player, "SoulHand"));
        } else if ($ev->getItem()->getId() == 503) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa, $player, "WispsSpawner"));
        } else if ($ev->getItem()->getId() == 504) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa, $player, "StickyFluid"));
        } else if ($ev->getItem()->getId() == 505) {
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillExecuteEvent($this->sa, $player, "ChasingFluid"));
        }

    }

    public function onJoin(PlayerJoinEvent $ev)
    {
        $player = $ev->getPlayer();
        $this->sa->getServer()->getPluginManager()->callEvent(new ChakraGenerateEvent($this->sa, $player));
        if (array_key_exists($player->getName(), $this->playerName)) {
            unset($this->playerName[$player->getName()]);
        }
    }

    public function onQuit(PlayerQuitEvent $ev)
    {
        $player = $ev->getPlayer();
        $this->sa->database->saveConfig($player);
    }
    /** list of player who is setting a new piano*/
    //Look like ["name" => [x,y,z],]
    public function onHeld(PlayerItemHeldEvent $ev)
    {
        $id = $ev->getItem()->getId();
        if (in_array($id, $this->sa->skillIdItem)) {
            $player = $ev->getPlayer();
            if ($id == 504) {
                $this->sa->getServer()->getPluginManager()->callEvent(new SkillOnHandEvent($this->sa, $player, "StickyFluid"));
            }
            if ($id == 505) {
                $this->sa->getServer()->getPluginManager()->callEvent(new SkillOnHandEvent($this->sa, $player, "ChasingFluid"));

            }
        }

    }

    public function onBreak(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        if (array_key_exists($player->getName(), $this->playerName)) {
            $name = $player->getName();
            $block = $ev->getBlock();
            $num = count($this->playerName[$name]) + 1 - 1; //minus for the [2] in the commands
            $player->sendMessage("§aBlock $num is set");
            if ($num >= 2) {
                $this->playerName[$name][$num - 1] = [$block->x, $block->y, $block->z];

                $x1 = $this->playerName[$name][0][0];
                $x2 = $this->playerName[$name][1][0];
                $y1 = $this->playerName[$name][0][1];
                $y2 = $this->playerName[$name][1][1];
                $z1 = $this->playerName[$name][0][2];
                $z2 = $this->playerName[$name][1][2];
                $config = $this->sa->safeArea;
                $level = $player->getLevel()->getName();
                $areaName = $this->playerName[$name][2];

                //+1 because pocketmine get the lower value of a block coordinate
                $config->setNested("$level.$areaName.x", ($x1 <= $x2) ? [$x1, $x2 + 1] : [$x2, $x1 + 1]);
                $config->setNested("$level.$areaName.y", ($y1 <= $y2) ? [$y1, $y2 + 1] : [$y2, $y1 + 1]);
                $config->setNested("$level.$areaName.z", ($z1 <= $z2) ? [$z1, $z2 + 1] : [$z2, $z1 + 1]);
                $config->save();
                $player->sendMessage("§aFinishing adding a area at level §b$level §anamed §b$areaName §a!");
                $ev->setCancelled();
                unset($this->playerName[$name]);
                return;
            }
            $this->playerName[$name][$num - 1] = [$block->x, $block->y, $block->z];
            $ev->setCancelled();
        }
    }

    public function onWorldChange(EntityLevelChangeEvent $ev)
    {
        $originLevel = $ev->getOrigin()->getName();
        if ($originLevel == $this->sa->pvpWorldName) {
            $player = $ev->getEntity();
            if (!$player instanceof Player) {
                return;
            }
            $skillItem = $this->sa->skillIdItem;
            foreach ($skillItem as $number) {
                if ($player->getInventory()->contains(Item::get($number))) {
                    $player->getInventory()->remove(Item::get($number));
                }

            }
        }
    }

    public function onDrop(PlayerDropItemEvent $ev)
    {
        $item = $ev->getItem();
        $player = $ev->getPlayer();
        if (in_array($item->getId(), $this->sa->skillIdItem)) {
            $ev->setCancelled();
            $ev->getPlayer()->sendMessage("§6Không được vứt item này bro");
        }
    }
}