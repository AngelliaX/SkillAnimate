<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\SkillContainer;

use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use Tungsten\SkillAnimate\AnimateController\destroyBlockTask;
use Tungsten\SkillAnimate\AnimateController\spawnBlockDelayedTask;
use Tungsten\SkillAnimate\AnimateController\spawnParticleDelayedTask;
use Tungsten\SkillAnimate\AnimateController\spawnParticleRepeatingTask;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;


class WispsSpawner
{
    private $sa;
    //end this skill time;
    private $endTime;
    //distance to trigger the skillcollideevent;
    private $distance;
    //Radius that appear the wisps;
    private $radius;
    //how many wisps on the area;
    private $amount;

    public function __construct(SkillAnimate $sa, Player $player)
    {
        $this->sa = $sa;
        if(!is_null($config = $sa->database->getConfig($player)->getNested("WispsSpawner"))){
            $this->endTime = $config["endTime"];
            $this->distance = $config["distance"];
            $this->radius = $config["radius"];
            $this->amount = $config["amount"];
        }else{
            $config = $sa->skillData->getNested("WispsSpawner");
            $this->endTime = $config["endTime"];
            $this->distance = $config["distance"];
            $this->radius = $config["radius"];
            $this->amount = $config["amount"];
        }
        $this->spawnParticle($sa, $player);
    }
    public function spawnParticle(SkillAnimate $sa, Player $player): void
    {
        $level = $player->getLevel();
        $playerPos = $player->asVector3()->add(0,1.5,0);
        for ($i = 1; $i <= $this->amount; $i++) {
            $randX = $this->frand(-$this->radius,+$this->radius,2);
            $randY = $this->frand(0, 2,2);
            $randZ = $this->frand(- $this->radius, + $this->radius,2);

            $sa->getScheduler()->scheduleRepeatingTask(new spawnParticleRepeatingTask($this->sa, [$randX, $randY, $randZ], $player, Particle::TYPE_FIREWORKS_OVERLAY, $this->endTime, "WispsSpawner", "fire.fire", $this->distance,false,true,false,[255,0,0],5), 1);

            /**  thuat toan de tao ra cac particle noi tu 1 pos toi pplayer*/
            $tempDivide = 0;
            $pos = $this->PosCorrection($player->getDirection(),$player,$randX,$randY,$randZ);
            if(abs($pos->x - $playerPos->x) >= abs($pos->z - $playerPos->z)){
                $tempDivide = abs($pos->x - $playerPos->x);
            }else{
                $tempDivide = abs($pos->z - $playerPos->z);
            }
            /** abs() is needed cuz if you dont use it, if it is negative, the loop will not be called because of $x always < 0 */
            for($x =abs($pos->x - $playerPos->x);$x > 0;$x -= abs(($pos->x -$playerPos->x)/$tempDivide)) {
                $tempX = ($pos->x -$playerPos->x) * $x/abs($pos->x - $playerPos->x);
                $tempY = ($pos->y - ($playerPos->y)) * $x/abs($pos->x - $playerPos->x);
                $tempZ = ($pos->z -$playerPos->z) * $x/abs($pos->x - $playerPos->x);
                /** already correct the pos above ($pos) so dont need to correct again here */
                $tempPos = $playerPos->add($tempX,$tempY,$tempZ);
                /** $time will increase from 0->1*/
                $time =(int) round(20 - 20*$x/abs($pos->x - $playerPos->x));
                $sa->getScheduler()->scheduleDelayedTask(new spawnParticleDelayedTask($tempPos, $level, Particle::TYPE_SPARKLER,$player,"WispsSpawner.small",1, "fire.fire",$this->distance,[rand(0,255),rand(0,255),rand(0,255)],false,false), $time);
                $sa->getScheduler()->scheduleDelayedTask(new spawnParticleDelayedTask($tempPos, $level, Particle::TYPE_SPARKLER,$player,"WispsSpawner",1, "fire.fire",$this->distance,[rand(0,255),rand(0,255),rand(0,255)],true), $time);
                $sa->getScheduler()->scheduleDelayedTask(new spawnParticleDelayedTask($tempPos, $level, Particle::TYPE_SPARKLER,$player,"WispsSpawner",1, "fire.fire",$this->distance,[rand(0,255),rand(0,255),rand(0,255)],true), $time);
            }
        }
    }
    private function frand($min, $max, $decimals = 0) :float{
        $scale = pow(10, $decimals);
        return mt_rand((int) $min * $scale,(int) $max * $scale) / $scale;
    }
    private function PosCorrection(int $direc, Vector3 $pos,?float $x, ?float $y,?float $z): Vector3
    {
        if ($direc == 0) {
            return $pos->add($x, $y, $z);
        } else if ($direc == 1) {
            return $pos->add(-$z, $y, $x);
        } else if ($direc == 2) {
            return $pos->add(-$x, $y, -$z);
        } else {
            return $pos->add($z, $y, -$x);
        }
    }
}