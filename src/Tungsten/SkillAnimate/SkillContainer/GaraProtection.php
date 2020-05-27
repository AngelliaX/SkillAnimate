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
    private $destroyTime;
    private $distance;
    private $radius;
    public function __construct(SkillAnimate $sa, Player $player,int $destroyTime = null,int $distance = null,int $radius = null)
    {

        if(!is_null($config = $sa->database->getConfig($player)->getNested("GaraProtection"))){
            $this->destroyTime = $config["destroyTime"];
            $this->distance = $config["distance"];
            $this->radius = $config["radius"];
        }else{
            $config = $sa->skillData->getNested("GaraProtection");
            $this->destroyTime = $config["destroyTime"];
            $this->distance = $config["distance"];
            $this->radius = $config["radius"];
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
        $maxX = $this->radius;
        $maxY = $this->radius;
        $maxZ = $this->radius;
        $minX = -$this->radius;
        $minY = -$this->radius;
        $minZ = -$this->radius;
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
                            if ($xs + $ys + $zs < 0.65) {
                                continue;
                            }
                        }
                        if($y == 1){
                            if(rand(0,2) != 0){
                                continue;
                            }
                        }
                        $position = $this->PosCorrection($direc,$pos,$x,$y,$z);

                        if (rand(0, 3) != 0) {
                            $blockData = [(rand(0, 6) == 0) ? 179 : 24, 15];
                            $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData,$player,"GaraProtection",$this->destroyTime, "dig.sand",$this->distance), $tick);
                            $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "dig.sand"), $tick + $this->destroyTime);
                        } else {
                            $blockData = [20, 15];
                            if (rand(0, 4) == 0) {
                                $tick += 1;
                            }
                            if(rand(0,10) == 0){
                                $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData,$player,"GaraProtection",$this->destroyTime, "random.glass",$this->distance), $tick);
                                $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "random.glass"), $tick + $this->destroyTime);
                                continue;
                            }
                            $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData,$player,"GaraProtection",$this->destroyTime,"",$this->distance), $tick);
                            $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData), $tick + $this->destroyTime);

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