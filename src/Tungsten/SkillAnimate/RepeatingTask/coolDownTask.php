<?php

namespace Tungsten\SkillAnimate\RepeatingTask;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\EventListener\SkillExecuteListener;
use Tungsten\SkillAnimate\SkillAnimate;

class coolDownTask extends Task
{
    private $sel;
    private $player;
    private $skillName;
    private $timeLeft = 0;
    public function __construct(SkillExecuteListener $sel,Player $player,string $skillName,int $timeLeft)
    {
        $this->sel = $sel;
        $this->skillName = $skillName;
        $this->player = $player;
        $this->timeLeft = $timeLeft;
        $sel->coolDown[$player->getName()."_".$skillName] = $this;
    }


    public function onRun($tick)
    {
        $this->timeLeft -= $this->getHandler()->getPeriod();
        if($this->timeLeft <=0 ){
            $this->getHandler()->cancel();
            unset($this->sel->coolDown[$this->player->getName()."_".$this->skillName]);
            $this->player->sendTip("§c".$this->skillName."§f da san sang");
            $this->playMusic($this->player,"random.toast");
        }
    }
    public function getTimeLeft(){
        return round($this->timeLeft/20,1);
    }
    private function playMusic(Player $player, string $soundName)
    {
        $sound = new PlaySoundPacket();
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = 0.5;
        $sound->pitch = 1;
        $sound->soundName = $soundName;
        SkillAnimate::$instance->getServer()->broadcastPacket([$player], $sound);
    }

}