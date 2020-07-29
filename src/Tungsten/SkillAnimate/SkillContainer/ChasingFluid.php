<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\SkillContainer;

use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\AnimateController\spawnBlockRepeatingTask;
use Tungsten\SkillAnimate\AnimateController\spawnParticleDelayedTask;
use Tungsten\SkillAnimate\Events\SkillCollideEvent;
use Tungsten\SkillAnimate\RepeatingTask\blockPersonalTask;
use Tungsten\SkillAnimate\SkillAnimate;

class ChasingFluid extends Task
{
    public $endTime;
    public $oneRoundSpeed;
    private $sa;
    private $player;
    private $target;
    private $checkTick = 0;
    private $howManyTimeCheck = 0;

    private $speed = 0.35; //0.5block/5tick  => 2block/1s
    private $nowPos;
    private $countTime;

    public function __construct(SkillAnimate $sa, Player $player, Player $target)
    {
        $this->sa = $sa;
        $this->player = $player;
        $this->target = $target;
        $this->nowPos = $player->asVector3()->add(0,1,0);

        if (!is_null($config = $sa->database->getConfig($player)->getNested("SoulHand"))) {
            $this->endTime = $config["endTime"];
            $this->oneRoundSpeed = $config["oneRoundSpeed"];
        } else {
            $config = $sa->skillData->getNested("SoulHand");
            $this->endTime = $config["endTime"];
            $this->oneRoundSpeed = $config["oneRoundSpeed"];
        }

    }

    public function onRun($tick)
    {
        //code only run every 5tick
        $this->countTime += $this->getHandler()->getPeriod();
        if($this->countTime < 2){
            return;
        }else{
            $this->countTime = 0;
        }
        $target = $this->target;
        $nowPos = $this->nowPos;

        $tempX = (($target->x - $nowPos->x)>0) ? $this->speed: -$this->speed;
        $tempY = ((($target->y+1) - $nowPos->y)>0) ? $this->speed: -$this->speed;
        $tempZ = (($target->z - $nowPos->z)>0)? $this->speed: -$this->speed;
        if(abs($target->x - $nowPos->x) < $this->speed){
            $tempX = 0;
        }
        if(abs(($target->y+1) - $nowPos->y) < $this->speed){
            $tempY = 0;
        }
        if(abs($target->z - $nowPos->z) < $this->speed){
            $tempZ = 0;
        }

        $nowPos = $nowPos->add($tempX,$tempY,$tempZ);
        $this->nowPos = $nowPos;

        $distance = sqrt($nowPos->distanceSquared($target));

        if($distance < 1.5){
            var_dump("done");
            $this->getHandler()->cancel();
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillCollideEvent($this->sa, $this->player, $this->target, "ChasingFluid"));
        }
        $signX = $this->sign($tempX);
        $signY = $this->sign($tempY);
        $signZ = $this->sign($tempZ);
        for ($r = 0; $r <= 0.25; $r += 0.25) {
            $amount = ($r * 30) + 1;
            for ($i = pi(); $i <= 3 * pi(); $i += 2 * pi() / $amount) {
                $x = cos($i) * $r;
                $z = sin($i) * $r;
                $target->getLevel()->addParticle(new GenericParticle($nowPos->add(($x-1.25)*$signX,($z),($x-1.25)*$signZ),Particle::TYPE_SPARKLER,((255 & 0xff) << 24) | ((rand(0, 81) & 0xff) << 16) | ((rand(0, 247) & 0xff) << 8) | ( rand(235, 255) & 0xff)));
            }
        }

        $target->getLevel()->addParticle(new GenericParticle($nowPos->add(-0.25*$signX,0,-0.25*$signZ),Particle::TYPE_SPARKLER,((255 & 0xff) << 24) | ((rand(0, 81) & 0xff) << 16) | ((rand(0, 247) & 0xff) << 8) | ( rand(235, 255) & 0xff)));
        $target->getLevel()->addParticle(new GenericParticle($nowPos,Particle::TYPE_SPARKLER,((255 & 0xff) << 24) | ((rand(0, 81) & 0xff) << 16) | ((rand(0, 247) & 0xff) << 8) | ( rand(235, 255) & 0xff)));
    }

    private function sign ($num){
        return (($num > 0) - ($num < 0));
    }
    public function callTaskParallel(array $xyz, ?int $endTime, string $sound = null)
    {
        $blockData = [(rand(0, 6) == 0) ? 179 : 24, 15];
        $this->sa->getScheduler()->scheduleRepeatingTask(new spawnBlockRepeatingTask($this->sa, [$xyz[0], $xyz[1], $xyz[2]], $this->player, $blockData, $endTime, "SoulHand", "dig.grass", 1), 1);
        $this->sa->getScheduler()->scheduleRepeatingTask(new spawnBlockRepeatingTask($this->sa, [$xyz[0], $xyz[1], -$xyz[2]], $this->player, $blockData, $endTime, "SoulHand", "dig.grass", 1), 1);
    }

    private function particleFromVectorAtoB(Player $player, Level $level, Vector3 $a, ?Vector3 $b, ?int $time, ?int $bonusTime = 0): void
    {
        $playerPos = $a;//vector is modified already before this function
        $closest = $b;

        $tempDivide = 0;
        $pos = $closest;
        if (abs($pos->x - $playerPos->x) >= abs($pos->z - $playerPos->z)) {
            $tempDivide = abs($pos->x - $playerPos->x);
        } else {
            $tempDivide = abs($pos->z - $playerPos->z);
        }
        /** abs() is needed cuz if you dont use it, if it is negative, the loop will not be called because of $x always < 0 */
        for ($i = $time + 1; $i > 1; $i--) {
            $tempX = ($pos->x - $playerPos->x) * (($time - $i) / $time);
            $tempY = ($pos->y - $playerPos->y) * (($time - $i) / $time);
            $tempZ = ($pos->z - $playerPos->z) * (($time - $i) / $time);
            /** already correct the pos above ($pos) so dont need to correct again here */
            $tempPos = $playerPos->add($tempX, $tempY, $tempZ);
            $tempPos = $tempPos->add($this->frand(0, 0.125), $this->frand(0, 0.125), $this->frand(0, 0.125));
            /** $time will increase from 0->1*/
            $time2 = (int)$time - $i;
            for ($o = rand(3, 6); $o > 1; $o--) {
                $this->sa->getScheduler()->scheduleDelayedTask(new spawnParticleDelayedTask($tempPos, $level, Particle::TYPE_SPARKLER, $player, "StickyFluid", 1, "liquid.water", 0.5, [rand(0, 81), rand(0, 247), rand(235, 255)], true), $time2 + $bonusTime);
            }
        }
    }

    // Tự biến đổi cho mỗi skill

    private function frand($min, $max, $decimals = 0): float
    {
        $scale = pow(10, $decimals);
        return mt_rand((int)$min * $scale, (int)$max * $scale) / $scale;
    }
}