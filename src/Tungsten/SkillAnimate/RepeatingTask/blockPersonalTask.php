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
    private $skipY;
    /** spawnTask|null The task on AnimateController */
    private $spawnTask;
    public function __construct(SkillAnimate $sa, Player $skillOwner, Vector3 $pos, Level $level, string $skillName, int $endtime, ?float $distance = 0.25, bool $skipY = false,Task $spawnTask = null)
    {
        $this->sa = $sa;
        $this->skillOwner = $skillOwner;
        $this->pos = $pos;
        $this->level = $level;
        $this->skillName = $skillName;
        $this->endTime = $endtime;
        $this->distance = $distance;
        $this->skipY = $skipY;
        $this->spawnTask = $spawnTask;
    }


    public function onRun($tick)
    {
        $this->time += 1;
        if ($this->time > $this->endTime) {
            $this->sa->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        /*
        ->when you use $block->getPosition(),the returned value will be the lower number.
        Example:
        ->a X coord of a block is 160,it's will be 160 and 161 in two side,at middle point)
        ->a X coord is -160,it's -160,-159
        FOR THAT REASON, i plused 1.
        */

        foreach ($this->level->getPlayers() as $player) {
            if ($player->x < $this->pos->x - $this->distance or $player->x > $this->pos->x + 1 + $this->distance) {
                continue;//skip this loop if this line is called
            }
            if (!$this->skipY) {
                if ($player->y < $this->pos->y - $this->distance or $player->y > $this->pos->y + 1 + $this->distance) {
                    continue;
                }
            }
            if ($player->z < $this->pos->z - $this->distance or $player->z > $this->pos->z + 1 + $this->distance) {
                continue;
            }
            if ($player->getName() == $this->skillOwner->getName()) {
                #continue;
            }
            $this->sa->getServer()->getPluginManager()->callEvent(new SkillCollideEvent($this->sa, $this->skillOwner, $player, $this->skillName,$this->spawnTask,$this));
        }
    }
}