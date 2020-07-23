<?php

namespace Tungsten\SkillAnimate\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use Tungsten\SkillAnimate\SkillAnimate;

class SkillCollideEvent extends Event implements Cancellable
{
    private $sa;
    /** @var Player */
    private $player;
    /** @var Player */
    private $skillOwner;
    /** @var string */
    private $skillName;
    /** @var Task|null  */
    private $spawnTask;
    /** @var Task|null  */
    private $personalTask;
    public function __construct(SkillAnimate $sa, Player $skillOwner, Player $player, string $skillName, Task $spawnTask = null, Task $personalTask = null)
    {

        $this->sa = $sa;
        $this->skillOwner = $skillOwner;
        $this->player = $player;
        $this->skillName = $skillName;
        $this->spawnTask = $spawnTask;
        $this->personalTask = $personalTask;
    }

    /**
     * @return Player
     * return Player that collide to the skill
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @return Player that execute the skill
     */
    public function getSkillOwner()
    {
        return $this->skillOwner;
    }

    public function getSkillName()
    {
        return $this->skillName;
    }

    public function getSpawnTask() : ?Task {
        return $this->spawnTask;
    }

    public function getPersonalTask() : ?Task {
        return $this->personalTask;
    }
}
