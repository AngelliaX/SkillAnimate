<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\SkillContainer;

use pocketmine\math\Vector3;
use pocketmine\Player;
use Tungsten\SkillAnimate\AnimateController\destroyBlockTask;
use Tungsten\SkillAnimate\AnimateController\spawnBlockDelayedTask;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;


class GroundBadabum
{
    public $distance;
    public $width;
    public $destroytime;
    public function __construct(SkillAnimate $sa, Player $player)
    {
        if(!is_null($config = $sa->database->getConfig($player)->getNested("GroundBadabum"))){
            $this->destroytime = $config["destroyTime"];
            $this->distance = $config["distance"];
            $this->width = $config["width"];
        }else{
            $config = $sa->skillData->getNested("GroundBadabum");
            $this->destroytime = $config["destroyTime"];
            $this->distance = $config["distance"];
            $this->width = $config["width"];
        }
        $this->spawnBlock($sa, $player);
    }

    public function spawnBlock(SkillAnimate $sa, Player $player): void
    {
        $level = $player->getLevel();
        //dong 0 nam 1  bac 3 tay 2
        $direc = $player->getDirection();

        $tick = 2;

        $pos = $player;
        $pos = $this->PosCorrection($direc,$pos,2,0,0);
        $unitVector = $player->getDirectionVector();
        $unitVector->setComponents($unitVector->x,0,$unitVector->z);
        $tempZ = -1;
        for($i = 0; $i <= $this->distance; $i++){
            if($i < $this->width +2){
                $tempZ++;
            }
            if($i > $this->width +3 and $i < $this->distance - $this->width){
                if($tempZ >= $this->width){
                    $tempZ--;
                }else{
                    $tempZ++;
                }
            }
            if($i >= $this->distance - $this->width){
                $tempZ--;
            }
            $pos = $pos->add($unitVector);

            $position = 0;
            if(rand(0,10) == 0){
                $position = $pos->add(0,1,0);
            }else{
                $position = $pos;
            }

            if(rand(0,15) == 0){
                $position = $position->add($unitVector);
            }
            $blockData = [(rand(0, 6) == 0) ? 179 : 24, 15];

            $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($position, $level, $blockData,$player,"GroundBadabum",$this->destroytime,"dig.grass"), $tick);
            $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($position, $level, $blockData, "dig.grass"), $tick + $this->destroytime);
            $position = $pos;
            for ($z = 0; $z <= $tempZ; $z++) {
                $position = $pos;
                if(rand(0,3) == 0){$tick +=1;}
                $blockData = [(rand(0, 6) == 0) ? 179 : 24, 15];
                if($direc == 0 or $direc == 2){
                    if(rand(0,15) == 0){
                        $position = $pos->add(0,1,0);
                    }
                    $tempPos = $position->add(0,0,$z);
                    $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($tempPos, $level, $blockData,$player,"GroundBadabum",$this->destroytime, "dig.grass"), $tick);
                    $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($tempPos, $level, $blockData, "dig.grass"), $tick + $this->destroytime);
                    $tempPos = $position->add(0,0,-$z);
                    $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($tempPos, $level, $blockData,$player,"GroundBadabum",$this->destroytime, "dig.grass"), $tick);
                    $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($tempPos, $level, $blockData, "dig.grass"), $tick + $this->destroytime);
                }else{
                    if(rand(0,15) == 0){
                        $position = $pos->add(0,1,0);
                    }
                    $tempPos = $position->add($z,0,0);
                    $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($tempPos, $level, $blockData,$player,"GroundBadabum",$this->destroytime, "dig.grass"), $tick);
                    $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($tempPos, $level, $blockData, "dig.grass"), $tick + $this->destroytime);
                    $tempPos = $position->add(-$z,0,0);
                    $sa->getScheduler()->scheduleDelayedTask(new spawnBlockDelayedTask($tempPos, $level, $blockData,$player,"GroundBadabum",$this->destroytime, "dig.grass"), $tick);
                    $sa->getScheduler()->scheduleDelayedTask(new destroyBlockTask($tempPos, $level, $blockData, "dig.grass"), $tick + $this->destroytime);
                }

            }
        }


        /*
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
        */

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