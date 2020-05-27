<?php

namespace Tungsten\SkillAnimate\RepeatingTask;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\Events\SkillCollideEvent;
use Tungsten\SkillAnimate\SkillAnimate;

class blockPersonalTask extends Task
{
    /** @var SkillAnimate */
    public $sa;
    /** @var Player */
    public $skillOwner;
    /** @var Vector3 */
    public $pos;
    /** @var Level */
    public $level;
    /** @var string */
    public $skillName;
    /** @var int */
    public $endTime;

    private $time = 0;
    private $distance;
    public function __construct(SkillAnimate $sa, Player $skillOwner, Vector3 $pos, Level $level, string $skillName, int $endtime,int $distance = 1)
    {
        $this->sa = $sa;
        $this->skillOwner = $skillOwner;
        $this->pos = $pos;
        $this->level = $level;
        $this->skillName = $skillName;
        $this->endTime = $endtime;
        $this->distance = $distance;
        #var_dump("class called");
    }


    public function onRun($tick)
    {
        $this->time += 1;
        if ($this->time >= $this->endTime) {
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        foreach($this->level->getPlayers() as $player){
            if($player->x < $this->pos->x -$this->distance or $player->x > $this->pos->x +$this->distance){
                continue;
            }
            if($player->y < $this->pos->y -$this->distance or $player->y > $this->pos->y +$this->distance){
                continue;
            }
            if($player->z < $this->pos->z -$this->distance or $player->z > $this->pos->z +$this->distance){
                continue;
            }
            if($player->getName() == $this->skillOwner->getName()){
                continue;
            }
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillCollideEvent($this->sa,$this->skillOwner,$player,$this->skillName));
        }
    }
}