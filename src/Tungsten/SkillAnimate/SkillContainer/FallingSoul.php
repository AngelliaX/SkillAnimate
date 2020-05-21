<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\SkillContainer;

use pocketmine\math\Vector3;
use pocketmine\Player;
use Tungsten\SkillAnimate\AnimateController\destroyBlockTask;
use Tungsten\SkillAnimate\AnimateController\spawnBlockDelayedTask;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;


class FallingSoul
{
    public $distance = 30;
    public $width = 5;
    private $damage = 3;
    public $destroytime = 5*20;
    public function __construct(SkillAnimate $sa, Player $player,array $customData = null)
    {
        //TODO lam phan customdata (moi player co the dung skill vs distanct khac nhau,v.v
        if($customData != null){
            $this->distance = $customData[0];
            $this->width = $customData[1];
            $this->damage = $customData [2];
        }
        $this->spawnBlock($sa, $player);
    }

    public function spawnBlock(SkillAnimate $sa, Player $player): void
    {
        $pos = new Vector3($player->getX(), $player->getY(), $player->getZ());
        $level = $player->getLevel();
        //dong 0 nam 1  bac 3 tay 2
        $direc = $player->getDirection();

        $tick = 2;

        $tempZ = -1;
        for ($x = 2; $x <= $this->distance; $x++) {
            if ($tempZ < $this->width) {
                $tempZ++;
            }
            if ($x > $this->width + 1) {
                if ($tempZ % 2 == 0) {
                    $tempZ--;
                } else {
                    $tempZ++;
                }
            }
            for ($z = 0; $z <= $tempZ; $z++) {
                if (rand(0, 3) == 0) $tick++;
                $y = (rand(0, 15) == 0) ? 1 : 0;
                $position = $this->PosCorrection($direc, $pos, $x, $y, $z);

                if ($level->getBlock($position)->getId() == 0) {
                    $task = new blockPersonalTask($sa, $player, $position, $level, "GroundBadabum", $this->destroytime);
                    $sa->getScheduler()->scheduleRepeatingTask($task, 1);
                }

                $blockData = [(rand(0, 6) == 0) ? 179 : 24, 15];
                $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData, "dig.grass"), $tick);
                $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "dig.grass"), $tick + $this->destroytime);

                $position = $this->PosCorrection($direc, $pos, $x, $y, -$z);
                $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData, "dig.grass"), $tick);
                $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "dig.grass"), $tick + $this->destroytime);

            }
            for ($z = -1; $z <= 1; $z++) {
                $position = $this->PosCorrection($direc, $pos, -2, 0, $z);
                if ($level->getBlock($position)->getId() == 0) {
                    $task = new blockPersonalTask($sa, $player, $position, $level, "GroundBadabum", $this->destroytime);
                    $sa->getScheduler()->scheduleRepeatingTask($task, 1);
                }
                $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData, "dig.grass"), $tick);
                $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "dig.grass"), $tick + $this->destroytime);
            }


        }

    }

    public function PosCorrection(int $direc, Vector3 $pos, int $x, int $y, int $z): Vector3
    {
        if ($direc == 0) {
            return $pos->add($x, $y, $z);
        } else if ($direc == 1) {
            return $pos->add($z, $y, $x);
        } else if ($direc == 2) {
            return $pos->add(-$x, $y, $z);
        } else {
            return $pos->add($z, $y, -$x);
        }
    }
}