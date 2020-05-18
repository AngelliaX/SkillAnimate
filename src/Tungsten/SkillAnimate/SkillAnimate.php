<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use Tungsten\SkillAnimate\SkillContainer\GaraProtection;
class SkillAnimate extends PluginBase implements Listener
{
    /** @var  */
    public static $instance;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        self::$instance = $this;
    }
    public function InteractEvent(PlayerInteractEvent $ev): void
    {
        $player = $ev->getPlayer();
        var_dump($player->namedtag);

        return;
        if ($ev->getItem()->getId() == 500) {
            new GaraProtection($this,$player);
        }

    }
}