<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\SkillContainer;

use pocketmine\math\Vector3;
use pocketmine\Player;
use Tungsten\SkillAnimate\AnimateController\destroyBlockTask;
use Tungsten\SkillAnimate\AnimateController\spawnBlockDelayedTask;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;


class GaraProtection
{

    public function __construct(SkillAnimate $sa, Player $player)
    {
        $this->spawnBlock($sa, $player);
    }

    public function spawnBlock(SkillAnimate $sa, Player $player): void
    {
        $pos = new Vector3($player->getX(), $player->getY(), $player->getZ());
        $level = $player->getLevel();
        //dong 0 nam 1  bac 3 tay 2
        $direc = $player->getDirection();

        $tick = 2;
        $destroytime = 5 * 20;

        $maxX = 6;
        $maxY = 6;
        $maxZ = 6;
        $minX = -6;
        $minY = -6;
        $minZ = -6;
        $radiusX = ($maxX - $minX) / 2;
        $radiusY = ($maxY - $minY) / 2;
        $radiusZ = ($maxZ - $minZ) / 2;

        $centerX = $minX + $radiusX;
        $centerY = $minY + $radiusY;
        $centerZ = $minZ + $radiusZ;

        for ($x = $maxX; $x >= $minX; $x--) {
            $xs = ($x - $centerX) ** 2 / $radiusX ** 2;
            for ($y = $maxY; $y >= $minY; $y--) {
                $ys = ($y - $centerY) ** 2 / $radiusY ** 2;
                for ($z = $maxZ; $z >= $minZ; $z--) {
                    $zs = ($z - $centerZ) ** 2 / $radiusZ ** 2;
                    if ($xs + $ys + $zs <= 1.0) {
                        if (true) {
                            if ($xs + $ys + $zs < 0.7) {
                                continue;
                            }
                        }

                        $position = $this->PosCorrection($direc,$pos,$x,$y,$z);

                        if($level->getBlock($position)->getId() == 0){
                            $task = new blockPersonalTask($sa,$player,$position,$level,"GaraProtection", $destroytime);
                            $sa->getScheduler()->scheduleRepeatingTask($task,1);
                        }

                        if (rand(0, 3) != 0) {
                            $blockData = [24, 15];
                            $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData, "dig.sand"), $tick);
                            $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "dig.sand"), $tick + $destroytime);
                        } else {
                            $blockData = [20, 15];
                            if (rand(0, 4) == 0) {
                                $tick += 1;
                            }
                            if(rand(0,10) == 0){
                                $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData, "random.glass"), $tick);
                                $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "random.glass"), $tick + $destroytime);
                                continue;
                            }
                            $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData), $tick);
                            $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData), $tick + $destroytime);

                        }

                    }
                }
            }
        }
    }
    public function PosCorrection(int $direc,Vector3 $pos,int $x ,int $y, int $z) : Vector3{
        if ($direc == 0) {
            return $pos->add($x,$y,$z);
        } else if ($direc == 1) {
            return $pos->add($z,$y,$x);
        } else if ($direc == 2) {
            return $pos->add(-$x,$y,$z);
        } else {
            return $pos->add($z,$y,-$x);
        }
    }
}