<?php

namespace Tungsten\SkillAnimate\Events;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use Tungsten\SkillAnimate\SkillAnimate;

class SkillCollideEvent extends Event implements Cancellable{
    private $sa;
    /** @var Player */
    private $player;
    /** @var Player */
    public $skillOwner;
    /** @var string */
    public $skillName;
	public function __construct(SkillAnimate $sa,Player $skillOwner,Player $player,string $skillName){

		$this->sa = $sa;
		$this->skillOwner = $skillOwner;
		$this->player = $player;
		$this->skillName = $skillName;
	}

    /**
     * @return Player
     * return Player that collide to the skill
     */
	public function getPlayer(){
	    return $this->player;
    }

    /**
     * @return Player that execute the skill
     */
    public function getSkillOwner(){
	    return $this->skillOwner;
    }
    public function getSkillName(){
        return $this->skillName;
    }
}
