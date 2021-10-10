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

        if(!$target->isOnline()){
            $this->getHandler()->cancel();
            if($this->player->isOnline()){
                $this->player->sendMessage("§cChasingFluid: §rTarget vua thoat khoi server!");
            }
            return;
        }
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

}