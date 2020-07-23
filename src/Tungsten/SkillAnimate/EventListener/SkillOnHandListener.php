<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\Events\SkillOnHandEvent;
use Tungsten\SkillAnimate\SkillAnimate;

class SkillOnHandListener extends Task implements Listener
{
    public $sa;
    private $listplayersName = []; //list of players' name who are holding the skill

    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }

    public function onHeld(SkillOnHandEvent $ev)
    {
        array_push($this->listplayersName, $ev->getPlayer()->getName());
    }

    public function onRun(int $currentTick)
    {
        foreach ($this->listplayersName as $holderName) {
            $closest = "§cNo Target";
            $lastSquare = 900; //this is also the range,30 block
            $holder = $this->sa->getServer()->getPlayer($holderName);
            if($holder == null) {
                unset($this->listplayersName[array_search($holderName, $this->listplayersName)]);
                continue;
            }
            foreach ($holder->getLevel()->getPlayers() as $p) { // for every player in the sender's world
                if ($p !== $holder) {
                    $square = $holder->distanceSquared($p);
                    #var_dump($square);
                    if ($lastSquare > $square) {
                        $closest = $p;
                        $lastSquare = $square;
                    }
                }
            }
            if ($holder->getInventory()->getItemInHand()->getId() == 504) { //StickyFluid
                if ($holder instanceof Player)
                    if (!$closest instanceof Player) {
                        $holder->sendTip("Target: §6". $closest);
                        return;
                    }
                $holder->sendTip("Target: §6" . $closest->getName());
            } else if ($holder->getInventory()->getItemInHand()->getId() == 505) { //ChasingFluid

            } else {
                unset($this->listplayersName[array_search($holderName, $this->listplayersName)]);
            }
        }
    }
}