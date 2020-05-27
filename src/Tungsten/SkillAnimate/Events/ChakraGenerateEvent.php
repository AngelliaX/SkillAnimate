<?php

namespace Tungsten\SkillAnimate\Events;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use Tungsten\SkillAnimate\SkillAnimate;

class ChakraGenerateEvent extends Event implements Cancellable{
    private $sa;
    /** @var Player $player */
    private $player;
	public function __construct(SkillAnimate $sa,Player $player){

		$this->sa = $sa;
		$this->player = $player;
	}

    /**
     * @return Player
     * return Player that execute
     */
	public function getPlayer(){
	    return $this->player;
    }
}
