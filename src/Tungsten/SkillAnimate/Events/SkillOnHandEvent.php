<?php

namespace Tungsten\SkillAnimate\Events;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use Tungsten\SkillAnimate\SkillAnimate;

    /** this class is called when the skill item is on hand */
class SkillOnHandEvent extends Event implements Cancellable{
    private $sa;
    /** @var Player $player */
    private $player;
    private $skillName;
	public function __construct(SkillAnimate $sa,Player $player,string $skillName){

		$this->sa = $sa;
		$this->player = $player;
		$this->skillName = $skillName;
	}

    /**
     * @return Player
     * return Player that execute
     */
	public function getPlayer(){
	    return $this->player;
    }

    public function getSkillName(){
	    return $this->skillName;
    }
}
