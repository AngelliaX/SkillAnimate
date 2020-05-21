<?php
declare(strict_types=1);

namespace Tungsten\SkillAnimate\EventListener;
use onebone\economyapi\event\money\AddMoneyEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use Tungsten\SkillAnimate\Events\SkillCollideEvent;
use Tungsten\SkillAnimate\Events\SkillExecuteEvent;
use Tungsten\SkillAnimate\SkillAnimate;

use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;

use Tungsten\SkillAnimate\SkillContainer\GaraProtection;
use Tungsten\SkillAnimate\SkillContainer\GroundBadabum;

class SkillListener implements Listener
{
    public $sa;
    public $requiredChakra =
        [
            "GaraProtection" => 30,
            "GroundBadabum" => 40
        ];
    public function __construct(SkillAnimate $sa)
    {
        $this->sa = $sa;
    }

    public function onSkillExecute(SkillExecuteEvent $ev){
        $player = $ev->getPlayer();
        $skillName = $ev->getSkillName();
        $chakra = $player->namedtag->getFloat("Chakra");
        $requiredChakra = $this->requiredChakra[$skillName];

        if($chakra < $requiredChakra){
            $this->playMusic($player,"mob.elderguardian.curse");
            return;
        }

        if($skillName == "GaraProtection"){
            #$ev = new EntityDamageByEntityEvent($player,$player,EntityDamageEvent::CAUSE_ENTITY_ATTACK,7);
            #$player->attack($ev);
            #$player->knockBack($player,1,-2,-2,0.4);
            new GaraProtection($this->sa,$player);
            $player->namedtag->setFloat("Chakra",$chakra - $requiredChakra);
        }
        if($skillName == "GroundBadabum"){
            new GroundBadabum($this->sa,$player);
            $player->namedtag->setFloat("Chakra",$chakra - $requiredChakra);
        }

    }
    public function onSkillCollide(SkillCollideEvent $ev){

        $player = $ev->getPlayer();
        $skillOwner = $ev->getSkillOwner();
        $direc = $player->getDirection();

        if($direc == 0){
            $xDirec = -1;
            $zDirec = 0;
        }else if($direc == 1){
            $xDirec = 0;
            $zDirec = -1;
        }else if($direc == 2){
            $xDirec = +1;
            $zDirec = 0;
        }else{
            $xDirec = 0;
            $zDirec = +1;
        }

        if($ev->getSkillName() == "GaraProtection"){
            $player->knockBack($player,999,$xDirec,$zDirec,0.4);
        }
        if($ev->getSkillName() == "GroundBadabum"){
            $ev = new EntityDamageByEntityEvent($skillOwner,$player,EntityDamageEvent::CAUSE_ENTITY_ATTACK,2);
            $player->attack($ev);
            $player->knockBack($player,999,$xDirec,$zDirec,0.2);
        }
    }
    public function playMusic(Player $player, string $soundName)
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